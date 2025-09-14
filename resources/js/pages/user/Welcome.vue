<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Clock, Calendar, CheckCircle, User, MapPin, LogOut, UserCircle, BarChart3 } from 'lucide-vue-next';
import { useCompanySettings } from '@/composables/useCompanySettings';
import BottomNavigation from '@/components/BottomNavigation.vue';

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

const currentTime = ref('');
const currentDate = ref('');
let timeInterval: number | null = null;

const updateTime = () => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    currentDate.value = new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

onMounted(() => {
    updateTime();
    timeInterval = window.setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (timeInterval) {
        clearInterval(timeInterval);
    }
});
</script>

<template>
    <Head title="SweetHR - Employee Portal" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Header - Full Width -->
        <div class="bg-background/95 backdrop-blur-sm border-b sticky top-0 z-40">
            <div class="mx-auto max-w-[480px] px-4 py-4">
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

        <!-- Mobile Container for Content -->
        <div class="mx-auto max-w-[480px] bg-background min-h-screen relative">

            <!-- Main Content -->
            <div class="px-4 py-6 pb-20">
                <!-- Welcome Section -->
                <div class="mb-6 rounded-xl border bg-gradient-to-br from-card via-card to-card/80 p-6 shadow-sm">
                    <div class="text-center space-y-4">
                        <!-- Avatar Section -->
                        <div class="relative mx-auto">
                            <div class="mx-auto w-16 h-16 rounded-full bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center ring-4 ring-primary/5">
                                <UserCircle class="h-8 w-8 text-primary" />
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-background flex items-center justify-center">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Greeting -->
                        <div class="space-y-2">
                            <h2 class="text-2xl font-bold bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text">
                                Halo, {{ user?.name?.split(' ')[0] || 'Guest' }}! 👋
                            </h2>
                            <div class="flex items-center justify-center gap-2 text-muted-foreground">
                                <div class="w-1.5 h-1.5 rounded-full bg-primary"></div>
                                <p class="text-sm font-medium">
                                    {{ user?.department?.name || 'No Department' }}
                                </p>
                                <div class="w-1 h-1 rounded-full bg-muted-foreground/50"></div>
                                <p class="text-sm">
                                    {{ user?.position?.title || 'No Position' }}
                                </p>
                            </div>
                        </div>

                        <!-- Date & Time Display -->
                        <div class="bg-gradient-to-r from-muted/50 to-muted/30 rounded-lg p-4 border border-border/50">
                            <div class="flex items-center justify-center gap-3 mb-2">
                                <Calendar class="h-4 w-4 text-primary" />
                                <p class="text-sm font-semibold text-foreground">
                                    {{ currentDate }}
                                </p>
                            </div>
                            <div class="flex items-center justify-center gap-2">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                <p class="text-lg font-mono font-bold text-primary tracking-wider">
                                    {{ currentTime }}
                                </p>
                            </div>
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
        </div>

        <BottomNavigation current-route="/home" />
    </div>
</template>