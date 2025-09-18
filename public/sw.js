const CACHE_NAME = 'sweethr-v1';
const urlsToCache = [
  '/',
  '/manifest.json',
  '/icons/icon-192x192.svg',
  '/icons/icon-512x512.svg',
  '/offline.html'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  console.log('SW: Installing service worker');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('SW: Opened cache');
        // Only cache essential files, skip ones that might fail
        return cache.addAll([
          '/',
          '/manifest.json',
          '/offline.html'
        ]);
      })
      .catch((error) => {
        console.log('SW: Cache install failed:', error);
        // Don't fail the install if cache fails
        return Promise.resolve();
      })
  );
  // Force the waiting service worker to become the active service worker
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  // Claim control of all clients
  self.clients.claim();
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', (event) => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Handle API requests differently
  if (event.request.url.includes('/api/')) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          // If successful, clone and cache the response
          if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // If network fails, try to serve from cache
          return caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // Return offline page for API requests if no cache
            return new Response(
              JSON.stringify({
                error: 'Offline',
                message: 'Anda sedang offline. Beberapa fitur mungkin tidak tersedia.'
              }),
              {
                status: 503,
                statusText: 'Service Unavailable',
                headers: { 'Content-Type': 'application/json' }
              }
            );
          });
        })
    );
    return;
  }

  // Handle page requests
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached version or fetch from network
        if (response) {
          return response;
        }

        return fetch(event.request).then((response) => {
          // Check if we received a valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // Clone the response
          const responseToCache = response.clone();

          caches.open(CACHE_NAME)
            .then((cache) => {
              cache.put(event.request, responseToCache);
            });

          return response;
        });
      })
      .catch(() => {
        // If both cache and network fail, show offline page
        if (event.request.destination === 'document') {
          return caches.match('/offline.html');
        }
      })
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
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(data)
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
      tag: data.tag || 'sweethr-notification',
      requireInteraction: true,
      actions: [
        {
          action: 'view',
          title: 'Lihat',
          icon: '/icons/view-action.png'
        },
        {
          action: 'dismiss',
          title: 'Tutup',
          icon: '/icons/dismiss-action.png'
        }
      ]
    };

    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  }
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow(event.notification.data?.url || '/')
    );
  }
});