<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'light') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- PWA Meta Tags --}}
        <meta name="application-name" content="{{ $companySettings['company_name'] ?? config('app.name', 'Sistem HR') }}">
        <meta name="theme-color" content="#3b82f6">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ $companySettings['company_name'] ?? config('app.name', 'Sistem HR') }}">
        <meta name="msapplication-TileColor" content="#3b82f6">
        <meta name="msapplication-tap-highlight" content="no">

        {{-- PWA Manifest --}}
        <link rel="manifest" href="/manifest.json?v={{ @filemtime(public_path('manifest.json')) }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "light" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ $companySettings['company_name'] ?? config('app.name', 'Laravel') }}</title>

        @if(isset($companySettings['favicon']) && $companySettings['favicon'])
            <link rel="icon" href="{{ $companySettings['favicon'] }}" sizes="any">
            <link rel="icon" href="{{ $companySettings['favicon'] }}" type="image/svg+xml">
            <link rel="apple-touch-icon" href="{{ $companySettings['favicon'] }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        {{-- Service Worker Registration --}}
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    const swUrl = '/sw.js?v={{ @filemtime(public_path('sw.js')) }}';
                    navigator.serviceWorker.register(swUrl)
                        .then(function(registration) {
                            console.log('SW registered: ', registration);

                            // Check for updates
                            registration.addEventListener('updatefound', () => {
                                const newWorker = registration.installing;
                                newWorker.addEventListener('statechange', () => {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // New content is available
                                        console.log('New content is available; please refresh.');

                                        // Show modern update notification
                                        showUpdateNotification();
                                    }
                                });
                            });
                            let swRefreshing = false;
                            navigator.serviceWorker.addEventListener('controllerchange', () => {
                                if (swRefreshing) return;
                                swRefreshing = true;
                                window.location.reload();
                            });
                        })
                        .catch(function(registrationError) {
                            console.log('SW registration failed: ', registrationError);
                        });

                    // Listen for messages from service worker
                    navigator.serviceWorker.addEventListener('message', event => {
                        if (event.data && event.data.type === 'CACHE_UPDATED') {
                            console.log('Cache updated by service worker');
                        }
                    });
                });
            }

            // Handle offline/online events
            window.addEventListener('online', function() {
                console.log('App is online');
                // Optional: Show online notification
            });

            window.addEventListener('offline', function() {
                console.log('App is offline');
                // Optional: Show offline notification
            });

            // PWA Install prompt
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                showInstallBanner();
            });

            // Track PWA install
            window.addEventListener('appinstalled', (evt) => {
                console.log('PWA was installed');
                hideInstallBanner();
            });

            // Modern update notification system
            function showUpdateNotification() {
                // Remove existing notification
                const existingNotification = document.getElementById('sw-update-notification');
                if (existingNotification) {
                    existingNotification.remove();
                }

                // Create notification element
                const notification = document.createElement('div');
                notification.id = 'sw-update-notification';
                notification.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: #3B82F6;
                        color: white;
                        padding: 16px 20px;
                        border-radius: 12px;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                        z-index: 9999;
                        max-width: 300px;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        animation: slideIn 0.3s ease-out;
                    ">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="
                                width: 8px;
                                height: 8px;
                                background: #10B981;
                                border-radius: 50%;
                                animation: pulse 2s infinite;
                            "></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 4px;">
                                    Update Tersedia
                                </div>
                                <div style="font-size: 14px; opacity: 0.9;">
                                    Aplikasi telah diperbarui dengan fitur terbaru
                                </div>
                            </div>
                        </div>
                        <div style="
                            margin-top: 12px;
                            display: flex;
                            gap: 8px;
                            justify-content: flex-end;
                        ">
                            <button onclick="dismissUpdateNotification()" style="
                                background: rgba(255,255,255,0.2);
                                border: none;
                                color: white;
                                padding: 6px 12px;
                                border-radius: 6px;
                                font-size: 14px;
                                cursor: pointer;
                            ">Nanti</button>
                            <button onclick="updateApp()" style="
                                background: white;
                                border: none;
                                color: #3B82F6;
                                padding: 6px 12px;
                                border-radius: 6px;
                                font-size: 14px;
                                font-weight: 600;
                                cursor: pointer;
                            ">Update Sekarang</button>
                        </div>
                    </div>
                `;

                // Add animation styles
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes slideIn {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    @keyframes pulse {
                        0%, 100% { opacity: 1; }
                        50% { opacity: 0.5; }
                    }
                `;
                document.head.appendChild(style);

                document.body.appendChild(notification);

                // Auto-dismiss after 10 seconds if not interacted
                setTimeout(() => {
                    const notification = document.getElementById('sw-update-notification');
                    if (notification) {
                        notification.style.animation = 'slideIn 0.3s ease-out reverse';
                        setTimeout(() => notification.remove(), 300);
                    }
                }, 10000);
            }

            function showInstallBanner() {
                if (document.getElementById('pwa-install-banner')) return;
                if (!document.getElementById('pwa-install-banner-style')) {
                    const style = document.createElement('style');
                    style.id = 'pwa-install-banner-style';
                    style.textContent = `
                        #pwa-install-banner button { font: inherit; }
                        #pwa-install-banner-actions { display: flex; gap: 10px; align-items: center; }
                        @media (max-width: 640px) {
                            #pwa-install-banner {
                                left: 12px !important;
                                right: 12px !important;
                                transform: none !important;
                                width: auto !important;
                                flex-direction: column !important;
                                align-items: stretch !important;
                            }
                            #pwa-install-banner > div:first-child {
                                text-align: center !important;
                                width: 100% !important;
                            }
                            #pwa-install-banner-actions {
                                width: 100% !important;
                                justify-content: center !important;
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }
                const container = document.createElement('div');
                container.id = 'pwa-install-banner';
                container.style.position = 'fixed';
                container.style.bottom = '20px';
                container.style.left = '50%';
                container.style.transform = 'translateX(-50%)';
                container.style.background = '#111827';
                container.style.color = 'white';
                container.style.padding = '12px 16px';
                container.style.borderRadius = '10px';
                container.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
                container.style.zIndex = '10000';
                container.style.display = 'flex';
                container.style.alignItems = 'center';
                container.style.gap = '10px';
                const text = document.createElement('div');
                text.textContent = 'Install aplikasi ke layar utama';
                text.style.fontSize = '14px';
                const btn = document.createElement('button');
                btn.textContent = 'Install';
                btn.style.background = 'white';
                btn.style.color = '#3B82F6';
                btn.style.border = 'none';
                btn.style.padding = '6px 12px';
                btn.style.borderRadius = '6px';
                btn.style.fontWeight = '600';
                btn.style.cursor = 'pointer';
                btn.addEventListener('click', async () => {
                    if (!deferredPrompt) return;
                    deferredPrompt.prompt();
                    try {
                        await deferredPrompt.userChoice;
                    } catch (_) {}
                    deferredPrompt = null;
                    hideInstallBanner();
                });
                const close = document.createElement('button');
                close.textContent = 'Tutup';
                close.style.background = 'transparent';
                close.style.color = 'white';
                close.style.border = '1px solid rgba(255,255,255,0.3)';
                close.style.padding = '6px 12px';
                close.style.borderRadius = '6px';
                close.style.cursor = 'pointer';
                close.addEventListener('click', hideInstallBanner);
                container.appendChild(text);
                const actions = document.createElement('div');
                actions.id = 'pwa-install-banner-actions';
                actions.appendChild(btn);
                actions.appendChild(close);
                container.appendChild(actions);
                document.body.appendChild(container);
            }

            function hideInstallBanner() {
                const el = document.getElementById('pwa-install-banner');
                if (el) el.remove();
            }

            function updateApp() {
                const notification = document.getElementById('sw-update-notification');
                if (notification) {
                    notification.style.animation = 'slideIn 0.3s ease-out reverse';
                    setTimeout(() => notification.remove(), 300);
                }

                // Show loading indicator
                const loadingIndicator = document.createElement('div');
                loadingIndicator.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background: rgba(0,0,0,0.8);
                        color: white;
                        padding: 20px;
                        border-radius: 12px;
                        z-index: 10000;
                        text-align: center;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    ">
                        <div style="
                            width: 40px;
                            height: 40px;
                            border: 4px solid rgba(255,255,255,0.3);
                            border-top: 4px solid white;
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                            margin: 0 auto 12px;
                        "></div>
                        <div>Memperbarui aplikasi...</div>
                    </div>
                `;

                const spinStyle = document.createElement('style');
                spinStyle.textContent = `
                    @keyframes spin {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(spinStyle);
                document.body.appendChild(loadingIndicator);

                (async () => {
                    try {
                        const reg = await navigator.serviceWorker.getRegistration();
                        if (reg && reg.waiting) {
                            reg.waiting.postMessage({ type: 'SKIP_WAITING' });
                            return;
                        }
                        if (navigator.serviceWorker.controller) {
                            navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
                            return;
                        }
                    } catch (e) {}
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })();
            }

            function dismissUpdateNotification() {
                const notification = document.getElementById('sw-update-notification');
                if (notification) {
                    notification.style.animation = 'slideIn 0.3s ease-out reverse';
                    setTimeout(() => notification.remove(), 300);
                }
            }

            // Check for updates on app focus
            window.addEventListener('focus', () => {
                if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({ type: 'CHECK_UPDATE' });
                }
            });
        </script>
    </body>
</html>
