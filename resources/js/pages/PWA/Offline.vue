<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 dark:bg-gray-900">
        <div class="w-full max-w-md text-center">
            <!-- Offline Icon -->
            <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                <WifiOff class="h-12 w-12 text-gray-600 dark:text-gray-400" />
            </div>

            <!-- Title -->
            <h1 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Anda Sedang Offline</h1>

            <!-- Description -->
            <p class="mb-8 text-gray-600 dark:text-gray-400">
                Koneksi internet tidak tersedia. Beberapa fitur mungkin terbatas, namun data Anda akan tersinkronisasi ketika koneksi kembali.
            </p>

            <!-- Features Available Offline -->
            <div class="mb-8 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Fitur yang Tersedia Offline:</h3>
                <ul class="space-y-2 text-left text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-center">
                        <Check class="mr-2 h-4 w-4 text-green-500" />
                        Melihat data attendance sebelumnya
                    </li>
                    <li class="flex items-center">
                        <Check class="mr-2 h-4 w-4 text-green-500" />
                        Melihat profil dan informasi dasar
                    </li>
                    <li class="flex items-center">
                        <Check class="mr-2 h-4 w-4 text-green-500" />
                        Cache halaman yang sudah dibuka
                    </li>
                    <li class="flex items-center">
                        <X class="mr-2 h-4 w-4 text-red-500" />
                        Check-in/out attendance (akan tersinkron nanti)
                    </li>
                    <li class="flex items-center">
                        <X class="mr-2 h-4 w-4 text-red-500" />
                        Pengajuan cuti baru
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <button @click="retry" class="w-full rounded-lg bg-blue-600 px-4 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                    <RefreshCw class="mr-2 inline h-4 w-4" />
                    Coba Lagi
                </button>

                <button @click="goHome" class="w-full rounded-lg bg-gray-600 px-4 py-3 font-medium text-white transition-colors hover:bg-gray-700">
                    <Home class="mr-2 inline h-4 w-4" />
                    Kembali ke Beranda
                </button>
            </div>

            <!-- Network Status -->
            <div class="mt-8 rounded-lg bg-gray-100 p-4 dark:bg-gray-800">
                <div class="flex items-center justify-center text-sm">
                    <div :class="['mr-2 h-2 w-2 rounded-full', isOnline ? 'bg-green-500' : 'bg-red-500']"></div>
                    <span class="text-gray-600 dark:text-gray-400"> Status: {{ isOnline ? 'Online' : 'Offline' }} </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Check, Home, RefreshCw, WifiOff, X } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const isOnline = ref(navigator.onLine);

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine;
};

const retry = () => {
    if (isOnline.value) {
        window.location.reload();
    } else {
        // Try to go back to previous page
        window.history.back();
    }
};

const goHome = () => {
    router.visit('/');
};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
});
</script>
