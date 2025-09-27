<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { useCompanySettings } from '@/composables/useCompanySettings';

defineProps<{
    title?: string;
    description?: string;
}>();

const { companyName, companyLogo } = useCompanySettings();
</script>

<template>
    <div class="mx-auto max-w-[480px] bg-gradient-to-br from-background via-background to-muted/20 min-h-screen">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-white/[0.02] bg-[size:60px_60px]" />
        <div class="absolute inset-0 bg-gradient-to-t from-transparent via-background/50 to-background" />

        <!-- Main Content -->
        <div class="relative flex flex-1 items-center justify-center p-6">
            <div class="w-full max-w-md">
                <!-- Card Container -->
                <div class="relative">
                    <!-- Glow Effect -->
                    <div class="absolute -inset-0.5 rounded-2xl bg-gradient-to-r from-primary/20 via-primary/10 to-primary/20 opacity-75 blur"></div>

                    <!-- Main Card -->
                    <div class="relative rounded-2xl border bg-card/50 backdrop-blur-xl p-8 shadow-2xl">
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
                                    <div class="text-xl font-bold bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text text-transparent">
                                        {{ companyName }}
                                    </div>
                                </Link>

                                <!-- Title and Description -->
                                <div class="space-y-2 text-center">
                                    <h1 class="text-xl font-semibold text-foreground">{{ title }}</h1>
                                    <p class="text-sm text-muted-foreground leading-relaxed">{{ description }}</p>
                                </div>
                            </div>

                            <!-- Form Content -->
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="absolute bottom-6 left-0 right-0 text-center">
            <p class="text-xs text-muted-foreground">
                © {{ new Date().getFullYear() }} {{ companyName }}. Sistem Manajemen SDM Modern
            </p>
        </div>
    </div>
</template>
