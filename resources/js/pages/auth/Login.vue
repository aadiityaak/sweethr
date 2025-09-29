<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <AuthBase title="Masuk ke Akun Anda" description="Silakan masukkan email dan password untuk melanjutkan">
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-center text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300"
        >
            {{ status }}
        </div>

        <Form
            v-bind="AuthenticatedSessionController.store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="space-y-6">
                <!-- Email Field -->
                <div class="space-y-3">
                    <Label for="email" class="text-sm font-medium text-foreground">Email Address</Label>
                    <div class="relative">
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="Masukkan email Anda"
                            class="h-12 min-h-[48px] touch-manipulation rounded-lg border-2 bg-background/50 backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:bg-background focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.email" class="text-xs" />
                </div>

                <!-- Password Field -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-sm font-medium text-foreground">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs text-primary transition-colors duration-200 hover:text-primary/80"
                            :tabindex="5"
                        >
                            Lupa password?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="Masukkan password Anda"
                            class="h-12 min-h-[48px] touch-manipulation rounded-lg border-2 bg-background/50 backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:bg-background focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.password" class="text-xs" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between py-2">
                    <Label for="remember" class="flex cursor-pointer items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            :tabindex="3"
                            class="rounded border-2 transition-all duration-200 focus:ring-2 focus:ring-primary/20"
                        />
                        <span class="text-sm text-muted-foreground">Ingat saya</span>
                    </Label>
                </div>

                <!-- Login Button -->
                <Button
                    type="submit"
                    class="h-12 min-h-[48px] w-full touch-manipulation rounded-lg bg-gradient-to-r from-primary to-primary/90 font-semibold text-primary-foreground shadow-lg transition-all duration-200 hover:shadow-xl hover:shadow-primary/25 disabled:scale-100 disabled:opacity-70"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    <span v-if="processing">Memproses...</span>
                    <span v-else>Masuk ke Akun</span>
                </Button>
            </div>

            <!-- Signup Link -->
            <div class="text-center">
                <p class="text-sm text-muted-foreground">
                    Belum punya akun?
                    <TextLink
                        :href="register()"
                        :tabindex="5"
                        class="font-medium text-primary transition-colors duration-200 hover:text-primary/80 hover:underline"
                    >
                        Daftar sekarang
                    </TextLink>
                </p>
            </div>
        </Form>
    </AuthBase>
</template>
