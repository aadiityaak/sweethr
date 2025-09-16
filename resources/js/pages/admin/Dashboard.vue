<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AttendanceChart from '@/components/AttendanceChart.vue';
// Admin dashboard uses direct URL
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
        href: '/admin/dashboard',
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Generate last 30 days labels for line chart
const generateLast30Days = () => {
    const days = [];
    const today = new Date();

    for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);

        const day = date.getDate();
        const month = date.getMonth() + 1;

        // Format as "DD/MM" for better readability
        days.push(`${day.toString().padStart(2, '0')}/${month.toString().padStart(2, '0')}`);
    }

    return days;
};

// Generate mock attendance data (replace with real data from backend)
const generateMockAttendanceData = () => {
    const data = [];

    for (let i = 0; i < 30; i++) {
        const date = new Date();
        date.setDate(date.getDate() - (29 - i));

        // Skip weekends for more realistic data
        const dayOfWeek = date.getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            data.push(0); // Weekend - no attendance expected
        } else {
            // Generate realistic attendance rates (75-95%)
            const baseRate = 85;
            const variation = Math.random() * 20 - 10; // -10 to +10
            const rate = Math.max(70, Math.min(98, baseRate + variation));
            data.push(Math.round(rate));
        }
    }

    return data;
};
</script>

<template>
    <Head title="Dasbor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Welcome Hero Section -->
            <div class="relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-blue-50 via-indigo-50/80 to-purple-50/60 p-8 shadow-sm dark:border-gray-800/50 dark:from-blue-950/20 dark:via-indigo-950/20 dark:to-purple-950/20">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 backdrop-blur-sm">
                            <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="text-sm font-medium text-blue-600/80 dark:text-blue-400/80">
                            {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </div>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Selamat datang, {{ user.name }}!
                    </h1>
                    <p class="mt-2 flex items-center gap-2 text-gray-600 dark:text-gray-300">
                        <span class="inline-flex items-center rounded-full bg-white/60 px-2.5 py-0.5 text-xs font-medium text-gray-700 backdrop-blur-sm dark:bg-gray-900/60 dark:text-gray-300">
                            {{ user.employee_id }}
                        </span>
                        <span class="text-gray-400">•</span>
                        <span>{{ user.department?.name }}</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ user.position?.title }}</span>
                    </p>
                </div>
                <!-- Decorative elements -->
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-blue-400/10 blur-3xl"></div>
                <div class="absolute -bottom-8 -left-4 h-32 w-32 rounded-full bg-indigo-400/10 blur-3xl"></div>
            </div>

            <!-- 30-Day Attendance Trend (Admin Only) -->
            <div v-if="user.is_admin" class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                        <CheckCircle class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Kehadiran 30 Hari</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Grafik tingkat kehadiran harian</p>
                    </div>
                </div>
                <div class="h-64">
                    <AttendanceChart
                        type="line"
                        :monthly-data="{
                            labels: generateLast30Days(),
                            data: generateMockAttendanceData()
                        }"
                    />
                </div>
            </div>

            <!-- Key Metrics Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Company Overview Card (Admin Only) -->
                <div v-if="user.is_admin" class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/50 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/50">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <Users class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Kehadiran Hari Ini</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ stats.today_present || 0 }} dari {{ stats.total_employees || 0 }} karyawan
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Hadir</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ stats.today_present || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-amber-50/50 px-3 py-2 dark:bg-amber-950/30">
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Tidak Hadir</span>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ (stats.total_employees || 0) - (stats.today_present || 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Tingkat Kehadiran</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ Math.round(((stats.today_present || 0) / (stats.total_employees || 1)) * 100) }}%</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <Link
                            href="/leave-requests"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            <Users class="mr-2 h-4 w-4" />
                            Kelola Karyawan
                        </Link>
                    </div>

                    <!-- Hover effect overlay -->
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <!-- Personal Attendance Card (Non-Admin Only) -->
                <div v-else class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/50 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/50">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <Clock class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Kehadiran Hari Ini</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ todayAttendance ? 'Sudah Masuk' : 'Belum Masuk' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full" :class="todayAttendance?.check_in_time ? 'bg-emerald-400' : 'bg-gray-300'"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Masuk</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatTime(todayAttendance?.check_in_time) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Keluar</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatTime(todayAttendance?.check_out_time) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Durasi</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ formatDuration(todayAttendance?.work_duration) }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <Link
                            v-if="!todayAttendance?.check_in_time"
                            href="/attendance/check-in"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            <Clock class="mr-2 h-4 w-4" />
                            Masuk Sekarang
                        </Link>
                        <Link
                            v-else-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time"
                            href="/attendance"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            <XCircle class="mr-2 h-4 w-4" />
                            Keluar Sekarang
                        </Link>
                        <div
                            v-else
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                        >
                            <CheckCircle class="mr-2 h-4 w-4" />
                            Hari Selesai
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <!-- Attendance Chart (Admin) / Leave Balance (User) -->
                <div v-if="user.is_admin" class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <CheckCircle class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Grafik Kehadiran</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Status kehadiran hari ini</p>
                            </div>
                        </div>
                    </div>

                    <div class="h-48">
                        <AttendanceChart
                            type="doughnut"
                            :attendance-data="{
                                present: stats.today_present || 0,
                                absent: (stats.total_employees || 0) - (stats.today_present || 0),
                                late: 0
                            }"
                        />
                    </div>

                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <div v-else class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.leave_balance }}</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Sisa Cuti</div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-blue-50/50 p-3 dark:bg-blue-950/30">
                        <div class="text-xs font-medium text-blue-700 dark:text-blue-300">Hari tersisa tahun ini</div>
                    </div>
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <!-- Monthly Performance -->
                <div class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/10 ring-1 ring-purple-500/20">
                            <CheckCircle class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ user.is_admin ? stats.monthly_attendance_rate + '%' : stats.monthly_attendance_days }}
                            </div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Bulan Ini</div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-purple-50/50 p-3 dark:bg-purple-950/30">
                        <div class="text-xs font-medium text-purple-700 dark:text-purple-300">
                            {{ user.is_admin ? 'Tingkat kehadiran' : 'Hari hadir' }}
                        </div>
                    </div>
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <!-- Pending Requests -->
                <div class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                            <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ stats.pending_leave_requests }}</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Menunggu</div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-amber-50/50 p-3 dark:bg-amber-950/30">
                        <div class="text-xs font-medium text-amber-700 dark:text-amber-300">
                            {{ user.is_admin ? 'Pengajuan cuti untuk ditinjau' : 'Pengajuan cuti Anda' }}
                        </div>
                    </div>
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>
            </div>

            <!-- Pending Leave Requests Section -->
            <div v-if="pendingLeaves.length > 0" class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 ring-1 ring-amber-500/20">
                        <AlertCircle class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ user.is_admin ? 'Pengajuan Cuti Menunggu' : 'Pengajuan Anda yang Menunggu' }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ pendingLeaves.length }} pengajuan menunggu persetujuan</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div
                        v-for="leave in pendingLeaves"
                        :key="leave.id"
                        class="group relative overflow-hidden rounded-lg border border-gray-200/50 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-gray-700/50 dark:bg-gray-950"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ leave.user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ leave.user.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ leave.user.employee_id }}</p>
                                    </div>
                                </div>
                                <div class="ml-10 space-y-1">
                                    <div class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                        {{ leave.leave_type.name }}
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                                        <span class="font-medium">({{ leave.total_days }} hari)</span>
                                    </p>
                                </div>
                            </div>
                            <div v-if="user.is_admin" class="flex gap-2">
                                <button class="inline-flex items-center rounded-md bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 transition-colors hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-400 dark:ring-emerald-400/30 dark:hover:bg-emerald-950">
                                    <CheckCircle class="mr-1 h-3 w-3" />
                                    Setujui
                                </button>
                                <button class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-400/30 dark:hover:bg-red-950">
                                    <XCircle class="mr-1 h-3 w-3" />
                                    Tolak
                                </button>
                            </div>
                        </div>
                        <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Content Grid -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Announcements -->
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pengumuman Terbaru</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Informasi penting dari perusahaan</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div
                            v-for="announcement in announcements.slice(0, 3)"
                            :key="announcement.id"
                            class="group relative overflow-hidden rounded-lg border border-gray-200/50 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-gray-700/50 dark:bg-gray-950"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    <Calendar class="h-3 w-3" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900 dark:text-white truncate">{{ announcement.title }}</h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ announcement.message }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <Clock class="h-2.5 w-2.5 text-gray-500 dark:text-gray-400" />
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(announcement.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"></div>
                        </div>
                        <div v-if="announcements.length === 0" class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400">
                            <Calendar class="mb-3 h-8 w-8 opacity-50" />
                            <p class="text-sm">Belum ada pengumuman</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20">
                            <CheckCircle class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aksi Cepat</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pintasan untuk tugas sehari-hari</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <Link
                            href="/leave-requests/create"
                            class="group flex w-full items-center gap-3 rounded-lg border border-gray-200/50 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-blue-300 dark:border-gray-700/50 dark:bg-gray-950 dark:hover:border-blue-600"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20 group-hover:bg-blue-500/20">
                                <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Ajukan Cuti</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Buat permintaan cuti baru</p>
                            </div>
                        </Link>
                        <Link
                            href="/attendance"
                            class="group flex w-full items-center gap-3 rounded-lg border border-gray-200/50 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-emerald-300 dark:border-gray-700/50 dark:bg-gray-950 dark:hover:border-emerald-600"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20 group-hover:bg-emerald-500/20">
                                <Clock class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Lihat Kehadiran</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Riwayat kehadiran Anda</p>
                            </div>
                        </Link>
                        <Link
                            v-if="user.is_admin"
                            href="/leave-requests"
                            class="group flex w-full items-center gap-3 rounded-lg border border-gray-200/50 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-amber-300 dark:border-gray-700/50 dark:bg-gray-950 dark:hover:border-amber-600"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 ring-1 ring-amber-500/20 group-hover:bg-amber-500/20">
                                <Users class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Kelola Cuti</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Tinjau pengajuan karyawan</p>
                            </div>
                        </Link>
                        <Link
                            v-if="user.is_admin"
                            href="/employees"
                            class="group flex w-full items-center gap-3 rounded-lg border border-gray-200/50 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-purple-300 dark:border-gray-700/50 dark:bg-gray-950 dark:hover:border-purple-600"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20 group-hover:bg-purple-500/20">
                                <Users class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Kelola Karyawan</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Data karyawan dan departemen</p>
                            </div>
                        </Link>
                        <Link
                            v-if="user.is_admin"
                            href="/office-locations"
                            class="group flex w-full items-center gap-3 rounded-lg border border-gray-200/50 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-green-300 dark:border-gray-700/50 dark:bg-gray-950 dark:hover:border-green-600"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/10 ring-1 ring-green-500/20 group-hover:bg-green-500/20">
                                <CheckCircle class="h-4 w-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">Lokasi Kantor</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Kelola lokasi dan radius</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>