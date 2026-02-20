<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useCompanySettings } from '@/composables/useCompanySettings';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();

const { companyName, companyLogo } = useCompanySettings();
</script>

<template>
    <div class="mx-auto min-h-screen max-w-[480px] bg-background">
        <!-- Main Content -->
        <div class="flex flex-1 items-center justify-center p-6">
            <div class="w-full max-w-md">
                <!-- Main Card -->
                <div class="rounded-lg border bg-card p-8">
                    <div class="flex flex-col gap-8">
                        <!-- Logo and Branding -->
                        <div class="flex flex-col items-center gap-6">
                            <Link :href="home()" class="group flex flex-col items-center gap-3 transition-all duration-300">
                                <!-- Logo Container -->
                                <img
                                    v-if="companyLogo"
                                    :src="companyLogo"
                                    :alt="companyName"
                                    class="h-16 w-16 object-contain drop-shadow-lg"
                                    @error="console.error('Failed to load logo:', companyLogo)"
                                />
                                <AppLogoIcon v-else class="size-16 fill-primary drop-shadow-lg" />
                                <!-- Company Name -->
                                <div class="bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text text-xl font-bold text-transparent">
                                    {{ companyName }}
                                </div>
                            </Link>

                            <!-- Title and Description -->
                            <div class="space-y-2 text-center">
                                <h1 class="text-xl font-semibold text-foreground">{{ title }}</h1>
                                <p class="text-sm leading-relaxed text-muted-foreground">{{ description }}</p>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <slot />
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
