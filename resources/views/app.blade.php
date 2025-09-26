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

                                        // Optional: Show update notification
                                        if (confirm('Aplikasi telah diperbarui. Refresh untuk mendapatkan versi terbaru?')) {
                                            window.location.reload();
                                        }
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
        </script>
    </body>
</html>
