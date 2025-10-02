<script setup lang="ts">
import AttendanceCalendar from '@/components/AttendanceCalendar.vue';
import BottomNavigation from '@/components/BottomNavigation.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Calendar, CheckCircle, Clock, Loader2, MapPin, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface OfficeLocation {
    id: number;
    name: string;
}

interface WorkShift {
    id: number;
    name: string;
    code: string;
    start_time: string;
    end_time: string;
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
    workShift?: WorkShift | null;
}

interface TodayAttendance {
    id: number;
    check_in_time: string | null;
    check_out_time: string | null;
    status: string;
    work_duration: number | null;
    office_location: OfficeLocation;
    workShift?: WorkShift | null;
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

// Debug logging
console.log('Attendance Index - Props:', { attendances, todayAttendance, filters });
console.log('Today Attendance with Shift:', todayAttendance);
console.log('Attendances Data:', attendances?.data);

const { toast } = useToast();

// State for calendar
const showCalendarView = ref(true);
const selectedMonth = ref(new Date());

// State for checkout
const isCheckingOut = ref(false);
const locationStatus = ref<'idle' | 'loading' | 'success' | 'error'>('idle');
const locationError = ref('');
const userLocation = ref({ latitude: 0, longitude: 0 });

// Checkout form
const checkoutForm = useForm({
    latitude: 0,
    longitude: 0,
});

// Transform attendance data for calendar
const calendarAttendanceData = computed(() => {
    if (!attendances?.data) return [];

    const transformedData = attendances.data.map((attendance) => ({
        date: attendance.date,
        status:
            attendance.status === 'present'
                ? 'present'
                : attendance.status === 'late'
                  ? 'late'
                  : attendance.status === 'absent'
                    ? 'absent'
                    : 'absent',
        check_in_time: attendance.check_in_time,
        check_out_time: attendance.check_out_time,
        notes: `Durasi: ${formatDuration(attendance.work_duration)}`,
    }));

    return transformedData;
});

// Handle calendar day click
const handleDayClick = (_date: string, _record?: any) => {
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
    
    // Log the original value for debugging
    console.log('formatDuration - original value:', minutes);
    
    // Always use absolute value for duration
    const absMinutes = Math.abs(minutes);
    const hours = Math.floor(absMinutes / 60);
    const mins = absMinutes % 60;
    
    const result = `${hours}h ${mins}m`;
    console.log('formatDuration - result:', result);
    
    return result;
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

// Location and checkout functions
const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        locationError.value = 'Geolocation is not supported by this browser.';
        return;
    }

    locationStatus.value = 'loading';
    navigator.geolocation.getCurrentPosition(
        (position) => {
            userLocation.value.latitude = position.coords.latitude;
            userLocation.value.longitude = position.coords.longitude;
            checkoutForm.latitude = position.coords.latitude;
            checkoutForm.longitude = position.coords.longitude;
            locationStatus.value = 'success';
        },
        (error) => {
            locationStatus.value = 'error';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = 'Location access denied. Please enable location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = 'Location information is unavailable.';
                    break;
                case error.TIMEOUT:
                    locationError.value = 'Location request timed out.';
                    break;
                default:
                    locationError.value = 'An unknown error occurred while retrieving location.';
                    break;
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000,
        },
    );
};

const handleCheckOutClick = () => {
    // Reset states and get location
    locationStatus.value = 'idle';
    locationError.value = '';
    getCurrentLocation();
    // Show modal after starting location detection
    document.getElementById('checkOutModal')?.showModal();
};

const confirmCheckOut = () => {
    if (locationStatus.value !== 'success') {
        toast({
            title: '❌ Tidak Dapat Check Out',
            description: 'Lokasi belum terdeteksi. Mohon tunggu atau coba lagi.',
            variant: 'destructive',
        });
        return;
    }

    isCheckingOut.value = true;
    toast({
        title: '🕐 Sedang Check Out...',
        description: 'Memproses absensi keluar Anda.',
        variant: 'default',
    });

    checkoutForm.post('/attendance/check-out', {
        onSuccess: () => {
            isCheckingOut.value = false;
            document.getElementById('checkOutModal')?.close();
            toast({
                title: '✅ Check Out Berhasil!',
                description: `Absensi keluar Anda telah tercatat pada ${new Date().toLocaleTimeString('id-ID')}.`,
                variant: 'success',
                duration: 5000,
            });
            // Refresh the page to update attendance data
            window.location.reload();
        },
        onError: (errors) => {
            isCheckingOut.value = false;
            console.error('Check-out errors:', errors);
            toast({
                title: '❌ Check Out Gagal',
                description: errors.message || 'Terjadi kesalahan saat melakukan check out. Silakan coba lagi.',
                variant: 'destructive',
                duration: 6000,
            });
        },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Riwayat Absensi" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Container -->
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <!-- Mobile Header -->
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
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
                <div class="mb-8 rounded-lg border bg-card p-6">
                    <h2 class="mb-6 text-lg font-semibold">Status Hari Ini</h2>

                    <!-- Clean horizontal layout for today's attendance -->
                    <div class="rounded-lg border bg-gradient-to-r from-card to-card/80 p-4">
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Check In -->
                            <div class="text-center">
                                <div class="mb-2 flex items-center justify-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                        <div class="h-2 w-2 rounded-full bg-green-600 dark:bg-green-400"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-green-700 dark:text-green-400">MASUK</span>
                                </div>
                                <p class="text-lg font-bold text-foreground">
                                    {{ formatTime(todayAttendance?.check_in_time) }}
                                </p>
                            </div>

                            <!-- Check Out -->
                            <div class="text-center">
                                <div class="mb-2 flex items-center justify-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                        <div class="h-2 w-2 rounded-full bg-red-600 dark:bg-red-400"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-red-700 dark:text-red-400">KELUAR</span>
                                </div>
                                <p class="text-lg font-bold text-foreground">
                                    {{ formatTime(todayAttendance?.check_out_time) }}
                                </p>
                            </div>

                            <!-- Duration -->
                            <div class="text-center">
                                <div class="mb-2 flex items-center justify-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                        <div class="h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">DURASI</span>
                                </div>
                                <p class="text-lg font-bold text-foreground">
                                    {{ formatDuration(todayAttendance?.work_duration) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div v-if="todayAttendance?.office_location" class="flex items-center gap-2 rounded-md bg-muted p-3">
                            <MapPin class="h-4 w-4 text-muted-foreground" />
                            <span class="text-sm text-muted-foreground">
                                {{ todayAttendance.office_location?.name || 'Remote' }}
                            </span>
                        </div>
                        <div v-if="todayAttendance?.workShift" class="flex items-center gap-2 rounded-md bg-blue-50 p-3 dark:bg-blue-900/20">
                            <Clock class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            <div class="flex-1">
                                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                    {{ todayAttendance.workShift.name }}
                                </span>
                                <span class="ml-2 text-xs text-blue-600 dark:text-blue-400">
                                    ({{ formatTime(todayAttendance.workShift.start_time) }} - {{ formatTime(todayAttendance.workShift.end_time) }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Check Out Button -->
                    <div v-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time" class="mt-4">
                        <button
                            @click="handleCheckOutClick"
                            class="flex w-full items-center justify-center gap-2 rounded-md bg-destructive px-4 py-3 font-medium text-destructive-foreground transition-colors hover:bg-destructive/90"
                        >
                            <Clock class="h-4 w-4" />
                            Check Out Sekarang
                        </button>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="mb-8 rounded-lg border bg-card p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Tampilan Riwayat</h3>
                        <div class="flex rounded-lg border bg-background">
                            <button
                                @click="showCalendarView = true"
                                :class="[
                                    'rounded-l-md px-3 py-1.5 text-xs font-medium transition-colors',
                                    showCalendarView ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
                                ]"
                            >
                                📅 Kalender
                            </button>
                            <button
                                @click="showCalendarView = false"
                                :class="[
                                    'rounded-r-md border-l px-3 py-1.5 text-xs font-medium transition-colors',
                                    !showCalendarView ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
                                ]"
                            >
                                📋 Daftar
                            </button>
                        </div>
                    </div>

                    <div v-if="!showCalendarView" class="grid grid-cols-2 gap-3">
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
                    <div class="border-b p-6">
                        <h2 class="text-lg font-semibold">Riwayat Kehadiran</h2>
                    </div>

                    <!-- Calendar View -->
                    <div v-if="showCalendarView" class="p-6">
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
                            <div v-for="attendance in attendances?.data || []" :key="attendance.id" class="p-6 transition-colors hover:bg-muted/50">
                                <div class="mb-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <component
                                            :is="getStatusIcon(attendance.status)"
                                            class="h-4 w-4"
                                            :class="getStatusColor(attendance.status)"
                                        />
                                        <span class="text-sm font-medium">
                                            {{
                                                new Date(attendance.date).toLocaleDateString('id-ID', {
                                                    weekday: 'long',
                                                    day: 'numeric',
                                                    month: 'long',
                                                })
                                            }}
                                        </span>
                                    </div>
                                    <span
                                        class="rounded-full border px-2 py-1 text-xs font-medium capitalize"
                                        :class="getStatusBadgeColor(attendance.status)"
                                    >
                                        {{ attendance.status === 'present' ? 'Hadir' : attendance.status === 'late' ? 'Terlambat' : 'Tidak Hadir' }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-6 text-sm">
                                    <div>
                                        <p class="text-xs text-muted-foreground">Masuk</p>
                                        <p class="font-medium">{{ formatTime(attendance.check_in_time) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground">Keluar</p>
                                        <p class="font-medium">{{ formatTime(attendance.check_out_time) }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-col gap-2">
                                    <div v-if="attendance.workShift" class="flex items-center gap-1">
                                        <Clock class="h-3 w-3 text-blue-600 dark:text-blue-400" />
                                        <span class="text-xs text-blue-600 dark:text-blue-400">
                                            Shift: {{ attendance.workShift.name }} ({{ formatTime(attendance.workShift.start_time) }} -
                                            {{ formatTime(attendance.workShift.end_time) }})
                                        </span>
                                    </div>
                                    <div v-if="attendance.office_location" class="flex items-center gap-1">
                                        <MapPin class="h-3 w-3 text-muted-foreground" />
                                        <span class="text-xs text-muted-foreground">{{ attendance.office_location?.name || 'Remote' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <Clock class="h-3 w-3 text-muted-foreground" />
                                        <span class="text-xs text-muted-foreground">Durasi: {{ formatDuration(attendance.work_duration) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!attendances?.data?.length" class="p-8 text-center">
                                <Calendar class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                                <p class="text-muted-foreground">Belum ada data absensi</p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="attendances?.meta?.total > attendances?.meta?.per_page" class="border-t p-6">
                            <p class="text-center text-sm text-muted-foreground">
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
    <dialog
        ref="checkOutModal"
        id="checkOutModal"
        class="fixed top-1/2 left-1/2 m-0 max-w-sm -translate-x-1/2 -translate-y-1/2 transform rounded-lg border bg-card p-6 backdrop:bg-black/50"
    >
        <div>
            <h3 class="mb-2 text-lg font-semibold">Konfirmasi Check Out</h3>
            <p class="mb-4 text-sm text-muted-foreground">Apakah Anda yakin ingin check out sekarang?</p>

            <!-- Location Status -->
            <div class="mb-4 rounded-lg border p-3">
                <div v-if="locationStatus === 'loading'" class="flex items-center gap-2 text-blue-600">
                    <Loader2 class="h-4 w-4 animate-spin" />
                    <span class="text-sm">Mendeteksi lokasi...</span>
                </div>
                <div v-else-if="locationStatus === 'success'" class="flex items-center gap-2 text-green-600">
                    <CheckCircle class="h-4 w-4" />
                    <div class="text-sm">
                        <div class="font-medium">Lokasi terdeteksi</div>
                        <div class="text-xs text-muted-foreground">
                            {{ userLocation.latitude.toFixed(6) }}, {{ userLocation.longitude.toFixed(6) }}
                        </div>
                    </div>
                </div>
                <div v-else-if="locationStatus === 'error'" class="flex items-center gap-2 text-red-600">
                    <XCircle class="h-4 w-4" />
                    <div class="text-sm">
                        <div class="font-medium">Gagal mendeteksi lokasi</div>
                        <div class="text-xs">{{ locationError }}</div>
                    </div>
                </div>
                <div v-else class="flex items-center gap-2 text-muted-foreground">
                    <MapPin class="h-4 w-4" />
                    <span class="text-sm">Menunggu deteksi lokasi...</span>
                </div>
            </div>

            <div class="flex gap-3">
                <button
                    @click="$refs.checkOutModal.close()"
                    class="flex-1 rounded-md border bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                >
                    Batal
                </button>
                <button
                    @click="confirmCheckOut"
                    :disabled="locationStatus !== 'success' || isCheckingOut"
                    class="flex-1 rounded-md bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground transition-colors hover:bg-destructive/90 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Loader2 v-if="isCheckingOut" class="mr-2 h-4 w-4 animate-spin" />
                    {{ isCheckingOut ? 'Memproses...' : 'Konfirmasi' }}
                </button>
            </div>
        </div>
    </dialog>
</template>
