<script setup lang="ts">
import AttendanceChart from '@/components/AttendanceChart.vue';
import { Button } from '@/components/ui/button';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    BarChart3,
    Calendar,
    CheckCircle,
    Clock,
    Download,
    Edit,
    Eye,
    MapPin,
    MoreHorizontal,
    Search,
    Trash2,
    Users,
    XCircle,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

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
    shift_info?: {
        start_time: string;
        end_time: string;
        name: string;
    };
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
        date_from?: string;
        date_to?: string;
        sort?: string;
        direction?: string;
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
    { title: 'Kelola Kehadiran', href: '/admin/attendance' },
];

// Reactive filter state
const searchQuery = ref(filters.search || '');
const selectedStatus = ref(filters.status || '');
const selectedDepartment = ref(filters.department || '');
const selectedOfficeLocation = ref(filters.office_location || '');
const selectedDate = ref(filters.date || '');
const selectedDateFrom = ref(filters.date_from || '');
const selectedDateTo = ref(filters.date_to || '');

// Action dropdown state
const activeDropdown = ref<number | null>(null);

// Delete confirmation modal state
const showDeleteModal = ref(false);
const attendanceToDelete = ref<AttendanceRecord | null>(null);

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
        date_from: selectedDateFrom.value || undefined,
        date_to: selectedDateTo.value || undefined,
        sort: sortField.value || undefined,
        direction: sortDirection.value || undefined,
    };

    // Remove empty values
    Object.keys(newFilters).forEach((key) => {
        if (!newFilters[key as keyof typeof newFilters]) {
            delete newFilters[key as keyof typeof newFilters];
        }
    });

    router.get('/admin/attendance', newFilters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
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

// Handle date range changes
const handleDateFromChange = (date: string | undefined) => {
    selectedDateFrom.value = date || '';
    updateFilters();
};

const handleDateToChange = (date: string | undefined) => {
    selectedDateTo.value = date || '';
    updateFilters();
};

// Quick date selection
const selectQuickDate = (period: string) => {
    const today = new Date();
    let fromDate: Date;
    let toDate: Date;

    switch (period) {
        case 'today':
            fromDate = today;
            toDate = today;
            break;
        case 'yesterday':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 1);
            toDate = fromDate;
            break;
        case 'thisWeek':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
            toDate = new Date(today);
            toDate.setDate(today.getDate() + (6 - today.getDay())); // End of week (Saturday)
            break;
        case 'thisMonth':
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1); // First day of month
            toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // Last day of month
            break;
        case 'last3Months':
            fromDate = new Date(today.getFullYear(), today.getMonth() - 3, 1); // 3 months ago, first day
            toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); // Last day of current month
            break;
        default:
            return;
    }

    // Format dates as YYYY-MM-DD
    const formatDate = (date: Date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    selectedDateFrom.value = formatDate(fromDate);
    selectedDateTo.value = formatDate(toDate);
    updateFilters();
};

const formatTime = (time: string | null | any) => {
    if (!time) return '--:--';

    try {
        // Handle time in HH:mm:ss format from database
        if (typeof time === 'string' && time.length === 8) {
            const [hours, minutes] = time.split(':');
            return `${hours}:${minutes}`;
        }

        // Handle time in HH:mm format
        if (typeof time === 'string' && time.length === 5) {
            return time;
        }

        // Handle time as DateTime object (converted to string by JSON serialization)
        if (typeof time === 'string' && time.includes('T')) {
            // Extract time part from ISO string
            const timePart = time.split('T')[1]?.split('.')[0] || time;
            const [hours, minutes] = timePart.split(':');
            return `${hours}:${minutes}`;
        }

        // Handle time as object (from Laravel datetime cast)
        if (typeof time === 'object' && time !== null) {
            // Try to extract time from various possible object structures
            let timeStr: string;
            if ('time' in time) {
                timeStr = (time as any).time;
            } else if ('datetime' in time) {
                timeStr = (time as any).datetime;
            } else if ('date' in time) {
                timeStr = (time as any).date;
            } else {
                timeStr = time.toString();
            }

            if (timeStr.includes('T')) {
                const timePart = timeStr.split('T')[1]?.split('.')[0] || timeStr;
                const [hours, minutes] = timePart.split(':');
                return `${hours}:${minutes}`;
            }
        }

        // Fallback: try to parse as time string
        const dateObj = new Date(`2000-01-01T${time}`);
        if (!isNaN(dateObj.getTime())) {
            return dateObj.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
            });
        }

        return '--:--';
    } catch (error) {
        console.error('Error formatting time:', error, 'Input:', time);
        return '--:--';
    }
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return '--';

    // Round to 1 decimal place to avoid long decimals
    const roundedMinutes = Math.round(minutes * 10) / 10;

    const hours = Math.floor(roundedMinutes / 60);
    const mins = roundedMinutes % 60;

    // Format minutes with 1 decimal place if it has decimals
    const minsFormatted = mins % 1 !== 0 ? mins.toFixed(1) : mins.toString();

    return hours > 0 ? `${hours}j ${minsFormatted}m` : `${minsFormatted}m`;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getStatusBadge = (status: string) => {
    const badges = {
        present: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/50 dark:text-emerald-400',
        late: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/50 dark:text-amber-400',
        absent: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-950/50 dark:text-red-400',
        half_day: 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-950/50 dark:text-blue-400',
    };
    return badges[status as keyof typeof badges] || badges.absent;
};

const getStatusText = (status: string) => {
    const statusMap = {
        present: 'Hadir',
        late: 'Terlambat',
        absent: 'Tidak Hadir',
        half_day: 'Setengah Hari',
    };
    return statusMap[status as keyof typeof statusMap] || 'Tidak Diketahui';
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'present':
            return CheckCircle;
        case 'late':
            return AlertCircle;
        case 'absent':
            return XCircle;
        case 'half_day':
            return Clock;
        default:
            return XCircle;
    }
};

// Generate mock weekly data for chart
const generateWeeklyData = () => {
    return {
        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        data: [92, 88, 95, 89, 91, 0, 0], // Weekend = 0
    };
};

// Generate mock attendance breakdown
const generateAttendanceBreakdown = () => {
    return {
        present: stats.present_today,
        absent: stats.absent_today,
        late: stats.late_today,
    };
};

// Generate mock 30-day trend data
const generateTrendData = () => {
    const today = new Date();
    const labels = [];
    const data = [];
    const presentData = [];
    const lateData = [];

    for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        const dayOfWeek = date.getDay();

        // Skip weekends
        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
            labels.push(date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
            const total = Math.floor(Math.random() * 20) + 80; // 80-100%
            const present = Math.floor(total * 0.9);
            const late = Math.floor(total * 0.1);

            data.push(total);
            presentData.push(present);
            lateData.push(late);
        }
    }

    return { labels, data, presentData, lateData };
};

// Generate mock department data
const generateDepartmentData = () => {
    return {
        labels: ['IT', 'HR', 'Finance', 'Marketing', 'Operations'],
        data: [45, 38, 42, 40, 48],
    };
};

// Delete attendance record
const deleteAttendance = (attendance: AttendanceRecord) => {
    attendanceToDelete.value = attendance;
    showDeleteModal.value = true;
    activeDropdown.value = null;
};

// Confirm delete attendance
const confirmDelete = () => {
    if (attendanceToDelete.value) {
        router.delete(`/admin/attendance/${attendanceToDelete.value.id}`, {
            preserveScroll: true,
        });
        attendanceToDelete.value = null;
        showDeleteModal.value = false;
    }
};

// Cancel delete
const cancelDelete = () => {
    attendanceToDelete.value = null;
    showDeleteModal.value = false;
};

// Toggle dropdown
const toggleDropdown = (recordId: number) => {
    activeDropdown.value = activeDropdown.value === recordId ? null : recordId;
};

// Charts visibility state
const showCharts = ref(false);

// Sorting functionality - default to ID descending
const sortField = ref(filters.sort || 'id');
const sortDirection = ref(filters.direction || 'desc');

// Close dropdown when clicking outside
const closeDropdown = () => {
    activeDropdown.value = null;
};

// Export attendance data
const exportData = () => {
    const queryParams = new URLSearchParams({
        date: selectedDate.value || '',
        date_from: selectedDateFrom.value || '',
        date_to: selectedDateTo.value || '',
        status: selectedStatus.value || '',
        department: selectedDepartment.value || '',
        office_location: selectedOfficeLocation.value || '',
        search: searchQuery.value || '',
        export: 'excel',
    });

    window.open(`/admin/attendance/export?${queryParams.toString()}`, '_blank');
};

// Sorting function
const sortBy = (field: string) => {
    if (sortField.value === field) {
        // Toggle direction if same field
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // Set new field and default to ascending
        sortField.value = field;
        sortDirection.value = 'asc';
    }

    updateFilters();
};

// Get sort icon for column header
const getSortIcon = (field: string) => {
    if (sortField.value !== field) {
        return ArrowUpDown;
    }
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
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
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kelola Kehadiran</h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Pantau dan kelola kehadiran seluruh karyawan</p>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Present Today -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Hadir Hari Ini</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ stats.present_today }} dari {{ stats.total_employees }} karyawan
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Hadir</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ stats.present_today }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tingkat Kehadiran</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ Math.round((stats.present_today / stats.total_employees) * 100) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Late Today -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                                <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Terlambat</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ stats.late_today }} dari {{ stats.total_employees }} karyawan
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-amber-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-amber-50/50 px-3 py-2 dark:bg-amber-950/30">
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Terlambat</span>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ stats.late_today }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ Math.round((stats.late_today / stats.total_employees) * 100) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Absent Today -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-red-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-red-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/20">
                                <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Tidak Hadir</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ stats.absent_today }} dari {{ stats.total_employees }} karyawan
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-red-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-red-50/50 px-3 py-2 dark:bg-red-950/30">
                            <span class="text-sm font-medium text-red-700 dark:text-red-400">Tidak Hadir</span>
                            <span class="text-sm font-semibold text-red-800 dark:text-red-300">{{ stats.absent_today }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ Math.round((stats.absent_today / stats.total_employees) * 100) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-red-500/5 to-rose-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Total Employees -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Karyawan</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Semua karyawan terdaftar</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ stats.total_employees }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tingkat Kehadiran</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white"> {{ stats.attendance_rate }}% </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Charts Section (Toggleable) -->
            <div v-if="showCharts" class="mb-8">
                <!-- Charts Row -->
                <div class="mb-8 grid gap-6 lg:grid-cols-2">
                    <!-- 30-Day Trend Chart -->
                    <div
                        class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                                <Calendar class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Kehadiran 30 Hari Terakhir</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Statistik kehadiran harian</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <AttendanceChart type="line" :trend-data="generateTrendData()" />
                        </div>
                        <div
                            class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                    </div>

                    <!-- Status Distribution -->
                    <div
                        class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                                <CheckCircle class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Status</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Perbandingan status kehadiran</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <AttendanceChart type="doughnut" :attendance-data="generateAttendanceBreakdown()" />
                        </div>
                        <div
                            class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                    </div>
                </div>

                <!-- Additional Charts Row -->
                <div class="mb-8 grid gap-6 lg:grid-cols-2">
                    <!-- Weekly Attendance Chart -->
                    <div
                        class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <Calendar class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Kehadiran Mingguan</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Persentase kehadiran per hari</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <AttendanceChart type="bar" :weekly-data="generateWeeklyData()" />
                        </div>
                        <div
                            class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                    </div>

                    <!-- Department-wise Attendance -->
                    <div
                        class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 ring-1 ring-amber-500/20">
                                <Users class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Kehadiran per Departemen</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Distribusi kehadiran per departemen</p>
                            </div>
                        </div>
                        <div class="h-64">
                            <AttendanceChart type="bar" :department-data="generateDepartmentData()" />
                        </div>
                        <div
                            class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div
                class="group relative rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30"
            >
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                        <Search class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pencarian & Filter</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Temukan data kehadiran yang Anda butuhkan</p>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="mb-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="relative max-w-md flex-1">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Cari karyawan..."
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-4 pl-10 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-400"
                                :value="searchQuery"
                                @input="handleSearchInput"
                            />
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <select
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :value="selectedDepartment"
                                @change="handleDepartmentChange"
                            >
                                <option value="">Semua Departemen</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                            <select
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :value="selectedOfficeLocation"
                                @change="handleOfficeLocationChange"
                            >
                                <option value="">Semua Lokasi</option>
                                <option v-for="location in officeLocations" :key="location.id" :value="location.id">{{ location.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Date Selection Section (Side by Side) -->
                <div class="mb-8 grid gap-6 lg:grid-cols-2">
                    <!-- Quick Date Selection -->
                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                                <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Pilih Periode Cepat</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Filter kehadiran berdasarkan periode waktu</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="selectQuickDate('today')"
                                class="rounded-md bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-800/50"
                            >
                                📅 Hari Ini
                            </button>
                            <button
                                @click="selectQuickDate('yesterday')"
                                class="rounded-md bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-800/50"
                            >
                                📅 Kemarin
                            </button>
                            <button
                                @click="selectQuickDate('thisWeek')"
                                class="rounded-md bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-800/50"
                            >
                                📈 Minggu Ini
                            </button>
                            <button
                                @click="selectQuickDate('thisMonth')"
                                class="rounded-md bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-800/50"
                            >
                                📅 Bulan Ini
                            </button>
                            <button
                                @click="selectQuickDate('last3Months')"
                                class="rounded-md bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-800/50"
                            >
                                📊 3 Bulan
                            </button>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                                <Calendar class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Pilih Rentang Tanggal</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Filter kehadiran berdasarkan rentang tanggal spesifik</p>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="relative">
                                <DatePicker
                                    :model-value="selectedDateFrom"
                                    @update:model-value="handleDateFromChange"
                                    placeholder="Pilih tanggal mulai"
                                    class="w-full"
                                />
                            </div>
                            <div class="relative">
                                <DatePicker
                                    :model-value="selectedDateTo"
                                    @update:model-value="handleDateToChange"
                                    placeholder="Pilih tanggal akhir"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
            </div>

            <!-- Export and Charts Toggle Section -->
            <div class="mt-6 mb-8 flex justify-end gap-3">
                <!-- Toggle Charts Button -->
                <button
                    @click="showCharts = !showCharts"
                    class="inline-flex items-center rounded-lg bg-purple-600 px-6 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none"
                    :class="{ 'bg-purple-700': showCharts }"
                >
                    <BarChart3 class="mr-2 h-4 w-4" />
                    {{ showCharts ? 'Sembunyikan Grafik' : 'Tampilkan Grafik' }}
                </button>

                <!-- Export Button -->
                <button
                    @click="exportData"
                    class="inline-flex items-center rounded-lg bg-green-600 px-6 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none"
                >
                    <Download class="mr-2 h-4 w-4" />
                    Export Excel
                </button>
            </div>

            <!-- Attendance Records Table -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30"
            >
                <div class="border-b border-gray-200/50 p-8 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Catatan Kehadiran</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ attendanceRecords.length }} catatan kehadiran ditemukan</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200/50 bg-gray-50/50 dark:border-gray-800/50 dark:bg-gray-900/50">
                            <tr>
                                <th
                                    @click="sortBy('id')"
                                    class="cursor-pointer px-6 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        ID
                                        <component :is="getSortIcon('id')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('user.name')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Karyawan
                                        <component :is="getSortIcon('user.name')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('shift_info.name')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Shift
                                        <component :is="getSortIcon('shift_info.name')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('date')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Tanggal
                                        <component :is="getSortIcon('date')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('check_in_time')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Check In
                                        <component :is="getSortIcon('check_in_time')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('check_out_time')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Check Out
                                        <component :is="getSortIcon('check_out_time')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('work_duration')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Durasi
                                        <component :is="getSortIcon('work_duration')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('status')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Status
                                        <component :is="getSortIcon('status')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th
                                    @click="sortBy('office_location.name')"
                                    class="cursor-pointer px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase transition-colors hover:bg-gray-100/50 dark:text-gray-400 dark:hover:bg-gray-800/50"
                                >
                                    <div class="flex items-center gap-1">
                                        Lokasi
                                        <component :is="getSortIcon('office_location.name')" class="h-3 w-3" />
                                    </div>
                                </th>
                                <th class="px-8 py-4 text-left text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/50 dark:divide-gray-800/50">
                            <tr v-for="record in attendanceRecords" :key="record.id" class="group hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                <td class="px-6 py-5 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ record.id }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                        >
                                            {{ record.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ record.user.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ record.user.employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ record.shift_info?.name || 'N/A' }}
                                        </div>
                                        <div v-if="record.shift_info" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatTime(record.shift_info.start_time) }} - {{ formatTime(record.shift_info.end_time) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(record.date) }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="space-y-1">
                                        <!-- Actual check-in time with status -->
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ formatTime(record.check_in_time) }}
                                            </div>
                                            <!-- Status indicator -->
                                            <div
                                                v-if="record.status === 'late'"
                                                class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400"
                                            >
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <span>Terlambat</span>
                                            </div>
                                            <div
                                                v-else-if="record.status === 'present'"
                                                class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400"
                                            >
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <span>Hadir</span>
                                            </div>
                                        </div>
                                        <!-- Late duration if applicable -->
                                        <div v-if="record.late_duration" class="text-xs text-amber-600 dark:text-amber-400">
                                            Terlambat {{ formatDuration(record.late_duration) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatTime(record.check_out_time) }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatDuration(record.work_duration) }}
                                    </div>
                                    <div v-if="record.overtime_duration" class="text-xs text-blue-600 dark:text-blue-400">
                                        Lembur {{ formatDuration(record.overtime_duration) }}
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2">
                                        <component :is="getStatusIcon(record.status)" class="h-3 w-3" />
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
                                            :class="getStatusBadge(record.status)"
                                        >
                                            {{ getStatusText(record.status) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <MapPin class="h-3 w-3" />
                                        {{ record.office_location?.name || 'Remote' }}
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="relative">
                                        <!-- Quick action buttons for larger screens -->
                                        <div class="hidden items-center gap-2 sm:flex">
                                            <Link
                                                :href="`/admin/attendance/${record.id}`"
                                                class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 transition-colors hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700"
                                                title="Lihat Detail"
                                            >
                                                <Eye class="h-3 w-3" />
                                            </Link>
                                            <Link
                                                :href="`/admin/attendance/${record.id}/edit`"
                                                class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 ring-1 ring-blue-200 transition-colors hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-800 dark:hover:bg-blue-900/50"
                                                title="Edit"
                                            >
                                                <Edit class="h-3 w-3" />
                                            </Link>
                                            <button
                                                @click="deleteAttendance(record)"
                                                class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-200 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-800 dark:hover:bg-red-900/50"
                                                title="Hapus"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </button>
                                        </div>

                                        <!-- Dropdown menu for smaller screens -->
                                        <div class="sm:hidden">
                                            <button
                                                @click="toggleDropdown(record.id)"
                                                class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 transition-colors hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-700"
                                            >
                                                <MoreHorizontal class="h-4 w-4" />
                                            </button>

                                            <!-- Dropdown menu -->
                                            <div
                                                v-if="activeDropdown === record.id"
                                                @click.stop
                                                class="absolute top-8 right-0 z-10 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 dark:bg-gray-800 dark:ring-gray-700"
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
                                            <div
                                                class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Dialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Konfirmasi Hapus</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus data kehadiran
                        <span v-if="attendanceToDelete && attendanceToDelete.user" class="font-semibold">
                            {{ attendanceToDelete.user.name }}
                        </span>
                        pada tanggal
                        <span v-if="attendanceToDelete" class="font-semibold"> {{ formatDate(attendanceToDelete.date) }} </span>? <br /><br />
                        <span class="text-red-600 dark:text-red-400"> Tindakan ini tidak dapat dibatalkan. </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button
                        @click="cancelDelete"
                        variant="outline"
                        class="border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Batal
                    </Button>
                    <Button @click="confirmDelete" variant="destructive" class="bg-red-600 hover:bg-red-700">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
