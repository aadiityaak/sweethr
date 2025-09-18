<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <!-- Offline Icon -->
      <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-200 dark:bg-gray-700 mb-8">
        <WifiOff class="h-12 w-12 text-gray-600 dark:text-gray-400" />
      </div>

      <!-- Title -->
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
        Anda Sedang Offline
      </h1>

      <!-- Description -->
      <p class="text-gray-600 dark:text-gray-400 mb-8">
        Koneksi internet tidak tersedia. Beberapa fitur mungkin terbatas,
        namun data Anda akan tersinkronisasi ketika koneksi kembali.
      </p>

      <!-- Features Available Offline -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 mb-8 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">
          Fitur yang Tersedia Offline:
        </h3>
        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400 text-left">
          <li class="flex items-center">
            <Check class="h-4 w-4 text-green-500 mr-2" />
            Melihat data attendance sebelumnya
          </li>
          <li class="flex items-center">
            <Check class="h-4 w-4 text-green-500 mr-2" />
            Melihat profil dan informasi dasar
          </li>
          <li class="flex items-center">
            <Check class="h-4 w-4 text-green-500 mr-2" />
            Cache halaman yang sudah dibuka
          </li>
          <li class="flex items-center">
            <X class="h-4 w-4 text-red-500 mr-2" />
            Check-in/out attendance (akan tersinkron nanti)
          </li>
          <li class="flex items-center">
            <X class="h-4 w-4 text-red-500 mr-2" />
            Pengajuan cuti baru
          </li>
        </ul>
      </div>

      <!-- Actions -->
      <div class="space-y-4">
        <button
          @click="retry"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors"
        >
          <RefreshCw class="h-4 w-4 inline mr-2" />
          Coba Lagi
        </button>

        <button
          @click="goHome"
          class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-lg transition-colors"
        >
          <Home class="h-4 w-4 inline mr-2" />
          Kembali ke Beranda
        </button>
      </div>

      <!-- Network Status -->
      <div class="mt-8 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
        <div class="flex items-center justify-center text-sm">
          <div
            :class="[
              'h-2 w-2 rounded-full mr-2',
              isOnline ? 'bg-green-500' : 'bg-red-500'
            ]"
          ></div>
          <span class="text-gray-600 dark:text-gray-400">
            Status: {{ isOnline ? 'Online' : 'Offline' }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { WifiOff, Check, X, RefreshCw, Home } from 'lucide-vue-next'

const isOnline = ref(navigator.onLine)

const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine
}

const retry = () => {
  if (isOnline.value) {
    window.location.reload()
  } else {
    // Try to go back to previous page
    window.history.back()
  }
}

const goHome = () => {
  router.visit('/')
}

onMounted(() => {
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
})

onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
})
</script>