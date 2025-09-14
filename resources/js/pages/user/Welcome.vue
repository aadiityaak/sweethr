<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Calendar, CheckCircle, User, MapPin, LogOut, Home, UserCircle, Settings, BarChart3 } from 'lucide-vue-next';
import { useCompanySettings } from '@/composables/useCompanySettings';

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

const { companyName, companyLogo } = useCompanySettings();

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

    <div class="min-h-screen bg-background">
        <!-- Mobile Container -->
        <div class="mx-auto max-w-[480px] bg-background min-h-screen">
            <!-- Mobile Header -->
            <div class="bg-background/95 backdrop-blur-sm border-b sticky top-0 z-40">
                <div class="px-4 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary p-2 overflow-hidden">
                                <img
                                    v-if="companyLogo"
                                    :src="companyLogo"
                                    :alt="companyName"
                                    class="h-4 w-4 object-contain"
                                />
                                <User v-else class="h-4 w-4 text-primary-foreground" />
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold">{{ companyName }}</h1>
                                <p class="text-sm text-muted-foreground">{{ user?.name || 'Guest' }}</p>
                            </div>
                        </div>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="rounded-md bg-secondary p-2 text-secondary-foreground hover:bg-secondary/80 transition-colors"
                        >
                            <LogOut class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="px-4 py-6 pb-24">
                <!-- Welcome Section -->
                <div class="mb-6 rounded-lg border bg-card p-6">
                    <div class="text-center">
                        <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-muted flex items-center justify-center">
                            <UserCircle class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <h2 class="text-xl font-semibold">
                            Halo, {{ user?.name?.split(' ')[0] || 'Guest' }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ user?.department?.name || 'No Department' }} • {{ user?.position?.title || 'No Position' }}
                        </p>
                        <div class="mt-4 rounded-md bg-muted p-3">
                            <p class="text-sm font-medium">
                                {{ getCurrentDate() }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ getCurrentTime() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Today's Attendance -->
                <div class="mb-6 rounded-lg border bg-card p-6">
                    <h3 class="mb-4 text-lg font-semibold flex items-center gap-2">
                        <Clock class="h-4 w-4" />
                        Absensi Hari Ini
                    </h3>

                    <div class="grid gap-3 grid-cols-2">
                        <div class="rounded-md border bg-card p-4">
                            <div class="text-center">
                                <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                    <Clock class="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                                <p class="text-xs font-medium text-muted-foreground">MASUK</p>
                                <p class="text-lg font-bold">
                                    {{ formatTime(todayAttendance?.check_in_time) }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-md border bg-card p-4">
                            <div class="text-center">
                                <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                                    <Clock class="h-5 w-5 text-red-600 dark:text-red-400" />
                                </div>
                                <p class="text-xs font-medium text-muted-foreground">KELUAR</p>
                                <p class="text-lg font-bold">
                                    {{ formatTime(todayAttendance?.check_out_time) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 rounded-md border bg-card p-4">
                        <div class="text-center">
                            <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                                <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <p class="text-xs font-medium text-muted-foreground">DURASI KERJA</p>
                            <p class="text-lg font-bold">
                                {{ formatDuration(todayAttendance?.work_duration) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="todayAttendance?.office_location" class="mt-4 flex items-center gap-2 rounded-md bg-muted p-3">
                        <MapPin class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">
                            {{ todayAttendance.office_location.name }}
                        </span>
                    </div>

                    <!-- Attendance Actions -->
                    <div class="mt-6 grid gap-3 grid-cols-2">
                        <!-- Check In Button -->
                        <Link
                            v-if="!todayAttendance?.check_in_time"
                            href="/attendance/check-in"
                            class="flex items-center justify-center gap-2 rounded-md bg-green-600 px-4 py-4 text-white font-medium hover:bg-green-700 transition-colors shadow-sm"
                        >
                            <Clock class="h-5 w-5" />
                            <span class="text-sm">Check In</span>
                        </Link>
                        <div
                            v-else
                            class="flex items-center justify-center gap-2 rounded-md bg-green-100 dark:bg-green-900/20 px-4 py-4 text-green-800 dark:text-green-200 font-medium"
                        >
                            <CheckCircle class="h-5 w-5" />
                            <span class="text-sm">Sudah Masuk</span>
                        </div>

                        <!-- Check Out Button -->
                        <Link
                            v-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time"
                            href="/attendance"
                            class="flex items-center justify-center gap-2 rounded-md bg-red-600 px-4 py-4 text-white font-medium hover:bg-red-700 transition-colors shadow-sm"
                        >
                            <Clock class="h-5 w-5" />
                            <span class="text-sm">Check Out</span>
                        </Link>
                        <div
                            v-else-if="todayAttendance?.check_out_time"
                            class="flex items-center justify-center gap-2 rounded-md bg-red-100 dark:bg-red-900/20 px-4 py-4 text-red-800 dark:text-red-200 font-medium"
                        >
                            <CheckCircle class="h-5 w-5" />
                            <span class="text-sm">Sudah Keluar</span>
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center gap-2 rounded-md bg-muted px-4 py-4 text-muted-foreground font-medium"
                        >
                            <Clock class="h-5 w-5" />
                            <span class="text-sm">Belum Waktunya</span>
                        </div>
                    </div>

                    <!-- History Button -->
                    <div class="mt-3">
                        <Link
                            href="/attendance"
                            class="flex w-full items-center justify-center gap-2 rounded-md border-2 border-dashed border-muted-foreground/20 bg-background px-4 py-3 font-medium hover:bg-accent hover:text-accent-foreground hover:border-muted-foreground/40 transition-all"
                        >
                            <Calendar class="h-4 w-4" />
                            Lihat Riwayat Absensi
                        </Link>
                    </div>
                </div>

                <!-- Monthly Stats -->
                <div class="rounded-lg border bg-card p-6">
                    <h3 class="mb-4 text-lg font-semibold flex items-center gap-2">
                        <BarChart3 class="h-4 w-4" />
                        Statistik Bulan Ini
                    </h3>

                    <div class="grid gap-4 grid-cols-2">
                        <div class="rounded-md border bg-card p-4 text-center">
                            <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-muted flex items-center justify-center">
                                <Calendar class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <p class="text-2xl font-bold">{{ stats?.monthly_attendance_days || 0 }}</p>
                            <p class="text-xs text-muted-foreground font-medium">Hari Masuk</p>
                        </div>

                        <div class="rounded-md border bg-card p-4 text-center">
                            <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-muted flex items-center justify-center">
                                <Clock class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <p class="text-2xl font-bold">{{ stats?.monthly_late_days || 0 }}</p>
                            <p class="text-xs text-muted-foreground font-medium">Hari Terlambat</p>
                        </div>

                        <div class="rounded-md border bg-card p-4 text-center">
                            <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-muted flex items-center justify-center">
                                <Clock class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <p class="text-2xl font-bold">{{ stats?.monthly_work_hours || 0 }}</p>
                            <p class="text-xs text-muted-foreground font-medium">Jam Kerja</p>
                        </div>

                        <div class="rounded-md border bg-card p-4 text-center">
                            <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-muted flex items-center justify-center">
                                <Calendar class="h-5 w-5 text-muted-foreground" />
                            </div>
                            <p class="text-2xl font-bold">{{ stats?.leave_balance || 0 }}</p>
                            <p class="text-xs text-muted-foreground font-medium">Sisa Cuti</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fixed Bottom Navigation -->
            <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[480px] bg-background border-t z-50">
                <div class="grid grid-cols-4 py-2">
                    <Link href="/home" class="flex flex-col items-center py-3 px-2 text-primary">
                        <div class="rounded-md bg-primary/10 p-2 mb-1">
                            <Home class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Beranda</span>
                    </Link>

                    <Link href="/attendance" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Clock class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Absensi</span>
                    </Link>

                    <Link href="/leave-requests" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Calendar class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Cuti</span>
                    </Link>

                    <Link href="/user/profile" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Settings class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Profil</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>