<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { Clock, Calendar, CheckCircle, User, MapPin, LogOut, UserCircle, BarChart3, Loader2 } from 'lucide-vue-next';
import { useCompanySettings } from '@/composables/useCompanySettings';
import { useToast } from '@/components/ui/toast/use-toast';
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

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
}

interface Props {
    user?: User;
    todayAttendance?: TodayAttendance | null;
    officeLocations?: OfficeLocation[];
    stats?: {
        monthly_attendance_days: number;
        monthly_late_days: number;
        monthly_work_hours: number;
        leave_balance: number;
    };
}

const { user, todayAttendance, officeLocations, stats } = defineProps<Props>();

const { companyName, companyLogo } = useCompanySettings();
const { toast } = useToast();

// Check-in form and location state
const form = useForm({
    office_location_id: '',
    latitude: 0,
    longitude: 0,
});

const locationStatus = ref<'idle' | 'loading' | 'success' | 'error'>('idle');
const locationError = ref('');
const selectedOffice = ref<OfficeLocation | null>(null);
const isInRange = ref(false);
const distanceToOffice = ref(0);
const isCheckingIn = ref(false);
const isCheckingOut = ref(false);

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

// Location and check-in functions
const calculateDistance = (lat1: number, lng1: number, lat2: number, lng2: number) => {
    const earthRadius = 6371000; // meters
    const lat1Rad = (lat1 * Math.PI) / 180;
    const lng1Rad = (lng1 * Math.PI) / 180;
    const lat2Rad = (lat2 * Math.PI) / 180;
    const lng2Rad = (lng2 * Math.PI) / 180;

    const deltaLat = lat2Rad - lat1Rad;
    const deltaLng = lng2Rad - lng1Rad;

    const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
              Math.cos(lat1Rad) * Math.cos(lat2Rad) *
              Math.sin(deltaLng / 2) * Math.sin(deltaLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return earthRadius * c;
};

const getCurrentLocation = (autoAction = false) => {
    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        locationError.value = 'Geolocation is not supported by this browser.';
        return;
    }

    locationStatus.value = 'loading';
    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            locationStatus.value = 'success';
            checkOfficeProximity();

            // Auto perform action if requested
            if (autoAction === 'checkin') {
                performCheckIn();
            } else if (autoAction === 'checkout') {
                performCheckOut();
            }
        },
        (error) => {
            locationStatus.value = 'error';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = 'Akses lokasi ditolak. Mohon izinkan akses lokasi.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = 'Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    locationError.value = 'Permintaan lokasi timeout.';
                    break;
                default:
                    locationError.value = 'Terjadi kesalahan saat mengambil lokasi.';
                    break;
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000
        }
    );
};

const checkOfficeProximity = () => {
    if (!form.latitude || !form.longitude || !officeLocations) return;

    let nearestOffice: OfficeLocation | null = null;
    let nearestDistance = Infinity;
    let isWithinRange = false;

    officeLocations.forEach(office => {
        const distance = calculateDistance(
            form.latitude,
            form.longitude,
            office.latitude,
            office.longitude
        );

        if (distance < nearestDistance) {
            nearestDistance = distance;
            nearestOffice = office;
        }

        if (distance <= office.radius_meters) {
            isWithinRange = true;
            form.office_location_id = office.id.toString();
        }
    });

    selectedOffice.value = nearestOffice;
    distanceToOffice.value = Math.round(nearestDistance);
    isInRange.value = isWithinRange;
};

const performCheckIn = () => {
    if (!isInRange.value) {
        if (selectedOffice.value) {
            const distanceNeeded = Math.round(distanceToOffice.value - selectedOffice.value.radius_meters);
            toast({
                title: '❌ Tidak Dapat Check In',
                description: `Anda masih berada di luar radius ${selectedOffice.value.radius_meters}m. Mohon mendekat ${distanceNeeded}m lagi ke ${selectedOffice.value.name}.`,
                variant: 'destructive',
                duration: 6000,
            });
        } else {
            toast({
                title: '❌ Tidak Dapat Check In',
                description: 'Anda tidak berada dalam jangkauan lokasi kantor manapun. Mohon mendekat ke lokasi kantor.',
                variant: 'destructive',
            });
        }
        locationStatus.value = 'idle'; // Reset status so user can try again
        return;
    }

    isCheckingIn.value = true;
    toast({
        title: '🕐 Sedang Check In...',
        description: 'Memproses absensi masuk Anda.',
        variant: 'default',
    });

    form.post('/attendance/check-in', {
        onSuccess: () => {
            isCheckingIn.value = false;
            locationStatus.value = 'idle'; // Reset for next time
            toast({
                title: '✅ Check In Berhasil!',
                description: `Absensi masuk Anda telah tercatat pada ${new Date().toLocaleTimeString('id-ID')}.`,
                variant: 'success',
                duration: 5000,
            });
            // Refresh the page to update attendance data
            window.location.reload();
        },
        onError: (errors) => {
            isCheckingIn.value = false;
            locationStatus.value = 'idle'; // Reset so user can try again
            console.error('Check-in errors:', errors);
            toast({
                title: '❌ Check In Gagal',
                description: errors.message || 'Terjadi kesalahan saat melakukan check in. Silakan coba lagi.',
                variant: 'destructive',
                duration: 6000,
            });
        },
        preserveState: true,
        preserveScroll: true,
    });
};

const handleCheckInClick = () => {
    if (locationStatus.value === 'idle' || locationStatus.value === 'error') {
        getCurrentLocation('checkin');
    } else if (locationStatus.value === 'success' && isInRange.value) {
        performCheckIn();
    }
};

const performCheckOut = () => {
    isCheckingOut.value = true;
    toast({
        title: '🕐 Sedang Check Out...',
        description: 'Memproses absensi keluar Anda.',
        variant: 'default',
    });

    const checkoutForm = useForm({
        latitude: form.latitude,
        longitude: form.longitude,
    });

    checkoutForm.post('/attendance/check-out', {
        onSuccess: () => {
            isCheckingOut.value = false;
            locationStatus.value = 'idle'; // Reset for next time
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
            locationStatus.value = 'idle'; // Reset so user can try again
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

const handleCheckOutClick = () => {
    if (locationStatus.value === 'idle' || locationStatus.value === 'error') {
        getCurrentLocation('checkout');
    } else if (locationStatus.value === 'success') {
        performCheckOut();
    }
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
                        <button
                            v-if="!todayAttendance?.check_in_time"
                            @click="handleCheckInClick"
                            :disabled="isCheckingIn || (locationStatus === 'success' && !isInRange)"
                            class="flex items-center justify-center gap-2 rounded-md px-4 py-4 text-white font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{
                                'bg-green-600 hover:bg-green-700': locationStatus === 'idle' || locationStatus === 'error' || (locationStatus === 'success' && isInRange),
                                'bg-blue-600 hover:bg-blue-700': locationStatus === 'loading',
                                'bg-gray-500': locationStatus === 'success' && !isInRange
                            }"
                        >
                            <Loader2 v-if="locationStatus === 'loading' || isCheckingIn" class="h-5 w-5 animate-spin" />
                            <Clock v-else class="h-5 w-5" />
                            <span class="text-sm">
                                {{ isCheckingIn ? 'Sedang Check In...' :
                                   locationStatus === 'loading' ? 'Mencari Lokasi...' :
                                   locationStatus === 'success' && !isInRange ? 'Di Luar Area' :
                                   locationStatus === 'error' ? 'Coba Lagi' :
                                   'Check In' }}
                            </span>
                        </button>
                        <div
                            v-else
                            class="flex items-center justify-center gap-2 rounded-md bg-green-100 dark:bg-green-900/20 px-4 py-4 text-green-800 dark:text-green-200 font-medium"
                        >
                            <CheckCircle class="h-5 w-5" />
                            <span class="text-sm">Sudah Masuk</span>
                        </div>

                        <!-- Check Out Button -->
                        <button
                            v-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time"
                            @click="handleCheckOutClick"
                            :disabled="isCheckingOut"
                            class="flex items-center justify-center gap-2 rounded-md px-4 py-4 text-white font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="{
                                'bg-red-600 hover:bg-red-700': locationStatus === 'idle' || locationStatus === 'error' || locationStatus === 'success',
                                'bg-blue-600 hover:bg-blue-700': locationStatus === 'loading'
                            }"
                        >
                            <Loader2 v-if="locationStatus === 'loading' || isCheckingOut" class="h-5 w-5 animate-spin" />
                            <Clock v-else class="h-5 w-5" />
                            <span class="text-sm">
                                {{ isCheckingOut ? 'Sedang Check Out...' :
                                   locationStatus === 'loading' ? 'Mencari Lokasi...' :
                                   locationStatus === 'error' ? 'Coba Lagi' :
                                   'Check Out' }}
                            </span>
                        </button>
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