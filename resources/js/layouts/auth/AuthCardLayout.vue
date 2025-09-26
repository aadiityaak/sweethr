<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link :href="home()" class="flex items-center gap-3 self-center font-medium">
                <img
                    v-if="companyLogo"
                    :src="companyLogo"
                    :alt="companyName"
                    class="h-10 w-10 object-contain"
                    @error="console.error('Failed to load logo:', companyLogo)"
                />
                <AppLogoIcon v-else class="size-10 fill-current text-primary" />
                <span class="text-lg font-semibold text-foreground">{{ companyName }}</span>
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="rounded-xl">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
