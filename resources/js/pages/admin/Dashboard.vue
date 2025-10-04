<script setup lang="ts">
import AttendanceChart from '@/components/AttendanceChart.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
// Admin dashboard uses direct URL
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    AlertTriangle,
    ArrowDown,
    ArrowUp,
    Calendar,
    CheckCircle,
    Clock,
    TrendingDown,
    TrendingUp,
    Users,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
    late_today?: Array<{
        id: number;
        name: string;
        employee_id: string;
        department: string;
        check_in_time: string;
        late_duration: number;
    }>;
    late_today_count?: number;
    on_leave_today?: Array<{
        user_name: string;
        employee_id: string;
        leave_type: string;
    }>;
    on_leave_today_count?: number;
    trend_comparison?: {
        attendance_rate: {
            current: number;
            last: number;
            change: number;
        };
        late_count: {
            current: number;
            last: number;
            change: number;
        };
    };
    alerts?: Array<{
        type: string;
        severity: string;
        user_name?: string;
        employee_id?: string;
        count?: number;
        message: string;
    }>;
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

// State for rejection modal
const showRejectionModal = ref(false);
const rejectionReason = ref('');
const currentLeaveId = ref<number | null>(null);

const formatTime = (time: string | null | undefined) => {
    if (!time) return '--:--';
    return time.substring(0, 5);
};

const formatDuration = (minutes: number | null | undefined) => {
    if (!minutes) return '--';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatLateDuration = (minutes: number | null | undefined) => {
    if (!minutes) return '0 menit';
    if (minutes < 60) return `${minutes} menit`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `${hours}j ${mins}m` : `${hours} jam`;
};

const getTrendIcon = (change: number) => {
    return change > 0 ? TrendingUp : change < 0 ? TrendingDown : null;
};

const getTrendColor = (change: number, inverse = false) => {
    if (change === 0) return 'text-gray-500';
    if (inverse) {
        return change > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400';
    }
    return change > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
};

const getSeverityColor = (severity: string) => {
    switch (severity) {
        case 'high':
            return 'bg-red-50 border-red-200 dark:bg-red-950/30 dark:border-red-800/50';
        case 'medium':
            return 'bg-amber-50 border-amber-200 dark:bg-amber-950/30 dark:border-amber-800/50';
        default:
            return 'bg-blue-50 border-blue-200 dark:bg-blue-950/30 dark:border-blue-800/50';
    }
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

// Leave request approval functions
const approveLeave = (leaveId: number) => {
    const form = useForm({});
    form.patch(`/admin/leave-requests/${leaveId}/approve`, {
        preserveState: false,
        preserveScroll: true,
    });
};

const rejectLeave = (leaveId: number) => {
    currentLeaveId.value = leaveId;
    rejectionReason.value = '';
    showRejectionModal.value = true;
};

const confirmRejection = () => {
    if (!currentLeaveId.value || !rejectionReason.value.trim()) return;

    const form = useForm({
        admin_notes: rejectionReason.value,
    });
    form.patch(`/admin/leave-requests/${currentLeaveId.value}/reject`, {
        preserveState: false,
        preserveScroll: true,
        onSuccess: () => {
            showRejectionModal.value = false;
            currentLeaveId.value = null;
            rejectionReason.value = '';
        },
    });
};

const cancelRejection = () => {
    showRejectionModal.value = false;
    currentLeaveId.value = null;
    rejectionReason.value = '';
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
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10">
                        <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Top Stats Cards (4 horizontal cards) -->
            <div v-if="user.is_admin" class="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Employees Card -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                            <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="flex items-center gap-1" :class="getTrendColor(5)">
                            <TrendingUp class="h-4 w-4" />
                            <span class="text-sm font-medium">5%</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Karyawan</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_employees || 0 }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Karyawan aktif</p>
                    </div>
                </div>

                <!-- Today Present Card -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                            <CheckCircle class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div
                            class="flex items-center gap-1"
                            :class="
                                getTrendColor(
                                    stats.trend_comparison?.attendance_rate.change || 0,
                                )
                            "
                        >
                            <component :is="getTrendIcon(stats.trend_comparison?.attendance_rate.change || 0)" class="h-4 w-4" />
                            <span class="text-sm font-medium">{{ Math.abs(stats.trend_comparison?.attendance_rate.change || 0).toFixed(1) }}%</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Hadir Hari Ini</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.today_present || 0 }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">{{ Math.round(((stats.today_present || 0) / (stats.total_employees || 1)) * 100) }}% tingkat kehadiran</p>
                    </div>
                </div>

                <!-- Late Today Card -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10">
                            <Clock class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div class="flex items-center gap-1" :class="getTrendColor(stats.trend_comparison?.late_count.change || 0, true)">
                            <component :is="getTrendIcon(stats.trend_comparison?.late_count.change || 0)" class="h-4 w-4" />
                            <span class="text-sm font-medium">{{ Math.abs(stats.trend_comparison?.late_count.change || 0) }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Terlambat Hari Ini</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.late_today_count || 0 }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Karyawan terlambat</p>
                    </div>
                </div>

                <!-- Pending Leave Card -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10">
                            <AlertCircle class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">Pending</div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pengajuan Cuti</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.pending_leave_requests || 0 }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Menunggu persetujuan</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid (2 columns) -->
            <div class="mb-6 grid gap-6 lg:grid-cols-2">
                <!-- Department Stats -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Departemen</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Kehadiran per departemen hari ini</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div v-for="dept in stats.department_stats?.slice(0, 5)" :key="dept.department" class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ dept.department }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ dept.attendance_rate.toFixed(0) }}%</p>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all"
                                        :style="{ width: `${dept.attendance_rate}%` }"
                                    ></div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">{{ dept.present_today }} dari {{ dept.total_employees }} karyawan</p>
                            </div>
                        </div>
                        <div v-if="!stats.department_stats || stats.department_stats.length === 0" class="py-8 text-center">
                            <Users class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada data departemen</p>
                        </div>
                    </div>
                </div>

                <!-- 30-Day Trend Chart -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Kehadiran 30 Hari</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tingkat kehadiran harian</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <AttendanceChart
                            type="line"
                            :monthly-data="{
                                labels: generateLast30Days(),
                                data: generateMockAttendanceData(),
                                presentData: [],
                                absentData: [],
                            }"
                        />
                    </div>
                </div>
            </div>

            <!-- Alerts Banner (if any) -->
            <div
                v-if="user.is_admin && stats.alerts && stats.alerts.length > 0"
                class="mb-6 rounded-2xl border border-amber-200/50 bg-gradient-to-br from-amber-50 to-orange-50/50 p-4 dark:border-amber-800/50 dark:from-amber-950/20 dark:to-orange-950/20"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10">
                        <AlertTriangle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ stats.alerts.length }} Peringatan Aktif</h3>
                        <div class="mt-2 space-y-1">
                            <p v-for="(alert, index) in stats.alerts.slice(0, 3)" :key="index" class="text-sm text-gray-700 dark:text-gray-300">• {{ alert.message }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="mb-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Late Today List -->
                <div class="rounded-2xl bg-gradient-to-br from-red-500 to-pink-600 p-6 text-white shadow-lg">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <Clock class="h-5 w-5" />
                        </div>
                        <span class="text-2xl font-bold">{{ stats.late_today_count || 0 }}</span>
                    </div>
                    <p class="text-sm font-medium opacity-90">Terlambat Hari Ini</p>
                    <p class="mt-1 text-xs opacity-75">Karyawan yang datang terlambat</p>
                </div>

                <!-- On Leave Today -->
                <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 p-6 text-white shadow-lg">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <Calendar class="h-5 w-5" />
                        </div>
                        <span class="text-2xl font-bold">{{ stats.on_leave_today_count || 0 }}</span>
                    </div>
                    <p class="text-sm font-medium opacity-90">Cuti Hari Ini</p>
                    <p class="mt-1 text-xs opacity-75">Karyawan yang sedang cuti</p>
                </div>

                <!-- Attendance Rate -->
                <div class="rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 p-6 text-white shadow-lg">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <CheckCircle class="h-5 w-5" />
                        </div>
                        <span class="text-2xl font-bold">{{ (stats.monthly_attendance_rate || 0).toFixed(0) }}%</span>
                    </div>
                    <p class="text-sm font-medium opacity-90">Tingkat Kehadiran</p>
                    <p class="mt-1 text-xs opacity-75">Rata-rata bulan ini</p>
                </div>

                <!-- Overtime Hours -->
                <div class="rounded-2xl bg-gradient-to-br from-orange-500 to-red-600 p-6 text-white shadow-lg">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <Clock class="h-5 w-5" />
                        </div>
                        <span class="text-2xl font-bold">{{ (stats.monthly_overtime_hours || 0).toFixed(0) }}</span>
                    </div>
                    <p class="text-sm font-medium opacity-90">Jam Lembur</p>
                    <p class="mt-1 text-xs opacity-75">Total bulan ini</p>
                </div>
            </div>

            <!-- Bottom Section: Pending Leaves & Recent Announcements -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Pending Leave Requests -->
                <div v-if="pendingLeaves.length > 0" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pengajuan Cuti</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ pendingLeaves.length }} menunggu persetujuan</p>
                        </div>
                        <Link href="/admin/leave-requests" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat Semua →</Link>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="leave in pendingLeaves.slice(0, 3)"
                            :key="leave.id"
                            class="flex items-center justify-between rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ leave.user?.name?.charAt(0) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ leave.user?.name }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ leave.leave_type?.name }} - {{ leave.total_days }} hari</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="approveLeave(leave.id)"
                                    class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-400"
                                >
                                    Setujui
                                </button>
                                <button
                                    @click="rejectLeave(leave.id)"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400"
                                >
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Announcements -->
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pengumuman Terbaru</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Informasi penting dari perusahaan</p>
                        </div>
                        <Link href="/admin/announcements" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">Lihat Semua →</Link>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="announcement in announcements.slice(0, 3)"
                            :key="announcement.id"
                            class="rounded-xl border border-gray-200 p-4 dark:border-gray-700"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                    <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ announcement.title }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs text-gray-600 dark:text-gray-400">{{ announcement.message }}</p>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">{{ formatDate(announcement.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="announcements.length === 0" class="py-8 text-center">
                            <Calendar class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada pengumuman</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Rejection Confirmation Modal -->
    <ConfirmationModal
        :show="showRejectionModal"
        title="Tolak Pengajuan Cuti"
        type="danger"
        confirm-text="Tolak"
        cancel-text="Batal"
        @confirm="confirmRejection"
        @cancel="cancelRejection"
    >
        <template #message>
            <div class="space-y-4">
                <p>Apakah Anda yakin ingin menolak pengajuan cuti ini?</p>
                <div>
                    <label for="rejection-reason" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Alasan Penolakan </label>
                    <textarea
                        id="rejection-reason"
                        v-model="rejectionReason"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="Mohon berikan alasan penolakan..."
                    ></textarea>
                </div>
            </div>
        </template>
    </ConfirmationModal>
</template>
