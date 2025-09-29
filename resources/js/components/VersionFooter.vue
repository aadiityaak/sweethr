<template>
    <div class="mt-8 border-t border-border bg-background/50 px-6 py-4">
        <div class="flex items-center justify-between text-xs text-muted-foreground">
            <div class="flex items-center gap-2">
                <span>&copy; {{ currentYear }} {{ companyName }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-mono">{{ versionInfo.fullVersion }}</span>
                <button @click="showDetails = !showDetails" class="rounded p-1 transition-colors hover:bg-accent" title="Version Details">
                    <Info class="h-3 w-3" />
                </button>
            </div>
        </div>

        <!-- Version Details Modal -->
        <div v-if="showDetails" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="showDetails = false">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Version Information</h3>
                    <button @click="showDetails = false" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Application:</span>
                        <span class="font-mono text-sm">{{ companyName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Version:</span>
                        <span class="font-mono text-sm">{{ versionInfo.fullVersion }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Build Date:</span>
                        <span class="font-mono text-sm">{{ formattedBuildDate }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Environment:</span>
                        <span class="font-mono text-sm capitalize">{{ versionInfo.environment }}</span>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <p class="text-center text-xs text-gray-500 dark:text-gray-400">Human Resource Management System</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useCompanySettings } from '@/composables/useCompanySettings';
import { Info, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface VersionInfo {
    version: string;
    fullVersion: string;
    buildDate: string;
    timestamp: string;
    environment: string;
}

const { companyName } = useCompanySettings();
const showDetails = ref(false);
const versionInfo = ref<VersionInfo>({
    version: '1.0.0',
    fullVersion: 'v1.0.0.20250926.2303',
    buildDate: new Date().toISOString(),
    timestamp: '20250926.2303',
    environment: 'development',
});

const currentYear = computed(() => new Date().getFullYear());

const formattedBuildDate = computed(() => {
    return new Date(versionInfo.value.buildDate).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
});

const loadVersionInfo = async () => {
    try {
        // Try to load from public/version.json
        const response = await fetch('/version.json');
        if (response.ok) {
            const data = await response.json();
            versionInfo.value = data;
        }
    } catch (error) {
        console.warn('Could not load version info, using default');
    }
};

onMounted(() => {
    loadVersionInfo();
});
</script>
