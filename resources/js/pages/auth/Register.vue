<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
    <AuthBase title="Buat Akun Baru" description="Silakan lengkapi data di bawah untuk membuat akun Anda">
        <Head title="Register" />

        <Form
            v-bind="RegisteredUserController.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="space-y-6">
                <!-- Name Field -->
                <div class="space-y-3">
                    <Label for="name" class="text-sm font-medium text-foreground">Nama Lengkap</Label>
                    <div class="relative">
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            name="name"
                            placeholder="Masukkan nama lengkap Anda"
                            class="h-12 min-h-[48px] touch-manipulation rounded-lg border bg-background transition-all duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.name" class="text-xs" />
                </div>

                <!-- Email Field -->
                <div class="space-y-3">
                    <Label for="email" class="text-sm font-medium text-foreground">Email Address</Label>
                    <div class="relative">
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="email@example.com"
                            class="h-12 min-h-[48px] touch-manipulation rounded-lg border bg-background transition-all duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.email" class="text-xs" />
                </div>

                <!-- Password Field -->
                <div class="space-y-3">
                    <Label for="password" class="text-sm font-medium text-foreground">Password</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="Masukkan password Anda"
                            class="h-12 min-h-[48px] touch-manipulation rounded-lg border bg-background transition-all duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.password" class="text-xs" />
                </div>

                <!-- Confirm Password Field -->
                <div class="space-y-3">
                    <Label for="password_confirmation" class="text-sm font-medium text-foreground">Konfirmasi Password</Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Konfirmasi password Anda"
                            class="h-12 min-h-[48px] touch-manipulation rounded-lg border bg-background transition-all duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <InputError :message="errors.password_confirmation" class="text-xs" />
                </div>

                <!-- Register Button -->
                <Button
                    type="submit"
                    class="h-12 min-h-[48px] w-full touch-manipulation rounded-lg bg-primary font-semibold text-primary-foreground transition-colors duration-200 hover:bg-primary/90 disabled:opacity-70"
                    :tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    <span v-if="processing">Memproses...</span>
                    <span v-else>Buat Akun</span>
                </Button>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-sm text-muted-foreground">
                    Sudah punya akun?
                    <TextLink
                        :href="login()"
                        :tabindex="6"
                        class="font-medium text-primary transition-colors duration-200 hover:text-primary/80 hover:underline"
                    >
                        Masuk sekarang
                    </TextLink>
                </p>
            </div>
        </Form>
    </AuthBase>
</template>
