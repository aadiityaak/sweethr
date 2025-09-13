<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Calendar, CheckCircle, User, MapPin, LogOut } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    employee_id: string;
    department?: {
        id: number;
        name: string;
    };
    position?: {
        id: number;
        title: string;
    };
}

interface TodayAttendance {
    id: number;
    check_in_time: string | null;
    check_out_time: string | null;
    status: string;
    work_duration: number | null;
    office_location: {
        id: number;
        name: string;
    };
}

interface Props {
    user?: User;
    todayAttendance?: TodayAttendance | null;
    stats?: {
        monthly_attendance_days: number;
        monthly_late_days: number;
        monthly_work_hours: number;
        leave_balance: number;
    };
}

const { user, todayAttendance, stats } = defineProps<Props>();

const formatTime = (time: string | null) => {
    if (!time) return '--:--';
    return time.substring(0, 5);
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return '--';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
};

const getCurrentTime = () => {
    return new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

const getCurrentDate = () => {
    return new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="SweetHR - Employee Portal" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-sm shadow-sm dark:bg-gray-950/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-blue-600 p-2">
                                <User class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">SweetHR</h1>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Employee Portal</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name || 'Guest' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.employee_id || '--' }}</p>
                        </div>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="rounded-lg bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <LogOut class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="mb-8 rounded-xl bg-white p-6 shadow-sm dark:bg-gray-950">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Selamat datang, {{ user?.name || 'Guest' }}!
                    </h2>
                    <p class="mt-1 text-gray-600 dark:text-gray-300">
                        {{ user?.department?.name || 'No Department' }} • {{ user?.position?.title || 'No Position' }}
                    </p>
                    <p class="mt-2 text-lg text-blue-600 dark:text-blue-400">
                        {{ getCurrentDate() }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ getCurrentTime() }}
                    </p>
                </div>
            </div>

            <!-- Today's Attendance -->
            <div class="mb-8 rounded-xl bg-white p-6 shadow-sm dark:bg-gray-950">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Absensi Hari Ini</h3>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="flex items-center gap-3 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <Clock class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Check In</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ formatTime(todayAttendance?.check_in_time) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                        <div class="rounded-lg bg-red-100 p-2 dark:bg-red-900/30">
                            <Clock class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Check Out</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ formatTime(todayAttendance?.check_out_time) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Durasi Kerja</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ formatDuration(todayAttendance?.work_duration) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="todayAttendance?.office_location" class="mt-4 flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                    <MapPin class="h-4 w-4 text-gray-400" />
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        Lokasi: {{ todayAttendance.office_location.name }}
                    </span>
                </div>

                <!-- Attendance Actions -->
                <div class="mt-6 flex gap-4">
                    <Link
                        v-if="!todayAttendance?.check_in_time"
                        href="/attendance/check-in"
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700"
                    >
                        <Clock class="h-5 w-5" />
                        Check In Sekarang
                    </Link>

                    <Link
                        v-else-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time"
                        href="/attendance"
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-600 px-6 py-3 text-white hover:bg-red-700"
                    >
                        <Clock class="h-5 w-5" />
                        Check Out Sekarang
                    </Link>

                    <div
                        v-else
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-100 px-6 py-3 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                    >
                        <CheckCircle class="h-5 w-5" />
                        Absensi Selesai
                    </div>

                    <Link
                        href="/attendance"
                        class="rounded-lg border border-gray-300 px-6 py-3 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Lihat Riwayat
                    </Link>
                </div>
            </div>

            <!-- Monthly Stats -->
            <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-950">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Statistik Bulan Ini</h3>

                <div class="grid gap-4 md:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 p-4 text-center dark:border-gray-700">
                        <div class="mx-auto mb-2 w-fit rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.monthly_attendance_days || 0 }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Hari Masuk</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 text-center dark:border-gray-700">
                        <div class="mx-auto mb-2 w-fit rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <Clock class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.monthly_late_days || 0 }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Hari Terlambat</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 text-center dark:border-gray-700">
                        <div class="mx-auto mb-2 w-fit rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <Clock class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.monthly_work_hours || 0 }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Total Jam Kerja</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 text-center dark:border-gray-700">
                        <div class="mx-auto mb-2 w-fit rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <Calendar class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats?.leave_balance || 0 }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Sisa Cuti</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>