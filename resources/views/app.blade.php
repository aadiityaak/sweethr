<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
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
        <link rel="manifest" href="/manifest.json">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

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
                    navigator.serviceWorker.register('/sw.js')
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
                // Prevent Chrome 67 and earlier from automatically showing the prompt
                e.preventDefault();
                // Stash the event so it can be triggered later
                deferredPrompt = e;

                // Optional: Show your own install button
                console.log('PWA install prompt available');

                // You can add your own install button here
                // showInstallButton();
            });

            // Track PWA install
            window.addEventListener('appinstalled', (evt) => {
                console.log('PWA was installed');
                // Optional: Analytics tracking
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

                // Reload after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
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
