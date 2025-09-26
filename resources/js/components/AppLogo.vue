<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useCompanySettings } from '@/composables/useCompanySettings';

const { companyName, companyLogo, getCurrentLogo } = useCompanySettings();
</script>

<template>
    <img
        v-if="companyLogo"
        :src="companyLogo"
        :alt="companyName"
        class="size-8 object-contain"
        @error="console.error('Failed to load logo:', companyLogo)"
    />
    <AppLogoIcon v-else class="size-8 fill-current text-sidebar-primary" />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ companyName }}</span>
    </div>
</template>
