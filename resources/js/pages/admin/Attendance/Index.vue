<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AttendanceChart from '@/components/AttendanceChart.vue';
import { type BreadcrumbItem } from '@/types';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    Users,
    Calendar,
    CheckCircle,
    XCircle,
    AlertCircle,
    Download,
    Filter,
    Search,
    MapPin,
    Eye
} from 'lucide-vue-next';

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

interface AttendanceRecord {
    id: number;
    check_in_time: string | null;
    check_out_time: string | null;
    status: 'present' | 'late' | 'absent' | 'half_day';
    work_duration: number | null;
    late_duration: number | null;
    overtime_duration: number | null;
    date: string;
    user: User;
    office_location: {
        id: number;
        name: string;
        address: string;
    };
    notes?: string;
}

interface AttendanceStats {
    total_employees: number;
    present_today: number;
    late_today: number;
    absent_today: number;
    on_leave_today: number;
    attendance_rate: number;
    average_work_hours: number;
    total_overtime_hours: number;
}

interface Props {
    attendanceRecords: AttendanceRecord[];
    stats: AttendanceStats;
    filters: {
        date?: string;
        status?: string;
        department?: string;
        search?: string;
    };
    departments: Array<{
        id: number;
        name: string;
    }>;
}

const { attendanceRecords, stats, filters, departments } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dasbor', href: '/dashboard' },
    { title: 'Kelola Kehadiran', href: '/admin/attendance' }
];

const formatTime = (time: string | null) => {
    if (!time) return '--:--';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return '--';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours}j ${mins}m` : `${mins}m`;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const getStatusBadge = (status: string) => {
    const badges = {
        present: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-400',
        late: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/50 dark:text-amber-400',
        absent: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-950/50 dark:text-red-400',
        half_day: 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-950/50 dark:text-blue-400'
    };
    return badges[status as keyof typeof badges] || badges.absent;
};

const getStatusText = (status: string) => {
    const statusMap = {
        present: 'Hadir',
        late: 'Terlambat',
        absent: 'Tidak Hadir',
        half_day: 'Setengah Hari'
    };
    return statusMap[status as keyof typeof statusMap] || 'Tidak Diketahui';
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'present': return CheckCircle;
        case 'late': return AlertCircle;
        case 'absent': return XCircle;
        case 'half_day': return Clock;
        default: return XCircle;
    }
};

// Generate mock weekly data for chart
const generateWeeklyData = () => {
    return {
        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        data: [92, 88, 95, 89, 91, 0, 0] // Weekend = 0
    };
};

// Generate mock attendance breakdown
const generateAttendanceBreakdown = () => {
    return {
        present: stats.present_today,
        absent: stats.absent_today,
        late: stats.late_today
    };
};
</script>

<template>
    <Head title="Kelola Kehadiran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Kelola Kehadiran
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Pantau dan kelola kehadiran seluruh karyawan
                    </p>
                </div>
                <div class="flex gap-3">
                    <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <Filter class="mr-2 h-4 w-4" />
                        Filter
                    </button>
                    <button class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Present Today -->
                <div class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                            <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.present_today }}</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Hadir Hari Ini</div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-emerald-50/50 p-3 dark:bg-emerald-950/30">
                        <div class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                            {{ Math.round((stats.present_today / stats.total_employees) * 100) }}% dari total karyawan
                        </div>
                    </div>
                </div>

                <!-- Late Today -->
                <div class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                            <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ stats.late_today }}</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Terlambat</div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-amber-50/50 p-3 dark:bg-amber-950/30">
                        <div class="text-xs font-medium text-amber-700 dark:text-amber-300">
                            {{ Math.round((stats.late_today / stats.total_employees) * 100) }}% dari total karyawan
                        </div>
                    </div>
                </div>

                <!-- Absent Today -->
                <div class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-red-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-red-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/20">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.absent_today }}</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Tidak Hadir</div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg bg-red-50/50 p-3 dark:bg-red-950/30">
                        <div class="text-xs font-medium text-red-700 dark:text-red-300">
                            {{ Math.round((stats.absent_today / stats.total_employees) * 100) }}% dari total karyawan
                        </div>
                    </div>
                </div>

                <!-- Attendance Rate -->
                <div class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.attendance_rate }}%</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Tingkat Kehadiran</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Progress</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all" :style="`width: ${stats.attendance_rate}%`"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Weekly Attendance Chart -->
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                            <Calendar class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Kehadiran Mingguan</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Persentase kehadiran per hari</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <AttendanceChart
                            type="bar"
                            :weekly-data="generateWeeklyData()"
                        />
                    </div>
                </div>

                <!-- Attendance Breakdown -->
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <CheckCircle class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Breakdown Kehadiran</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status kehadiran hari ini</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <AttendanceChart
                            type="doughnut"
                            :attendance-data="generateAttendanceBreakdown()"
                        />
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="rounded-xl border border-gray-200/50 bg-white p-4 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1 max-w-md">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            type="text"
                            placeholder="Cari karyawan..."
                            class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-400"
                            :value="filters.search"
                        />
                    </div>
                    <div class="flex gap-3">
                        <select class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">Semua Status</option>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="half_day">Setengah Hari</option>
                        </select>
                        <select class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">Semua Departemen</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                        <DatePicker
                            :model-value="filters.date"
                            placeholder="Pilih tanggal"
                        />
                    </div>
                </div>
            </div>

            <!-- Attendance Records Table -->
            <div class="rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="border-b border-gray-200/50 p-6 dark:border-gray-800/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Catatan Kehadiran
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ attendanceRecords.length }} catatan kehadiran ditemukan
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200/50 bg-gray-50/50 dark:border-gray-800/50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Check In
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Check Out
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Durasi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Lokasi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/50 dark:divide-gray-800/50">
                            <tr
                                v-for="record in attendanceRecords"
                                :key="record.id"
                                class="group hover:bg-gray-50/50 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ record.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ record.user.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ record.user.employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(record.date) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatTime(record.check_in_time) }}
                                    </div>
                                    <div v-if="record.late_duration" class="text-xs text-amber-600 dark:text-amber-400">
                                        Terlambat {{ formatDuration(record.late_duration) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatTime(record.check_out_time) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatDuration(record.work_duration) }}
                                    </div>
                                    <div v-if="record.overtime_duration" class="text-xs text-blue-600 dark:text-blue-400">
                                        Lembur {{ formatDuration(record.overtime_duration) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <component
                                            :is="getStatusIcon(record.status)"
                                            class="h-3 w-3"
                                        />
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                                            :class="getStatusBadge(record.status)"
                                        >
                                            {{ getStatusText(record.status) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <MapPin class="h-3 w-3" />
                                        {{ record.office_location.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <button class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700">
                                        <Eye class="mr-1 h-3 w-3" />
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>