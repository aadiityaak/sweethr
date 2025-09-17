<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 dark:bg-gray-900/80 dark:border-gray-700">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            SweetHR
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link
                            href="/login"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                        >
                            Login
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl mb-4">
                    Selamat Datang di
                    <span class="text-indigo-600 dark:text-indigo-400">SweetHR</span>
                </h1>

                <!-- Motivational Quote -->
                <div class="mt-8 mb-12">
                    <p class="text-2xl font-medium text-indigo-600 dark:text-indigo-400 mb-2 transition-all duration-500">
                        "{{ currentQuote.text }}"
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 transition-all duration-500">
                        - {{ currentQuote.author }}
                    </p>
                </div>

            </div>

        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-900 mt-32">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            © {{ new Date().getFullYear() }} SweetHR. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

// Define the component meta
defineOptions({
    layout: null, // No layout for public pages
})

// Motivational quotes
const quotes = [
    {
        text: "Sukses adalah hasil dari persiapan, kerja keras, dan belajar dari kegagalan.",
        author: "Colin Powell"
    },
    {
        text: "Cara untuk memulai adalah berhenti berbicara dan mulai melakukan.",
        author: "Walt Disney"
    },
    {
        text: "Kesempatan emas tidak akan datang dua kali. Manfaatkan hari ini dengan sebaik-baiknya.",
        author: "Benjamin Franklin"
    },
    {
        text: "Tidak ada yang mustahil bagi mereka yang mau berusaha.",
        author: "Alexander The Great"
    },
    {
        text: "Kegagalan adalah kesuksesan yang tertunda.",
        author: "William Arthur Ward"
    },
    {
        text: "Kerja keras mengalahkan bakat ketika bakat tidak bekerja keras.",
        author: "Tim Notke"
    },
    {
        text: "Mimpi besar, bekerja keras, tetap fokus, dan kelilingi diri Anda dengan orang-orang baik.",
        author: "Serena Williams"
    },
    {
        text: "Sukses bukanlah final, kegagalan bukanlah fatal: yang penting adalah keberanian untuk melanjutkan.",
        author: "Winston Churchill"
    },
    {
        text: "Jangan menunggu kesempatan. Ciptakanlah.",
        author: "George Bernard Shaw"
    },
    {
        text: "Tim yang bekerja bersama, menang bersama.",
        author: "Vince Lombardi"
    }
]

const currentQuote = ref(quotes[0])

// Function to get random quote
const getRandomQuote = () => {
    const randomIndex = Math.floor(Math.random() * quotes.length)
    currentQuote.value = quotes[randomIndex]
}

// Change quote every time component mounts
onMounted(() => {
    getRandomQuote()

    // Change quote every 5 seconds
    setInterval(() => {
        getRandomQuote()
    }, 5000)
})
</script>