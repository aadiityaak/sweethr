<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Users, Calendar, CheckCircle, XCircle, AlertCircle } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    employee_id: string;
    is_admin: boolean;
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

interface PendingLeave {
    id: number;
    start_date: string;
    end_date: string;
    total_days: number;
    user: {
        id: number;
        name: string;
        employee_id: string;
    };
    leave_type: {
        id: number;
        name: string;
    };
}

interface Stats {
    total_employees?: number;
    departments_count?: number;
    today_present?: number;
    monthly_attendance_rate?: number;
    pending_leave_requests?: number;
    monthly_overtime_hours?: number;
    monthly_attendance_days?: number;
    monthly_late_days?: number;
    monthly_work_hours?: number;
    leave_balance?: number;
    department_stats?: Array<{
        department: string;
        total_employees: number;
        present_today: number;
        attendance_rate: number;
    }>;
}

interface Props {
    user: User;
    todayAttendance: TodayAttendance | null;
    pendingLeaves: PendingLeave[];
    stats: Stats;
    announcements: Array<{
        id: number;
        title: string;
        message: string;
        created_at: string;
    }>;
}

const { user, todayAttendance, pendingLeaves, stats, announcements } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: dashboard().url,
    },
];

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
</script>

<template>
    <Head title="Dasbor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Welcome Section -->
            <div class="rounded-xl border border-sidebar-border/70 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 dark:border-sidebar-border dark:from-blue-950/30 dark:to-indigo-950/30">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Selamat datang kembali, {{ user.name }}!
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-300">
                    {{ user.employee_id }} • {{ user.department?.name }} • {{ user.position?.title }}
                </p>
            </div>

            <!-- Quick Actions & Today's Status -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Today's Attendance -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <Clock class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Kehadiran Hari Ini</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ todayAttendance ? 'Sudah Masuk' : 'Belum Masuk' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-300">Masuk:</span>
                            <span class="font-medium">{{ formatTime(todayAttendance?.check_in_time) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-300">Keluar:</span>
                            <span class="font-medium">{{ formatTime(todayAttendance?.check_out_time) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-300">Durasi:</span>
                            <span class="font-medium">{{ formatDuration(todayAttendance?.work_duration) }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <Link
                            v-if="!todayAttendance?.check_in_time"
                            href="/attendance/check-in"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                        >
                            Masuk
                        </Link>
                        <Link
                            v-else-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time"
                            href="/attendance"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        >
                            Keluar
                        </Link>
                        <div
                            v-else
                            class="inline-flex w-full items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                        >
                            Hari Selesai
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div v-if="user.is_admin" class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Karyawan</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ stats.total_employees }}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ stats.today_present }} hadir hari ini
                    </div>
                </div>

                <div v-else class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Sisa Cuti</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ stats.leave_balance }}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Hari tersisa tahun ini
                    </div>
                </div>

                <!-- Monthly Stats -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <CheckCircle class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Bulan Ini</h3>
                            <p class="text-2xl font-bold text-purple-600">
                                {{ user.is_admin ? stats.monthly_attendance_rate + '%' : stats.monthly_attendance_days }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ user.is_admin ? 'Tingkat kehadiran' : 'Hari hadir' }}
                    </div>
                </div>

                <!-- Pending Items -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <AlertCircle class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Menunggu</h3>
                            <p class="text-2xl font-bold text-orange-600">{{ stats.pending_leave_requests }}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ user.is_admin ? 'Pengajuan cuti untuk ditinjau' : 'Pengajuan cuti Anda' }}
                    </div>
                </div>
            </div>

            <!-- Pending Leave Requests -->
            <div v-if="pendingLeaves.length > 0" class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ user.is_admin ? 'Pengajuan Cuti Menunggu' : 'Pengajuan Anda yang Menunggu' }}
                </h2>
                <div class="space-y-3">
                    <div
                        v-for="leave in pendingLeaves"
                        :key="leave.id"
                        class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ leave.user.name }} ({{ leave.user.employee_id }})
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ leave.leave_type.name }} • {{ leave.start_date }} to {{ leave.end_date }} ({{ leave.total_days }} days)
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button class="rounded-lg bg-green-100 px-3 py-1 text-sm font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50">
                                Setujui
                            </button>
                            <button class="rounded-lg bg-red-100 px-3 py-1 text-sm font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Announcements -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Pengumuman Terbaru</h2>
                <div class="space-y-3">
                    <div
                        v-for="announcement in announcements"
                        :key="announcement.id"
                        class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ announcement.title }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ announcement.message }}</p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ announcement.created_at }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>