<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import { useCompanySettings } from '@/composables/useCompanySettings';
import { home } from '@/routes';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);

const { companyName, companyTagline } = useCompanySettings();
</script>

<template>
    <Head title="Login" />

    <div class="min-h-screen bg-white text-[#25282b]">
        <div class="mx-auto flex min-h-screen max-w-[480px] flex-col">
            <div class="bg-white px-6 py-10">
                <div class="flex items-center justify-between gap-4">
                    <Link :href="home()" class="inline-flex min-w-0 items-center gap-3">
                        <img src="/icons/logo.jpg" alt="logo" class="h-10 w-10 shrink-0 object-contain" />
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold tracking-wide uppercase text-[#25282b]">{{ companyName }}</div>
                            <div class="truncate text-xs text-[#7e7e7e]">{{ companyTagline }}</div>
                        </div>
                    </Link>
                </div>

            </div>

            <div class="h-12 w-full bg-[#e60000]" />

            <div class="mt-10">
                <h1 class="mt-5 text-[clamp(2.1rem,8vw,3.2rem)] font-extrabold leading-[0.92] tracking-[-0.05em] text-[#25282b] uppercase">
                    Login.
                </h1>
                <p class="mt-6 text-base leading-relaxed text-[#7e7e7e]">
                    Masuk untuk mengakses absensi, pengajuan cuti, dan fitur HR lainnya.
                </p>
            </div>

            <div class="flex-1 bg-white px-6 py-10">
                <div class="mx-auto flex min-h-full w-full max-w-md flex-col justify-center">

                    <div v-if="status" class="mb-6 rounded-[2px] border border-[#333333] bg-[#f2f2f2] px-4 py-3 text-sm text-[#25282b]">
                        {{ status }}
                    </div>

                    <Form v-bind="AuthenticatedSessionController.store.form()" :reset-on-success="['password']" v-slot="{ errors, processing }" class="flex flex-col gap-8">
                        <div class="space-y-6">
                            <div class="space-y-3">
                                <Label for="email" class="text-sm font-semibold text-[#25282b]">Email</Label>
                                <div class="relative">
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        autocapitalize="off"
                                        autocomplete="email"
                                        autocorrect="off"
                                        spellcheck="false"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        placeholder="Masukkan email Anda"
                                        class="h-12 min-h-[48px] touch-manipulation rounded-[2px] border border-[#333333] bg-white text-[#25282b] placeholder:text-[#7e7e7e] focus-visible:ring-0"
                                    />
                                </div>
                                <InputError :message="errors.email" class="text-xs text-[#e60000]" />
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <Label for="password" class="text-sm font-semibold text-[#25282b]">Password</Label>
                                    <Link
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="text-xs font-semibold text-[#3860be] underline decoration-[#3860be] underline-offset-4"
                                        :tabindex="5"
                                    >
                                        Lupa password?
                                    </Link>
                                </div>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        name="password"
                                        autocapitalize="none"
                                        autocorrect="off"
                                        spellcheck="false"
                                        autocomplete="current-password"
                                        required
                                        :tabindex="2"
                                        placeholder="Masukkan password Anda"
                                        class="h-12 min-h-[48px] touch-manipulation rounded-[2px] border border-[#333333] bg-white pr-12 text-[#25282b] placeholder:text-[#7e7e7e] focus-visible:ring-0"
                                    />
                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-[#7e7e7e] hover:text-[#25282b]"
                                        @click="showPassword = !showPassword"
                                        aria-label="Toggle password visibility"
                                        tabindex="-1"
                                    >
                                        <Eye v-if="!showPassword" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <InputError :message="errors.password" class="text-xs text-[#e60000]" />
                            </div>

                            <div class="flex items-center justify-between py-2">
                                <Label for="remember" class="flex cursor-pointer items-center space-x-3">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        :tabindex="3"
                                        class="rounded-[2px] border border-[#333333] data-[state=checked]:bg-[#e60000] data-[state=checked]:text-white"
                                    />
                                    <span class="text-sm text-[#7e7e7e]">Ingat saya</span>
                                </Label>
                            </div>

                            <button
                                type="submit"
                                class="inline-flex h-12 min-h-[48px] w-full touch-manipulation items-center justify-center rounded-[2px] bg-[#e60000] px-[10px] py-3 text-[14.4px] font-bold leading-none tracking-[0.144px] text-white hover:opacity-90 disabled:opacity-60"
                                :tabindex="4"
                                :disabled="processing"
                                data-test="login-button"
                            >
                                <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                <span v-if="processing">Memproses...</span>
                                <span v-else>Masuk ke Akun</span>
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="text-sm text-[#7e7e7e]">
                                Belum punya akun?
                                <Link :href="register()" :tabindex="5" class="ml-1 font-semibold text-[#3860be] underline decoration-[#3860be] underline-offset-4">
                                    Daftar sekarang
                                </Link>
                            </p>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>
