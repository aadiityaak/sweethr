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
    Briefcase,
    Shield
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
    face_photo_path?: string | null;
    face_photo_url?: string | null;
    face_match_confidence?: number | null;
    face_verification_passed?: boolean;
    face_verification_skipped?: boolean;
    face_verification_notes?: string | null;
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

</script>

<template>
    <Head :title="`Detail Kehadiran - ${attendance.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Detail Kehadiran
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ attendance.user.name }} • {{ formatDate(attendance.date) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ring-1 ring-inset"
                            :class="getStatusBadge(attendance.status)"
                        >
                            <component :is="getStatusIcon(attendance.status)" class="mr-1.5 h-4 w-4" />
                            {{ getStatusText(attendance.status) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Employee Card -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <User class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ attendance.user.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500">{{ attendance.user.employee_id }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-in Time -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <Clock class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Check-in</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatTime(attendance.check_in_time) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-out Time -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-500/10">
                                <Clock class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Check-out</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatTime(attendance.check_out_time) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Work Duration -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <Timer class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Kerja</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatDuration(attendance.work_duration) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-8">
                    <!-- Employee Details -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <User class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                Detail Karyawan
                            </h3>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ attendance.user.email }}</dd>
                                </div>
                                <div v-if="attendance.user.department" class="flex justify-between">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Departemen</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ attendance.user.department.name }}</dd>
                                </div>
                                <div v-if="attendance.user.position" class="flex justify-between">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Posisi</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ attendance.user.position.title }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Lembur</dt>
                                    <dd class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ formatDuration(attendance.overtime_duration) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Face Verification -->
                    <div v-if="attendance.face_match_confidence !== null || attendance.face_photo_url"
                         class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <Shield class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                                Verifikasi Wajah
                            </h3>
                        </div>
                        <div class="p-6">
                            <div v-if="attendance.face_photo_url" class="mb-6">
                                <img
                                    :src="attendance.face_photo_url"
                                    alt="Foto Check-in"
                                    class="h-48 w-full rounded-lg object-cover bg-gray-100 dark:bg-gray-800"
                                    @error="$event.target.style.display = 'none'"
                                />
                            </div>
                            <dl class="space-y-4">
                                <div v-if="attendance.face_match_confidence !== null" class="flex justify-between items-center">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Confidence</dt>
                                    <dd class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ Math.round(attendance.face_match_confidence) }}%
                                        </span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': attendance.face_match_confidence >= 70,
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': attendance.face_match_confidence >= 50 && attendance.face_match_confidence < 70,
                                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': attendance.face_match_confidence < 50
                                            }"
                                        >
                                            <span v-if="attendance.face_match_confidence >= 70">Tinggi</span>
                                            <span v-else-if="attendance.face_match_confidence >= 50">Sedang</span>
                                            <span v-else>Rendah</span>
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between items-center">
                                    <dt class="text-sm text-gray-600 dark:text-gray-400">Status</dt>
                                    <dd>
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': attendance.face_verification_passed,
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': attendance.face_verification_skipped,
                                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': attendance.face_verification_passed === false
                                            }"
                                        >
                                            <CheckCircle v-if="attendance.face_verification_passed" class="mr-1 h-3 w-3" />
                                            <AlertCircle v-else-if="attendance.face_verification_skipped" class="mr-1 h-3 w-3" />
                                            <XCircle v-else class="mr-1 h-3 w-3" />
                                            <span v-if="attendance.face_verification_passed">Berhasil</span>
                                            <span v-else-if="attendance.face_verification_skipped">Dilewati</span>
                                            <span v-else>Gagal</span>
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Office Location -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <Building class="mr-2 h-5 w-5 text-orange-600 dark:text-orange-400" />
                                {{ attendance.office_location.name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ attendance.office_location.address }}</p>
                        </div>
                        <div class="p-6">
                            <div class="aspect-[4/3] rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <iframe
                                    :src="`https://maps.google.com/maps?q=${attendance.office_location.latitude},${attendance.office_location.longitude}&z=16&output=embed`"
                                    class="h-full w-full"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in/out Locations -->
                    <div class="space-y-6">
                        <!-- Check-in Location -->
                        <div v-if="attendance.check_in_latitude && attendance.check_in_longitude"
                             class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                    <MapPin class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                                    Lokasi Check-in
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-500 font-mono">
                                    {{ attendance.check_in_latitude }}, {{ attendance.check_in_longitude }}
                                </p>
                            </div>
                            <div class="p-6">
                                <div ref="checkInMapContainer" class="aspect-[4/3] rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    <iframe
                                        :src="`https://maps.google.com/maps?q=${attendance.check_in_latitude},${attendance.check_in_longitude}&z=16&output=embed`"
                                        class="h-full w-full"
                                        style="border:0;"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                            </div>
                        </div>

                        <!-- Check-out Location -->
                        <div v-if="attendance.check_out_latitude && attendance.check_out_longitude"
                             class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                    <MapPin class="mr-2 h-5 w-5 text-red-600 dark:text-red-400" />
                                    Lokasi Check-out
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-500 font-mono">
                                    {{ attendance.check_out_latitude }}, {{ attendance.check_out_longitude }}
                                </p>
                            </div>
                            <div class="p-6">
                                <div ref="checkOutMapContainer" class="aspect-[4/3] rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    <iframe
                                        :src="`https://maps.google.com/maps?q=${attendance.check_out_latitude},${attendance.check_out_longitude}&z=16&output=embed`"
                                        class="h-full w-full"
                                        style="border:0;"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Information -->
            <div v-if="attendance.notes" class="mt-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <Briefcase class="mr-2 h-5 w-5 text-gray-600 dark:text-gray-400" />
                            Catatan
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ attendance.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            <div class="mt-8 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center">
                        <Calendar class="mr-1 h-4 w-4" />
                        Dibuat: {{ formatDateTime(attendance.created_at) }}
                    </span>
                    <span class="flex items-center">
                        <Clock class="mr-1 h-4 w-4" />
                        Diperbarui: {{ formatDateTime(attendance.updated_at) }}
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>