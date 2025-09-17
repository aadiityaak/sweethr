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
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                    Modern HR Management
                    <span class="text-indigo-600 dark:text-indigo-400">System</span>
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Streamline your HR processes with our comprehensive employee management, attendance tracking,
                    and leave management system designed for modern workplaces.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <Link
                        href="/login"
                        class="rounded-md bg-indigo-600 px-6 py-3 text-lg font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
                    >
                        Get Started
                    </Link>
                    <a
                        href="#features"
                        class="text-lg font-semibold leading-6 text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                    >
                        Learn more <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>

            <!-- Features Section -->
            <div id="features" class="mt-32">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                        Everything you need to manage your team
                    </h2>
                    <p class="mt-4 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        Comprehensive HR tools that grow with your business
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Employee Management -->
                    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                            <UsersIcon class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">
                            Employee Management
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            Complete employee data management with role hierarchy, departments, and positions.
                        </p>
                    </div>

                    <!-- Attendance Tracking -->
                    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                            <ClockIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">
                            Smart Attendance
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            Location-based attendance with GPS validation and face recognition for accurate tracking.
                        </p>
                    </div>

                    <!-- Leave Management -->
                    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10">
                            <CalendarIcon class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">
                            Leave Management
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            Streamlined leave requests with approval workflows and balance tracking.
                        </p>
                    </div>

                    <!-- Shift Management -->
                    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10">
                            <Cog6ToothIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">
                            Flexible Shifts
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            Support for multiple work shifts with automatic overtime calculation.
                        </p>
                    </div>

                    <!-- Reports & Analytics -->
                    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                            <ChartBarIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">
                            Reports & Analytics
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            Comprehensive reporting with Excel export and data insights.
                        </p>
                    </div>

                    <!-- Security -->
                    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10">
                            <ShieldCheckIcon class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">
                            Enterprise Security
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">
                            Role-based access control with two-factor authentication and data protection.
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mt-32 text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Ready to get started?
                </h2>
                <p class="mt-4 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Join thousands of companies using SweetHR to manage their workforce
                </p>
                <div class="mt-8">
                    <Link
                        href="/login"
                        class="rounded-md bg-indigo-600 px-8 py-3 text-lg font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
                    >
                        Start Your Free Trial
                    </Link>
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
import {
    UsersIcon,
    ClockIcon,
    CalendarIcon,
    Cog6ToothIcon,
    ChartBarIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline'

// Define the component meta
defineOptions({
    layout: null, // No layout for public pages
})
</script>