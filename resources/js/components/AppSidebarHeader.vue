<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { useAppearance } from '@/composables/useAppearance';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { appearance, updateAppearance } = useAppearance();
const isDark = computed(() => appearance.value === 'dark');
const toggleTheme = () => updateAppearance(isDark.value ? 'light' : 'dark');
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex w-full items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>

            <button
                type="button"
                @click="toggleTheme"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900"
            >
                <component :is="isDark ? Moon : Sun" class="h-4 w-4" />
                <span>{{ isDark ? 'Dark' : 'Light' }}</span>
            </button>
        </div>
    </header>
</template>
