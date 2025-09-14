<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useCompanySettings } from '@/composables/useCompanySettings';

const { companyName, companyLogo, getCurrentLogo } = useCompanySettings();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground overflow-hidden">
        <img
            v-if="companyLogo"
            :src="companyLogo"
            :alt="companyName"
            class="w-full h-full object-contain"
            @error="console.error('Failed to load logo:', companyLogo)"
        />
        <AppLogoIcon v-else class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ companyName }}</span>
    </div>
</template>
