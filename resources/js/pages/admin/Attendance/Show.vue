<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    MapPin,
    User,
    Building,
    Calendar,
    CheckCircle,
    XCircle,
    AlertCircle,
    ArrowLeft,
    Timer,
    Briefcase
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
    date: string;
    check_in_time: string | null;
    check_out_time: string | null;
    check_in_latitude: number | null;
    check_in_longitude: number | null;
    check_out_latitude: number | null;
    check_out_longitude: number | null;
    work_duration: number | null;
    break_duration: number | null;
    overtime_duration: number | null;
    status: 'present' | 'late' | 'absent' | 'half_day';
    notes?: string;
    user: User;
    office_location: {
        id: number;
        name: string;
        address: string;
        latitude: number;
        longitude: number;
    };
    created_at: string;
    updated_at: string;
}

interface Props {
    attendance: AttendanceRecord;
}

const { attendance } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dasbor', href: '/dashboard' },
    { title: 'Kelola Kehadiran', href: '/admin/attendance' },
    { title: 'Detail Kehadiran', href: `/admin/attendance/${attendance.id}` }
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
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatDateTime = (dateTimeString: string) => {
    return new Date(dateTimeString).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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

const openMaps = (lat: number, lng: number) => {
    const url = `https://www.google.com/maps?q=${lat},${lng}`;
    window.open(url, '_blank');
};
</script>

<template>
    <Head :title="`Detail Kehadiran - ${attendance.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/attendance"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Detail Kehadiran
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ attendance.user.name }} - {{ formatDate(attendance.date) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Employee Info Card -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <User class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Informasi Karyawan
                        </h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-lg font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ attendance.user.name.charAt(0) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ attendance.user.name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ attendance.user.employee_id }}</div>
                            </div>
                        </div>
                        <div class="space-y-2 border-t border-gray-200/50 pt-4 dark:border-gray-800/50">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Email:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ attendance.user.email }}</span>
                            </div>
                            <div v-if="attendance.user.department" class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Departemen:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ attendance.user.department.name }}</span>
                            </div>
                            <div v-if="attendance.user.position" class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Posisi:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ attendance.user.position.title }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Status Card -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                            <component :is="getStatusIcon(attendance.status)" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Status Kehadiran
                        </h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-center">
                            <span
                                class="inline-flex items-center rounded-full px-4 py-2 text-lg font-medium ring-1 ring-inset"
                                :class="getStatusBadge(attendance.status)"
                            >
                                <component :is="getStatusIcon(attendance.status)" class="mr-2 h-5 w-5" />
                                {{ getStatusText(attendance.status) }}
                            </span>
                        </div>
                        <div class="space-y-3 border-t border-gray-200/50 pt-4 dark:border-gray-800/50">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tanggal:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(attendance.date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Check-in:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatTime(attendance.check_in_time) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Check-out:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatTime(attendance.check_out_time) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Work Duration Card -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/10 ring-1 ring-purple-500/20">
                            <Timer class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Durasi Kerja
                        </h2>
                    </div>
                    <div class="space-y-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                                {{ formatDuration(attendance.work_duration) }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Jam Kerja</div>
                        </div>
                        <div class="space-y-3 border-t border-gray-200/50 pt-4 dark:border-gray-800/50">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Istirahat:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDuration(attendance.break_duration) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Lembur:</span>
                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ formatDuration(attendance.overtime_duration) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Office Location -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 ring-1 ring-orange-500/20">
                            <Building class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Lokasi Kantor
                        </h2>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ attendance.office_location.name }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ attendance.office_location.address }}</div>
                        </div>
                        <button
                            @click="openMaps(attendance.office_location.latitude, attendance.office_location.longitude)"
                            class="inline-flex items-center rounded-md bg-orange-50 px-3 py-2 text-sm font-medium text-orange-700 ring-1 ring-orange-200 hover:bg-orange-100 dark:bg-orange-950/50 dark:text-orange-300 dark:ring-orange-800"
                        >
                            <MapPin class="mr-2 h-4 w-4" />
                            Lihat di Maps
                        </button>
                    </div>
                </div>

                <!-- Check-in/out Locations -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500/10 ring-1 ring-green-500/20">
                            <MapPin class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Lokasi Check-in/out
                        </h2>
                    </div>
                    <div class="space-y-4">
                        <div v-if="attendance.check_in_latitude && attendance.check_in_longitude">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Check-in</span>
                                <button
                                    @click="openMaps(attendance.check_in_latitude!, attendance.check_in_longitude!)"
                                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                >
                                    Lihat Maps
                                </button>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ attendance.check_in_latitude }}, {{ attendance.check_in_longitude }}
                            </div>
                        </div>
                        <div v-if="attendance.check_out_latitude && attendance.check_out_longitude" class="border-t border-gray-200/50 pt-4 dark:border-gray-800/50">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Check-out</span>
                                <button
                                    @click="openMaps(attendance.check_out_latitude!, attendance.check_out_longitude!)"
                                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                >
                                    Lihat Maps
                                </button>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ attendance.check_out_latitude }}, {{ attendance.check_out_longitude }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes and Timestamps -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Notes -->
                <div v-if="attendance.notes" class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-500/10 ring-1 ring-gray-500/20">
                            <Briefcase class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Catatan
                        </h2>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ attendance.notes }}</p>
                    </div>
                </div>

                <!-- Timestamps -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/10 ring-1 ring-indigo-500/20">
                            <Calendar class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Riwayat Record
                        </h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Dibuat:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(attendance.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Diperbarui:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDateTime(attendance.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>