<template>
    <div class="mx-auto flex h-screen max-w-[480px] flex-col overflow-hidden bg-white text-[#25282b]">
        <header class="shrink-0 bg-white">
            <div class="px-4 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-[#e60000] p-1">
                            <img src="/icons/logo.jpg" alt="logo" class="h-full w-full rounded-full bg-white object-contain" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold tracking-[0.2em] text-[#7e7e7e] uppercase">Sistem HR</p>
                            <p class="truncate text-sm font-semibold tracking-wide text-[#25282b]">{{ companyName }}</p>
                        </div>
                    </div>
                    <Link href="/login" class="shrink-0 rounded-[60px] bg-[#e60000] px-4 py-2 text-xs font-semibold tracking-wide text-white">
                        Login
                    </Link>
                </div>
            </div>
        </header>

        <div class="shrink-0 h-10 bg-[#e60000]"></div>

        <main class="flex-1 bg-white px-4 py-6">
            <div class="flex h-full flex-col justify-center">
                <h1 class="text-3xl font-extrabold leading-[0.95] tracking-[-0.06em] text-[#25282b] uppercase">Selamat Datang.</h1>
                <Transition
                    mode="out-in"
                    enter-active-class="transition duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-300 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <div :key="currentQuoteIndex" class="mt-3">
                        <p class="text-base font-semibold leading-relaxed text-[#25282b]">"{{ currentQuote.text }}"</p>
                        <p class="mt-2 text-sm text-[#7e7e7e]">- {{ currentQuote.author }}</p>
                    </div>
                </Transition>
            </div>
        </main>

        <footer class="bg-[#f2f2f2] text-[#25282b]">
            <div class="px-4 py-5">
                <div class="space-y-5">
                    <div>
                        <p class="text-sm font-extrabold tracking-[0.2em] uppercase">Warungmasmbull HR</p>
                        <p class="mt-2 text-sm leading-relaxed text-[#7e7e7e]">
                            Platform HR mobile untuk kebutuhan operasional harian karyawan dan administrasi perusahaan.
                        </p>
                    </div>
                </div>

                <div class="mt-6 border-t border-black/10 pt-4">
                    <p class="text-xs text-[#7e7e7e]">© {{ year }} {{ companyName }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { useCompanySettings } from '@/composables/useCompanySettings';
import { Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// Define the component meta
defineOptions({
    layout: null, // No layout for public pages
});

const { companyName } = useCompanySettings();

const year = computed(() => new Date().getFullYear());

const quotes = [
    { text: 'Sukses adalah hasil dari persiapan, kerja keras, dan belajar dari kegagalan.', author: 'Colin Powell' },
    { text: 'Cara untuk memulai adalah berhenti berbicara dan mulai melakukan.', author: 'Walt Disney' },
    { text: 'Kesempatan emas tidak akan datang dua kali. Manfaatkan hari ini dengan sebaik-baiknya.', author: 'Benjamin Franklin' },
    { text: 'Tidak ada yang mustahil bagi mereka yang mau berusaha.', author: 'Alexander The Great' },
    { text: 'Kegagalan adalah kesuksesan yang tertunda.', author: 'William Arthur Ward' },
    { text: 'Kerja keras mengalahkan bakat ketika bakat tidak bekerja keras.', author: 'Tim Notke' },
    { text: 'Mimpi besar, bekerja keras, tetap fokus, dan kelilingi diri Anda dengan orang-orang baik.', author: 'Serena Williams' },
    { text: 'Sukses bukanlah final, kegagalan bukanlah fatal: yang penting adalah keberanian untuk melanjutkan.', author: 'Winston Churchill' },
    { text: 'Jangan menunggu kesempatan. Ciptakanlah.', author: 'George Bernard Shaw' },
    { text: 'Tim yang bekerja bersama, menang bersama.', author: 'Vince Lombardi' },
];

const currentQuoteIndex = ref(0);
const currentQuote = computed(() => quotes[currentQuoteIndex.value] ?? quotes[0]);

const pickRandomQuote = () => {
    if (quotes.length <= 1) return;
    let nextIndex = currentQuoteIndex.value;
    while (nextIndex === currentQuoteIndex.value) {
        nextIndex = Math.floor(Math.random() * quotes.length);
    }
    currentQuoteIndex.value = nextIndex;
};

let quoteInterval: number | undefined;

onMounted(() => {
    pickRandomQuote();
    quoteInterval = window.setInterval(() => pickRandomQuote(), 5000);
});

onBeforeUnmount(() => {
    if (quoteInterval) window.clearInterval(quoteInterval);
});
</script>
