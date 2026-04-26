<template>
    <div class="mx-auto flex h-screen max-w-[480px] flex-col overflow-hidden bg-white text-[#25282b]">
        <header class="flex-1 bg-[#25282b]">
            <div class="px-4 pt-6 pb-8">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-[#e60000] p-1">
                            <img src="/icons/logo.jpg" alt="logo" class="h-full w-full rounded-full bg-white object-contain" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold tracking-[0.2em] text-white/70 uppercase">Sistem HR</p>
                            <p class="truncate text-sm font-semibold tracking-wide text-white">{{ companyName }}</p>
                        </div>
                    </div>
                    <Link href="/login" class="shrink-0 rounded-[24px] bg-white/10 px-4 py-2 text-xs font-semibold tracking-wide text-white">
                        Login
                    </Link>
                </div>

                <div class="mt-10">
                    <h1 class="text-4xl font-extrabold leading-[0.9] tracking-[-0.06em] text-white uppercase">Selamat Datang.</h1>
                    <p class="mt-5 text-base leading-relaxed text-white/80">
                        Kelola absensi, cuti, slip gaji, dan LMS dalam satu aplikasi mobile.
                    </p>
                </div>
            </div>
        </header>

        <div class="h-14 bg-[#e60000]"></div>

        <footer class="bg-[#25282b] text-white">
            <div class="px-4 py-6">
                <div class="space-y-5">
                    <div>
                        <p class="text-sm font-extrabold tracking-[0.2em] uppercase">Perusahaan</p>
                        <p class="mt-2 text-sm leading-relaxed text-white/70">
                            Platform HR mobile untuk kebutuhan operasional harian karyawan dan administrasi perusahaan.
                        </p>
                    </div>
                </div>

                <div class="mt-8 border-t border-white/25 pt-4">
                    <p class="text-xs text-white/70">© {{ year }} {{ companyName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { useCompanySettings } from '@/composables/useCompanySettings';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Define the component meta
defineOptions({
    layout: null, // No layout for public pages
});

const { companyName } = useCompanySettings();

const year = computed(() => new Date().getFullYear());
</script>
