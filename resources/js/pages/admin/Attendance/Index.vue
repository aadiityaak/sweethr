<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import AttendanceChart from '@/components/AttendanceChart.vue';
import { type BreadcrumbItem } from '@/types';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted } from 'vue';
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
    Eye,
    Edit,
    Trash2,
    MoreHorizontal,
    Plus
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
        office_location?: string;
        search?: string;
    };
    departments: Array<{
        id: number;
        name: string;
    }>;
    officeLocations: Array<{
        id: number;
        name: string;
    }>;
}

const { attendanceRecords, stats, filters, departments, officeLocations } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dasbor', href: '/dashboard' },
    { title: 'Kelola Kehadiran', href: '/admin/attendance' }
];

// Reactive filter state
const searchQuery = ref(filters.search || '');
const selectedStatus = ref(filters.status || '');
const selectedDepartment = ref(filters.department || '');
const selectedOfficeLocation = ref(filters.office_location || '');
const selectedDate = ref(filters.date || '');

// Action dropdown state
const activeDropdown = ref<number | null>(null);

// Debounce search
let searchTimeout: NodeJS.Timeout;

// Filter functions
const updateFilters = () => {
    const newFilters = {
        search: searchQuery.value || undefined,
        status: selectedStatus.value || undefined,
        department: selectedDepartment.value || undefined,
        office_location: selectedOfficeLocation.value || undefined,
        date: selectedDate.value || undefined,
    };

    // Remove empty values
    Object.keys(newFilters).forEach(key => {
        if (!newFilters[key as keyof typeof newFilters]) {
            delete newFilters[key as keyof typeof newFilters];
        }
    });

    router.get('/admin/attendance', newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const handleSearchInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    searchQuery.value = target.value;

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 500); // 500ms debounce
};

const handleStatusChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    selectedStatus.value = target.value;
    updateFilters();
};

const handleDepartmentChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    selectedDepartment.value = target.value;
    updateFilters();
};

const handleOfficeLocationChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    selectedOfficeLocation.value = target.value;
    updateFilters();
};

const handleDateChange = (date: string | undefined) => {
    selectedDate.value = date || '';
    updateFilters();
};

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

// Delete attendance record
const deleteAttendance = (attendance: AttendanceRecord) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data kehadiran ${attendance.user.name} pada tanggal ${formatDate(attendance.date)}?`)) {
        router.delete(`/admin/attendance/${attendance.id}`, {
            preserveScroll: true,
        });
    }
    activeDropdown.value = null;
};

// Toggle dropdown
const toggleDropdown = (recordId: number) => {
    activeDropdown.value = activeDropdown.value === recordId ? null : recordId;
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    activeDropdown.value = null;
};

// Export attendance data
const exportData = () => {
    const queryParams = new URLSearchParams({
        date: selectedDate.value || '',
        status: selectedStatus.value || '',
        department: selectedDepartment.value || '',
        office_location: selectedOfficeLocation.value || '',
        search: searchQuery.value || '',
        export: 'excel'
    });

    window.open(`/admin/attendance/export?${queryParams.toString()}`, '_blank');
};

// Handle click outside to close dropdown
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.relative')) {
        activeDropdown.value = null;
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head title="Kelola Kehadiran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Kelola Kehadiran
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Pantau dan kelola kehadiran seluruh karyawan
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="exportData"
                            class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Export Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Present Today -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Hadir Hari Ini</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.present_today }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500">
                                    {{ Math.round((stats.present_today / stats.total_employees) * 100) }}% dari total
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Late Today -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-500/10">
                                <AlertCircle class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Terlambat</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.late_today }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500">
                                    {{ Math.round((stats.late_today / stats.total_employees) * 100) }}% dari total
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Absent Today -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                                <XCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tidak Hadir</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.absent_today }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500">
                                    {{ Math.round((stats.absent_today / stats.total_employees) * 100) }}% dari total
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Employees -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Karyawan</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_employees }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500">
                                    {{ stats.attendance_rate }}% tingkat kehadiran
                                </p>
                            </div>
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
                            :value="searchQuery"
                            @input="handleSearchInput"
                        />
                    </div>
                    <div class="flex gap-3">
                        <select
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :value="selectedStatus"
                            @change="handleStatusChange"
                        >
                            <option value="">Semua Status</option>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                            <option value="half_day">Setengah Hari</option>
                        </select>
                        <select
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :value="selectedDepartment"
                            @change="handleDepartmentChange"
                        >
                            <option value="">Semua Departemen</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                        <select
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :value="selectedOfficeLocation"
                            @change="handleOfficeLocationChange"
                        >
                            <option value="">Semua Lokasi</option>
                            <option v-for="location in officeLocations" :key="location.id" :value="location.id">{{ location.name }}</option>
                        </select>
                        <div class="relative">
                            <DatePicker
                                :model-value="selectedDate"
                                @update:model-value="handleDateChange"
                                placeholder="Pilih tanggal"
                                class="w-auto"
                            />
                        </div>
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
                                    <div class="relative">
                                        <!-- Quick action buttons for larger screens -->
                                        <div class="hidden sm:flex items-center gap-2">
                                            <Link
                                                :href="`/admin/attendance/${record.id}`"
                                                class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700 transition-colors"
                                                title="Lihat Detail"
                                            >
                                                <Eye class="h-3 w-3" />
                                            </Link>
                                            <Link
                                                :href="`/admin/attendance/${record.id}/edit`"
                                                class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 ring-1 ring-blue-200 hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-800 dark:hover:bg-blue-900/50 transition-colors"
                                                title="Edit"
                                            >
                                                <Edit class="h-3 w-3" />
                                            </Link>
                                            <button
                                                @click="deleteAttendance(record)"
                                                class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-200 hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-800 dark:hover:bg-red-900/50 transition-colors"
                                                title="Hapus"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </button>
                                        </div>

                                        <!-- Dropdown menu for smaller screens -->
                                        <div class="sm:hidden">
                                            <button
                                                @click="toggleDropdown(record.id)"
                                                class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700 transition-colors"
                                            >
                                                <MoreHorizontal class="h-4 w-4" />
                                            </button>

                                            <!-- Dropdown menu -->
                                            <div
                                                v-if="activeDropdown === record.id"
                                                @click.stop
                                                class="absolute right-0 top-8 z-10 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800 dark:ring-gray-700"
                                            >
                                                <Link
                                                    :href="`/admin/attendance/${record.id}`"
                                                    @click="closeDropdown"
                                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                >
                                                    <Eye class="mr-3 h-4 w-4" />
                                                    Lihat Detail
                                                </Link>
                                                <Link
                                                    :href="`/admin/attendance/${record.id}/edit`"
                                                    @click="closeDropdown"
                                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                >
                                                    <Edit class="mr-3 h-4 w-4" />
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="deleteAttendance(record)"
                                                    class="flex w-full items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                >
                                                    <Trash2 class="mr-3 h-4 w-4" />
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>