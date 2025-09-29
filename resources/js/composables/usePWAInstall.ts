import { onMounted, onUnmounted, ref } from 'vue';

export interface PWAInstallPrompt {
    prompt(): Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed'; platform: string }>;
}

export function usePWAInstall() {
    const isInstallable = ref(false);
    const isInstalled = ref(false);
    const isStandalone = ref(false);
    const deferredPrompt = ref<PWAInstallPrompt | null>(null);
    const isLoading = ref(false);

    // Check if app is running in standalone mode
    const checkStandaloneMode = () => {
        // Check various standalone indicators
        const standaloneChecks = [
            // PWA display mode
            window.matchMedia('(display-mode: standalone)').matches,
            // iOS Safari
            (window.navigator as any).standalone === true,
            // Android Chrome
            document.referrer.includes('android-app://'),
            // Windows PWA
            window.matchMedia('(display-mode: minimal-ui)').matches,
        ];

        isStandalone.value = standaloneChecks.some(Boolean);
        isInstalled.value = isStandalone.value;
    };

    // Handle beforeinstallprompt event
    const handleBeforeInstallPrompt = (e: Event) => {
        console.log('PWA install prompt available');

        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();

        // Stash the event so it can be triggered later
        deferredPrompt.value = e as any;
        isInstallable.value = true;
    };

    // Handle app installed event
    const handleAppInstalled = () => {
        console.log('PWA was installed');
        isInstalled.value = true;
        isInstallable.value = false;
        deferredPrompt.value = null;
    };

    // Install the PWA
    const installPWA = async (): Promise<boolean> => {
        if (!deferredPrompt.value) {
            console.warn('No install prompt available');
            return false;
        }

        try {
            isLoading.value = true;

            // Show the install prompt
            await deferredPrompt.value.prompt();

            // Wait for the user to respond to the prompt
            const { outcome } = await deferredPrompt.value.userChoice;

            console.log(`User response to the install prompt: ${outcome}`);

            // We no longer need the prompt. Clear it up.
            deferredPrompt.value = null;
            isInstallable.value = false;

            if (outcome === 'accepted') {
                isInstalled.value = true;
                return true;
            }

            return false;
        } catch (error) {
            console.error('Error during PWA installation:', error);
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    // Get platform-specific install instructions
    const getInstallInstructions = () => {
        const userAgent = navigator.userAgent.toLowerCase();

        if (userAgent.includes('iphone') || userAgent.includes('ipad')) {
            return {
                platform: 'iOS',
                steps: ['Tap tombol Share/Bagikan di Safari', 'Pilih "Add to Home Screen"', 'Tap "Add" untuk menginstall aplikasi'],
            };
        } else if (userAgent.includes('android')) {
            return {
                platform: 'Android',
                steps: ['Tap menu ⋮ di browser', 'Pilih "Add to Home screen" atau "Install app"', 'Konfirmasi instalasi aplikasi'],
            };
        } else {
            return {
                platform: 'Desktop',
                steps: [
                    'Klik icon install di address bar browser',
                    'Atau buka menu browser → "Install Aplikasi"',
                    'Konfirmasi untuk menginstall aplikasi',
                ],
            };
        }
    };

    // Check if PWA features are supported
    const isPWASupported = () => {
        return 'serviceWorker' in navigator && 'PushManager' in window;
    };

    // Get PWA installation status info
    const getInstallationInfo = () => {
        if (isInstalled.value) {
            return {
                status: 'installed',
                message: 'Aplikasi sudah terinstall di device Anda',
                canInstall: false,
            };
        } else if (isInstallable.value) {
            return {
                status: 'installable',
                message: 'Aplikasi dapat diinstall ke device Anda',
                canInstall: true,
            };
        } else if (!isPWASupported()) {
            return {
                status: 'unsupported',
                message: 'Browser Anda tidak mendukung PWA',
                canInstall: false,
            };
        } else {
            return {
                status: 'pending',
                message: 'Menunggu prompt instalasi dari browser',
                canInstall: false,
            };
        }
    };

    onMounted(() => {
        checkStandaloneMode();

        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

        // Listen for the appinstalled event
        window.addEventListener('appinstalled', handleAppInstalled);

        // Check if already installed by checking for PWA display mode
        const mediaQuery = window.matchMedia('(display-mode: standalone)');
        mediaQuery.addEventListener('change', checkStandaloneMode);
    });

    onUnmounted(() => {
        window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.removeEventListener('appinstalled', handleAppInstalled);
    });

    return {
        isInstallable,
        isInstalled,
        isStandalone,
        isLoading,
        installPWA,
        getInstallInstructions,
        getInstallationInfo,
        isPWASupported,
    };
}
