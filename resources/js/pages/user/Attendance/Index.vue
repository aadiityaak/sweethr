<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Clock, MapPin, CheckCircle, XCircle, AlertTriangle, ArrowLeft, Calendar } from 'lucide-vue-next';
import BottomNavigation from '@/components/BottomNavigation.vue';
import AttendanceCalendar from '@/components/AttendanceCalendar.vue';
import { ref, computed } from 'vue';

interface OfficeLocation {
    id: number;
    name: string;
}

interface Attendance {
    id: number;
    date: string;
    check_in_time: string | null;
    check_out_time: string | null;
    work_duration: number | null;
    overtime_duration: number;
    status: string;
    office_location: OfficeLocation;
}

interface TodayAttendance {
    id: number;
    check_in_time: string | null;
    check_out_time: string | null;
    status: string;
    work_duration: number | null;
    office_location: OfficeLocation;
}

interface Props {
    attendances?: {
        data: Attendance[];
        links: any[];
        meta: any;
    };
    todayAttendance: TodayAttendance | null;
    filters: {
        month?: string;
        year?: string;
    };
}

const { attendances, todayAttendance, filters } = defineProps<Props>();

// State for calendar
const showCalendarView = ref(true);
const selectedMonth = ref(new Date());

// Transform attendance data for calendar
const calendarAttendanceData = computed(() => {
    if (!attendances?.data) return [];

    const transformedData = attendances.data.map(attendance => ({
        date: attendance.date,
        status: attendance.status === 'present' ? 'present' :
                attendance.status === 'late' ? 'late' :
                attendance.status === 'absent' ? 'absent' : 'absent',
        check_in_time: attendance.check_in_time,
        check_out_time: attendance.check_out_time,
        notes: `Durasi: ${formatDuration(attendance.work_duration)}`
    }));

    console.log('Calendar attendance data:', transformedData);
    console.log('Raw attendances data:', attendances?.data);
    return transformedData;
});

// Handle calendar day click
const handleDayClick = (date: string, record?: any) => {
    console.log('Day clicked:', date, record);
    // You can add navigation to detail view here
};

// Handle month change
const handleMonthChange = (newMonth: Date) => {
    selectedMonth.value = newMonth;
    const month = (newMonth.getMonth() + 1).toString();
    const year = newMonth.getFullYear().toString();
    filterAttendance(month, year);
};

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

const getStatusColor = (status: string) => {
    switch (status) {
        case 'present':
            return 'text-green-600';
        case 'late':
            return 'text-orange-600';
        case 'absent':
            return 'text-destructive';
        default:
            return 'text-muted-foreground';
    }
};

const getStatusBadgeColor = (status: string) => {
    switch (status) {
        case 'present':
            return 'bg-green-50 text-green-700 border-green-200';
        case 'late':
            return 'bg-orange-50 text-orange-700 border-orange-200';
        case 'absent':
            return 'bg-red-50 text-red-700 border-red-200';
        default:
            return 'bg-muted text-muted-foreground border-border';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'present':
            return CheckCircle;
        case 'late':
            return AlertTriangle;
        case 'absent':
            return XCircle;
        default:
            return Clock;
    }
};

const filterAttendance = (month?: string, year?: string) => {
    router.get('/attendance', { month, year }, { preserveState: true });
};
</script>

<template>
    <Head title="Riwayat Absensi" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Container -->
        <div class="mx-auto max-w-[480px] bg-background min-h-screen">
            <!-- Mobile Header -->
            <div class="bg-background/95 backdrop-blur-sm border-b sticky top-0 z-40">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground hover:bg-secondary/80 transition-colors">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">Riwayat Absensi</h1>
                            <p class="text-sm text-muted-foreground">Data kehadiran Anda</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="px-4 py-6 pb-24">

                <!-- Today's Status -->
                <div class="mb-6 rounded-lg border bg-card p-6">
                    <h2 class="mb-4 text-lg font-semibold flex items-center gap-2">
                        <Clock class="h-4 w-4" />
                        Status Hari Ini
                    </h2>
                    <div class="space-y-3">
                        <div class="rounded-md border bg-card p-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-md bg-muted p-2">
                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-muted-foreground">MASUK</p>
                                    <p class="font-semibold">
                                        {{ formatTime(todayAttendance?.check_in_time) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-md border bg-card p-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-md bg-muted p-2">
                                    <Clock class="h-4 w-4 text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-muted-foreground">KELUAR</p>
                                    <p class="font-semibold">
                                        {{ formatTime(todayAttendance?.check_out_time) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-md border bg-card p-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-md bg-muted p-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-muted-foreground">DURASI</p>
                                    <p class="font-semibold">
                                        {{ formatDuration(todayAttendance?.work_duration) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="todayAttendance" class="mt-4 flex items-center gap-2 rounded-md bg-muted p-3">
                        <MapPin class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">
                            {{ todayAttendance.office_location.name }}
                        </span>
                    </div>

                    <!-- Check Out Button -->
                    <div v-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time" class="mt-4">
                        <button
                            @click="$refs.checkOutModal.showModal()"
                            class="flex w-full items-center justify-center gap-2 rounded-md bg-destructive px-4 py-3 text-destructive-foreground font-medium hover:bg-destructive/90 transition-colors"
                        >
                            <Clock class="h-4 w-4" />
                            Check Out Sekarang
                        </button>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="mb-6 rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium">Tampilan Riwayat</h3>
                        <div class="flex rounded-lg border bg-background">
                            <button
                                @click="showCalendarView = true"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-l-md transition-colors',
                                    showCalendarView
                                        ? 'bg-primary text-primary-foreground'
                                        : 'hover:bg-muted'
                                ]"
                            >
                                📅 Kalender
                            </button>
                            <button
                                @click="showCalendarView = false"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-r-md transition-colors border-l',
                                    !showCalendarView
                                        ? 'bg-primary text-primary-foreground'
                                        : 'hover:bg-muted'
                                ]"
                            >
                                📋 Daftar
                            </button>
                        </div>
                    </div>

                    <div v-if="!showCalendarView" class="grid gap-3 grid-cols-2">
                        <select
                            :value="filters.month"
                            @change="filterAttendance($event.target.value, filters.year)"
                            class="rounded-md border bg-background px-3 py-2 text-sm"
                        >
                            <option value="">Semua Bulan</option>
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <select
                            :value="filters.year"
                            @change="filterAttendance(filters.month, $event.target.value)"
                            class="rounded-md border bg-background px-3 py-2 text-sm"
                        >
                            <option value="">Semua Tahun</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>
                </div>

                <!-- Attendance History -->
                <div class="rounded-lg border bg-card">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-semibold flex items-center gap-2">
                            <Calendar class="h-4 w-4" />
                            Riwayat Kehadiran
                        </h2>
                    </div>

                    <!-- Calendar View -->
                    <div v-if="showCalendarView" class="p-4">
                        <AttendanceCalendar
                            :attendance-data="calendarAttendanceData"
                            :selected-month="selectedMonth"
                            @update:selected-month="handleMonthChange"
                            @day-click="handleDayClick"
                        />
                    </div>

                    <!-- List View -->
                    <div v-else>
                        <div class="divide-y">
                            <div
                                v-for="attendance in attendances?.data || []"
                                :key="attendance.id"
                                class="p-4 hover:bg-muted/50 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <component
                                            :is="getStatusIcon(attendance.status)"
                                            class="h-4 w-4"
                                            :class="getStatusColor(attendance.status)"
                                        />
                                        <span class="text-sm font-medium">
                                            {{ new Date(attendance.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                                        </span>
                                    </div>
                                    <span
                                        class="rounded-full border px-2 py-1 text-xs font-medium capitalize"
                                        :class="getStatusBadgeColor(attendance.status)"
                                    >
                                        {{ attendance.status === 'present' ? 'Hadir' : attendance.status === 'late' ? 'Terlambat' : 'Tidak Hadir' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-xs text-muted-foreground">Masuk</p>
                                        <p class="font-medium">{{ formatTime(attendance.check_in_time) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground">Keluar</p>
                                        <p class="font-medium">{{ formatTime(attendance.check_out_time) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground">Durasi</p>
                                        <p class="font-medium">{{ formatDuration(attendance.work_duration) }}</p>
                                    </div>
                                </div>

                                <div v-if="attendance.office_location" class="mt-2 flex items-center gap-1">
                                    <MapPin class="h-3 w-3 text-muted-foreground" />
                                    <span class="text-xs text-muted-foreground">{{ attendance.office_location.name }}</span>
                                </div>
                            </div>

                            <div v-if="!attendances?.data?.length" class="p-8 text-center">
                                <Calendar class="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
                                <p class="text-muted-foreground">Belum ada data absensi</p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="attendances?.meta?.total > attendances?.meta?.per_page" class="p-4 border-t">
                            <p class="text-sm text-muted-foreground text-center">
                                Menampilkan {{ attendances.meta.from }} - {{ attendances.meta.to }} dari {{ attendances.meta.total }} data
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <BottomNavigation current-route="/attendance" />
    </div>

        <!-- Check Out Modal -->
        <dialog ref="checkOutModal" class="rounded-lg border bg-card p-6 backdrop:bg-black/50 max-w-sm">
            <div>
                <h3 class="text-lg font-semibold mb-2">Konfirmasi Check Out</h3>
                <p class="text-sm text-muted-foreground mb-4">Apakah Anda yakin ingin check out sekarang?</p>
                <div class="flex gap-3">
                    <button
                        @click="$refs.checkOutModal.close()"
                        class="flex-1 rounded-md border bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        class="flex-1 rounded-md bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground hover:bg-destructive/90 transition-colors"
                    >
                        Konfirmasi
                    </button>
                </div>
            </div>
        </dialog>
</template>