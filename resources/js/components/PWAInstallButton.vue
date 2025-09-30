<template>
    <div class="space-y-4">
        <!-- PWA Install Controls -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center space-x-3">
                <!-- Install Button -->
                <button
                    v-if="installInfo.canInstall"
                    @click="handleInstall"
                    :disabled="isLoading"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
                >
                    <Download v-if="!isLoading" class="mr-2 h-4 w-4" />
                    <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                    {{ isLoading ? 'Installing...' : 'Install Aplikasi' }}
                </button>

                <!-- Manual Install Instructions Button -->
                <button
                    v-if="!installInfo.canInstall && !isInstalled"
                    @click="showInstructions = !showInstructions"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                >
                    <Info class="mr-2 h-4 w-4" />
                    {{ showInstructions ? 'Sembunyikan' : 'Cara Install' }}
                </button>

                <!-- Already Installed Badge -->
                <div
                    v-if="isInstalled"
                    class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                >
                    <Check class="mr-1 h-4 w-4" />
                    Sudah Terinstall
                </div>
            </div>
        </div>

        <!-- Manual Install Instructions -->
        <div v-if="showInstructions" class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <Info class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200">Cara Install di {{ instructions.platform }}</h4>
                    <ol class="mt-2 space-y-1 text-sm text-blue-800 dark:text-blue-300">
                        <li v-for="(step, index) in instructions.steps" :key="index" class="flex items-start">
                            <span
                                class="mt-0.5 mr-2 inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white"
                            >
                                {{ index + 1 }}
                            </span>
                            {{ step }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- PWA Features Info -->
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Keuntungan Install Aplikasi:</h4>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <Zap class="mr-2 h-4 w-4 flex-shrink-0 text-green-500" />
                    Akses lebih cepat
                </div>
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <Smartphone class="mr-2 h-4 w-4 flex-shrink-0 text-green-500" />
                    Shortcut di home screen
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { usePWAInstall } from '@/composables/usePWAInstall';
import { Check, Download, Info, Loader2, Smartphone, Zap } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const { isInstalled, isLoading, installPWA, getInstallInstructions, getInstallationInfo } = usePWAInstall();

const showInstructions = ref(false);

const installInfo = computed(() => getInstallationInfo());
const instructions = computed(() => getInstallInstructions());

const handleInstall = async () => {
    try {
        const success = await installPWA();
        if (success) {
            console.log('PWA installed successfully');
            // Optional: Show success message or redirect
        } else {
            console.log('PWA installation was cancelled or failed');
            // Optional: Show manual instructions
            showInstructions.value = true;
        }
    } catch (error) {
        console.error('Failed to install PWA:', error);
        // Optional: Show error message
    }
};
</script>
