<template>
    <!-- Mobile-only Layout -->
    <div class="mx-auto flex min-h-screen max-w-[480px] flex-col bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <!-- Navigation -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md dark:border-gray-700 dark:bg-gray-900/80">
            <div class="px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ companyName }}
                        </h1>
                    </div>
                    <div class="flex items-center">
                        <Link
                            href="/login"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex flex-1 items-center justify-center px-4 py-12">
            <div class="text-center">
                <h1 class="mb-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Selamat Datang di
                    <span class="text-indigo-600 dark:text-indigo-400">{{ companyName }}</span>
                </h1>

                <!-- Motivational Quote -->
                <div class="mt-6 mb-8">
                    <p class="mb-2 px-4 text-lg font-medium text-indigo-600 transition-all duration-500 dark:text-indigo-400">
                        "{{ currentQuote.text }}"
                    </p>
                    <p class="text-sm text-gray-500 transition-all duration-500 dark:text-gray-400">- {{ currentQuote.author }}</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-auto bg-white dark:bg-gray-900">
            <div class="px-4 pb-8">
                <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            © {{ new Date().getFullYear() }} {{ companyName }}. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { useCompanySettings } from '@/composables/useCompanySettings';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// Define the component meta
defineOptions({
    layout: null, // No layout for public pages
});

const { companyName } = useCompanySettings();

// Motivational quotes
const quotes = [
    {
        text: 'Sukses adalah hasil dari persiapan, kerja keras, dan belajar dari kegagalan.',
        author: 'Colin Powell',
    },
    {
        text: 'Cara untuk memulai adalah berhenti berbicara dan mulai melakukan.',
        author: 'Walt Disney',
    },
    {
        text: 'Kesempatan emas tidak akan datang dua kali. Manfaatkan hari ini dengan sebaik-baiknya.',
        author: 'Benjamin Franklin',
    },
    {
        text: 'Tidak ada yang mustahil bagi mereka yang mau berusaha.',
        author: 'Alexander The Great',
    },
    {
        text: 'Kegagalan adalah kesuksesan yang tertunda.',
        author: 'William Arthur Ward',
    },
    {
        text: 'Kerja keras mengalahkan bakat ketika bakat tidak bekerja keras.',
        author: 'Tim Notke',
    },
    {
        text: 'Mimpi besar, bekerja keras, tetap fokus, dan kelilingi diri Anda dengan orang-orang baik.',
        author: 'Serena Williams',
    },
    {
        text: 'Sukses bukanlah final, kegagalan bukanlah fatal: yang penting adalah keberanian untuk melanjutkan.',
        author: 'Winston Churchill',
    },
    {
        text: 'Jangan menunggu kesempatan. Ciptakanlah.',
        author: 'George Bernard Shaw',
    },
    {
        text: 'Tim yang bekerja bersama, menang bersama.',
        author: 'Vince Lombardi',
    },
];

const currentQuote = ref(quotes[0]);

// Function to get random quote
const getRandomQuote = () => {
    const randomIndex = Math.floor(Math.random() * quotes.length);
    currentQuote.value = quotes[randomIndex];
};

// Change quote every time component mounts
onMounted(() => {
    getRandomQuote();

    // Change quote every 5 seconds
    setInterval(() => {
        getRandomQuote();
    }, 5000);
});
</script>
