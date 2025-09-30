// Try to get version from version.json, fallback to static version
let APP_VERSION = '1.0.0';
let BUILD_TIMESTAMP = Date.now();

// Load version info if available
fetch('/version.json')
    .then((response) => response.json())
    .then((versionInfo) => {
        APP_VERSION = versionInfo.fullVersion;
        BUILD_TIMESTAMP = new Date(versionInfo.buildDate).getTime();
        console.log('SW: Loaded version info:', APP_VERSION);
    })
    .catch(() => {
        console.log('SW: Using fallback version:', APP_VERSION);
    });

const CACHE_NAME = `sistemhr-${APP_VERSION || 'fallback'}`;
const urlsToCache = ['/', '/manifest.json', '/icons/icon-192x192.svg', '/icons/icon-512x512.svg', '/offline.html'];

// Install event - cache resources
self.addEventListener('install', (event) => {
    console.log('SW: Installing service worker');
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => {
                console.log('SW: Opened cache');
                // Only cache essential files, skip ones that might fail
                return cache.addAll(['/', '/manifest.json', '/offline.html']);
            })
            .catch((error) => {
                console.log('SW: Cache install failed:', error);
                // Don't fail the install if cache fails
                return Promise.resolve();
            }),
    );
    // Force the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            // Delete old caches
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('SW: Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    }),
                );
            }),
            // Clear face recognition API cache specifically
            clearFaceRecognitionCache(),
        ]),
    );
    // Claim control of all clients
    self.clients.claim();
});

// Clear face recognition related cache
async function clearFaceRecognitionCache() {
    try {
        const cacheNames = await caches.keys();

        // Clear all caches to ensure no stale data
        for (const cacheName of cacheNames) {
            console.log('SW: Clearing all cache:', cacheName);
            await caches.delete(cacheName);
        }

        console.log('SW: All caches cleared successfully');
    } catch (error) {
        console.log('SW: Error clearing caches:', error);
    }
}

// Fetch event - serve from cache when offline
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // Skip non-GET requests for caching (POST, PUT, DELETE, etc.)
    if (event.request.method !== 'GET') {
        return;
    }

    // Handle model files - always fetch fresh to avoid stale face-api models
    if (event.request.url.includes('/models/')) {
        event.respondWith(
            fetch(event.request).catch(() => {
                return new Response('Model not available offline', { status: 503 });
            }),
        );
        return;
    }

    // Handle API requests - Network First, Cache Fallback Strategy
    if (event.request.url.includes('/api/')) {
        // Always try network first, only use cache if offline
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    // Only cache successful responses for offline fallback
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // If network fails (offline), try to serve from cache
                    return caches.match(event.request).then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Return offline message if no cache available
                        return new Response(
                            JSON.stringify({
                                error: 'Offline',
                                message: 'Anda sedang offline. Beberapa fitur mungkin tidak tersedia.',
                            }),
                            {
                                status: 503,
                                statusText: 'Service Unavailable',
                                headers: { 'Content-Type': 'application/json' },
                            },
                        );
                    });
                }),
        );
        return;
    }

    // Handle image requests - always fetch fresh for uploaded images
    if (event.request.url.includes('/storage/') || event.request.url.includes('/uploads/') || event.request.url.includes('/announcements/')) {
        event.respondWith(
            fetch(event.request, {
                cache: 'no-cache',
            }).catch(() => {
                // If network fails, try cache as fallback
                return caches.match(event.request);
            }),
        );
        return;
    }

    // Handle all page requests - Network First, Cache Fallback Strategy
    // This ensures users always get fresh data when online
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Check if we received a valid response
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // Cache the response for offline fallback only
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseToCache);
                });

                return response;
            })
            .catch(() => {
                // If network fails (offline), try to serve from cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // If no cache and offline, show offline page for documents
                    if (event.request.destination === 'document') {
                        return caches.match('/offline.html');
                    }
                });
            }),
    );
});

// Background sync for attendance data
self.addEventListener('sync', (event) => {
    if (event.tag === 'attendance-sync') {
        event.waitUntil(syncAttendanceData());
    }
});

async function syncAttendanceData() {
    try {
        // Get pending attendance data from IndexedDB
        const pendingData = await getPendingAttendanceData();

        for (const data of pendingData) {
            try {
                const response = await fetch('/api/attendance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(data),
                });

                if (response.ok) {
                    // Remove from pending data
                    await removePendingAttendanceData(data.id);
                }
            } catch (error) {
                console.log('Failed to sync attendance data:', error);
            }
        }
    } catch (error) {
        console.log('Sync failed:', error);
    }
}

// Helper functions for IndexedDB operations
async function getPendingAttendanceData() {
    // Implementation depends on your IndexedDB setup
    return [];
}

async function removePendingAttendanceData(id) {
    // Implementation depends on your IndexedDB setup
}

// Handle push notifications
self.addEventListener('push', (event) => {
    if (event.data) {
        const data = event.data.json();

        const options = {
            body: data.body,
            icon: '/icons/icon-192x192.png',
            badge: '/icons/badge-72x72.png',
            tag: data.tag || 'sistemhr-notification',
            requireInteraction: true,
            actions: [
                {
                    action: 'view',
                    title: 'Lihat',
                    icon: '/icons/view-action.png',
                },
                {
                    action: 'dismiss',
                    title: 'Tutup',
                    icon: '/icons/dismiss-action.png',
                },
            ],
        };

        event.waitUntil(self.registration.showNotification(data.title, options));
    }
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'view') {
        event.waitUntil(clients.openWindow(event.notification.data?.url || '/'));
    }
});

// Handle messages from main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CHECK_UPDATE') {
        // Force update check
        console.log('SW: Checking for updates...');
        self.registration.update().then(() => {
            console.log('SW: Update check completed');
        });
    }

    if (event.data && event.data.type === 'SKIP_WAITING') {
        // Force service worker activation
        self.skipWaiting();
    }
});
