<template>
  <div class="space-y-4">
    <!-- PWA Install Controls -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
      <div class="flex items-center space-x-3">
        <!-- Install Button -->
        <button
          v-if="installInfo.canInstall"
          @click="handleInstall"
          :disabled="isLoading"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md disabled:opacity-50 transition-colors"
        >
          <Download v-if="!isLoading" class="h-4 w-4 mr-2" />
          <Loader2 v-else class="h-4 w-4 mr-2 animate-spin" />
          {{ isLoading ? 'Installing...' : 'Install Aplikasi' }}
        </button>

        <!-- Manual Install Instructions Button -->
        <button
          v-if="!installInfo.canInstall && !isInstalled"
          @click="showInstructions = !showInstructions"
          class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
        >
          <Info class="h-4 w-4 mr-2" />
          {{ showInstructions ? 'Sembunyikan' : 'Cara Install' }}
        </button>

        <!-- Already Installed Badge -->
        <div v-if="isInstalled" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
          <Check class="h-4 w-4 mr-1" />
          Sudah Terinstall
        </div>
      </div>
    </div>

    <!-- Manual Install Instructions -->
    <div
      v-if="showInstructions"
      class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
    >
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <Info class="h-5 w-5 text-blue-600 dark:text-blue-400" />
        </div>
        <div class="ml-3">
          <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200">
            Cara Install di {{ instructions.platform }}
          </h4>
          <ol class="mt-2 text-sm text-blue-800 dark:text-blue-300 space-y-1">
            <li v-for="(step, index) in instructions.steps" :key="index" class="flex items-start">
              <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-600 text-white text-xs font-medium mr-2 mt-0.5 flex-shrink-0">
                {{ index + 1 }}
              </span>
              {{ step }}
            </li>
          </ol>
        </div>
      </div>
    </div>

    <!-- PWA Features Info -->
    <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
      <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
        Keuntungan Install Aplikasi:
      </h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
          <Zap class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
          Akses lebih cepat
        </div>
        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
          <Smartphone class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
          Shortcut di home screen
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { usePWAInstall } from '@/composables/usePWAInstall'
import {
  Download,
  Loader2,
  Info,
  Check,
  Smartphone,
  Zap
} from 'lucide-vue-next'

const {
  isInstallable,
  isInstalled,
  isLoading,
  installPWA,
  getInstallInstructions,
  getInstallationInfo
} = usePWAInstall()

const showInstructions = ref(false)

const installInfo = computed(() => getInstallationInfo())
const instructions = computed(() => getInstallInstructions())

const handleInstall = async () => {
  try {
    const success = await installPWA()
    if (success) {
      console.log('PWA installed successfully')
      // Optional: Show success message or redirect
    } else {
      console.log('PWA installation was cancelled or failed')
      // Optional: Show manual instructions
      showInstructions.value = true
    }
  } catch (error) {
    console.error('Failed to install PWA:', error)
    // Optional: Show error message
  }
}
</script>