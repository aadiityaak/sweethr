<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { MapPin, Clock, AlertCircle, CheckCircle, Loader2, ArrowLeft, User, AlertTriangle } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import LeafletMap from '@/components/LeafletMap.vue';
import BottomNavigation from '@/components/BottomNavigation.vue';
import { useCompanySettings } from '@/composables/useCompanySettings';
import { useToast } from '@/components/ui/toast/use-toast';

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
}

interface Props {
    officeLocations: OfficeLocation[];
    existingAttendance: any | null;
}

const { officeLocations, existingAttendance } = defineProps<Props>();

const { companyName, companyLogo } = useCompanySettings();
const { toast } = useToast();

const form = useForm({
    office_location_id: '',
    latitude: 0,
    longitude: 0,
});

const locationStatus = ref<'loading' | 'success' | 'error'>('loading');
const locationError = ref('');
const selectedOffice = ref<OfficeLocation | null>(null);
const isInRange = ref(false);
const distanceToOffice = ref(0);
const hasShownOutOfRangeAlert = ref(false);
const hasCheckedInToday = ref(false);

onMounted(() => {
    // Check if user already checked in today
    if (existingAttendance?.check_in_time) {
        hasCheckedInToday.value = true;
    }
    getCurrentLocation();
});

const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        locationError.value = 'Geolocation is not supported by this browser.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            locationStatus.value = 'success';
            checkOfficeProximity();
        },
        (error) => {
            locationStatus.value = 'error';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = 'Location access denied. Please enable location access and refresh the page.';
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
            maximumAge: 60000
        }
    );
};

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

const checkOfficeProximity = () => {
    if (!form.latitude || !form.longitude) return;

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

    // Show immediate feedback when location is determined
    if (nearestOffice) {
        if (isWithinRange && !isInRange.value) {
            // User just entered the range
            toast({
                title: '✅ Lokasi Ditemukan!',
                description: `Anda berada dalam radius ${nearestOffice.name}. Siap untuk check in!`,
                variant: 'success',
            });
        } else if (!isWithinRange && !hasShownOutOfRangeAlert.value) {
            // User is outside range - show alert immediately
            const distanceNeeded = Math.round(nearestDistance - nearestOffice.radius_meters);
            toast({
                title: '⚠️ Di Luar Jangkauan',
                description: `Anda harus berada dalam radius ${nearestOffice.radius_meters}m. Mohon mendekat ${distanceNeeded}m lagi ke ${nearestOffice.name}.`,
                variant: 'destructive',
                duration: 8000, // Show longer for important info
            });
            hasShownOutOfRangeAlert.value = true;
        }
    }

    isInRange.value = isWithinRange;
};

const submitCheckIn = () => {
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
        return;
    }

    toast({
        title: '🕐 Sedang Check In...',
        description: 'Memproses absensi masuk Anda.',
        variant: 'default',
    });

    form.post('/attendance/check-in', {
        onSuccess: (page) => {
            hasCheckedInToday.value = true;
            toast({
                title: '✅ Check In Berhasil!',
                description: `Absensi masuk Anda telah tercatat pada ${new Date().toLocaleTimeString('id-ID')}.`,
                variant: 'success',
                duration: 5000,
            });

            // Update the form to reflect successful check-in
            form.reset();
        },
        onError: (errors) => {
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
</script>

<template>
    <Head title="Check In - SweetHR" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Header - Full Width -->
        <div class="bg-background/95 backdrop-blur-sm border-b sticky top-0 z-40">
            <div class="mx-auto max-w-[480px] px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <Link
                            href="/home"
                            class="rounded-lg bg-muted p-2 hover:bg-muted/80 transition-colors"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
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
                            <h1 class="text-lg font-semibold">Check In</h1>
                            <p class="text-sm text-muted-foreground">Absensi Masuk</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Container for Content -->
        <div class="mx-auto max-w-[480px] bg-background min-h-screen relative">
            <!-- Main Content -->
            <div class="px-4 py-6 pb-20 space-y-6">
                <!-- Out of Range Alert -->
                <div v-if="locationStatus === 'success' && selectedOffice && !isInRange"
                     class="rounded-lg border-2 border-destructive/50 bg-destructive/10 p-4 animate-pulse">
                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-destructive/20 p-2">
                            <AlertTriangle class="h-5 w-5 text-destructive" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-destructive">Di Luar Jangkauan!</h3>
                            <p class="text-sm text-destructive/80 mt-1">
                                Anda harus berada dalam radius {{ selectedOffice.radius_meters }}m dari {{ selectedOffice.name }}
                            </p>
                            <p class="text-sm font-medium text-destructive mt-2">
                                📍 Mohon mendekat {{ Math.round(distanceToOffice - selectedOffice.radius_meters) }}m lagi
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Success Alert -->
                <div v-if="locationStatus === 'success' && isInRange"
                     class="rounded-lg border-2 border-green-500/50 bg-green-50 dark:bg-green-950/50 p-4">
                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-green-100 dark:bg-green-900/50 p-2">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-green-800 dark:text-green-300">Siap Check In!</h3>
                            <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                Anda berada dalam radius {{ selectedOffice?.name }}. Silakan lakukan check in.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Location Status -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="mb-4 text-lg font-semibold flex items-center gap-2">
                        <MapPin class="h-4 w-4 text-primary" />
                        Status Lokasi
                    </h2>

                    <!-- Loading State -->
                    <div v-if="locationStatus === 'loading'" class="flex items-center gap-3 p-4 bg-muted/50 rounded-lg">
                        <Loader2 class="h-5 w-5 animate-spin text-primary" />
                        <div>
                            <p class="font-medium">Mencari lokasi Anda...</p>
                            <p class="text-sm text-muted-foreground">Mohon izinkan akses lokasi</p>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="locationStatus === 'error'" class="p-4 bg-destructive/10 border border-destructive/20 rounded-lg">
                        <div class="flex items-start gap-3">
                            <div class="rounded-lg bg-destructive/20 p-2">
                                <AlertCircle class="h-5 w-5 text-destructive" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-destructive">Error Lokasi</p>
                                <p class="text-sm text-destructive/80 mt-1">{{ locationError }}</p>
                                <button
                                    @click="getCurrentLocation"
                                    class="mt-3 rounded-lg bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground hover:bg-destructive/90 transition-colors"
                                >
                                    Coba Lagi
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Success State -->
                    <div v-else class="space-y-4">
                        <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-950/50 border border-green-200 dark:border-green-800 rounded-lg">
                            <div class="rounded-lg bg-green-100 dark:bg-green-900/50 p-2">
                                <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="font-medium text-green-900 dark:text-green-100">Lokasi Ditemukan</p>
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    {{ form.latitude.toFixed(6) }}, {{ form.longitude.toFixed(6) }}
                                </p>
                            </div>
                        </div>

                        <!-- Office Proximity -->
                        <div v-if="selectedOffice" class="rounded-lg border bg-card p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="font-medium">{{ selectedOffice.name }}</h3>
                                    <p class="text-sm text-muted-foreground">{{ selectedOffice.address }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <MapPin class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm text-muted-foreground">
                                            Jarak: {{ distanceToOffice }}m
                                        </span>
                                    </div>
                                </div>
                                <div v-if="isInRange" class="rounded-lg bg-green-100 dark:bg-green-900/50 p-2">
                                    <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div v-else class="rounded-lg bg-red-100 dark:bg-red-900/50 p-2">
                                    <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                                </div>
                            </div>

                            <div v-if="isInRange" class="rounded-lg bg-green-50 dark:bg-green-950/50 border border-green-200 dark:border-green-800 p-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                    ✓ Anda berada dalam radius check-in ({{ selectedOffice.radius_meters }}m)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Office Locations -->
                <div class="rounded-lg border bg-card p-6">
                    <h2 class="mb-4 text-lg font-semibold flex items-center gap-2">
                        <Clock class="h-4 w-4 text-primary" />
                        Lokasi Kantor
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="office in officeLocations"
                            :key="office.id"
                            class="rounded-lg border p-4 transition-colors"
                            :class="{
                                'border-green-300 bg-green-50 dark:border-green-600 dark:bg-green-900/20':
                                    form.office_location_id === office.id.toString(),
                                'border-border hover:bg-muted/50': form.office_location_id !== office.id.toString()
                            }"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-medium">{{ office.name }}</h3>
                                    <p class="text-sm text-muted-foreground mt-1">{{ office.address }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-primary"></div>
                                        <span class="text-sm text-muted-foreground">
                                            Radius: {{ office.radius_meters }}m
                                        </span>
                                    </div>
                                </div>
                                <div v-if="form.office_location_id === office.id.toString()" class="rounded-lg bg-green-100 dark:bg-green-900/50 p-2">
                                    <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Map -->
                <div v-if="locationStatus === 'success'" class="rounded-lg border bg-card p-6">
                    <h2 class="mb-4 text-lg font-semibold flex items-center gap-2">
                        <MapPin class="h-4 w-4 text-primary" />
                        Peta Lokasi
                    </h2>
                    <div class="rounded-lg overflow-hidden border">
                        <LeafletMap
                            :locations="officeLocations"
                            :user-location="{ latitude: form.latitude, longitude: form.longitude }"
                            height="250px"
                            :show-radius="true"
                            :interactive="true"
                        />
                    </div>
                </div>

                <!-- Check In Action -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="text-center space-y-4">
                        <!-- Already Checked In State -->
                        <div v-if="hasCheckedInToday" class="mx-auto w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                            <CheckCircle class="h-8 w-8 text-green-600 dark:text-green-400" />
                        </div>
                        <!-- Normal States -->
                        <div v-else class="mx-auto w-16 h-16 rounded-full flex items-center justify-center"
                             :class="isInRange ? 'bg-green-100 dark:bg-green-900/50' : 'bg-muted'">
                            <Clock class="h-8 w-8" :class="isInRange ? 'text-green-600 dark:text-green-400' : 'text-muted-foreground'" />
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ hasCheckedInToday ? 'Sudah Check In Hari Ini' : (isInRange ? 'Siap Check In!' : 'Belum Bisa Check In') }}
                            </h2>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ hasCheckedInToday
                                    ? 'Absensi masuk Anda sudah tercatat untuk hari ini'
                                    : (isInRange ? 'Anda dapat melakukan absensi masuk sekarang' : 'Mohon mendekat ke lokasi kantor')
                                }}
                            </p>
                        </div>

                        <button
                            v-if="!hasCheckedInToday"
                            @click="submitCheckIn"
                            :disabled="!isInRange || form.processing"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg px-6 py-4 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="isInRange
                                ? 'bg-green-600 hover:bg-green-700 text-white shadow-sm'
                                : 'bg-muted text-muted-foreground cursor-not-allowed'"
                        >
                            <Clock class="h-4 w-4" />
                            <span v-if="form.processing">Sedang Check In...</span>
                            <span v-else>{{ isInRange ? 'Check In Sekarang' : 'Belum Bisa Check In' }}</span>
                        </button>

                        <!-- Already Checked In Button -->
                        <div v-if="hasCheckedInToday" class="w-full inline-flex items-center justify-center gap-2 rounded-lg px-6 py-4 text-sm font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800">
                            <CheckCircle class="h-4 w-4" />
                            <span>Sudah Check In</span>
                        </div>

                        <!-- Current Time -->
                        <div class="rounded-lg bg-muted/50 p-3 border">
                            <p class="text-sm text-muted-foreground text-center">
                                <Clock class="h-4 w-4 inline mr-2" />
                                Waktu saat ini: {{ new Date().toLocaleTimeString('id-ID') }}
                            </p>
                        </div>

                        <!-- Navigation Button -->
                        <Link
                            href="/home"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-primary/20 bg-primary/10 px-6 py-3 text-sm font-medium text-primary hover:bg-primary/20 transition-colors"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Kembali ke Beranda
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <BottomNavigation current-route="/attendance" />
    </div>
</template>