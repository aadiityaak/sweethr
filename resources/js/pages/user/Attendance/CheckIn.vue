<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { MapPin, Clock, AlertCircle, CheckCircle, Loader2 } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import LeafletMap from '@/components/LeafletMap.vue';

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Attendance',
        href: '/attendance',
    },
    {
        title: 'Check In',
        href: '/attendance/check-in',
    },
];

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

onMounted(() => {
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
    isInRange.value = isWithinRange;
};

const submitCheckIn = () => {
    if (!isInRange.value) {
        alert('You are not within range of any office location. Please move closer to an office.');
        return;
    }

    form.post('/attendance/check-in');
};
</script>

<template>
    <Head title="Check In" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Check In</h1>
                <p class="text-gray-600 dark:text-gray-300">Use your location to check in to work</p>
            </div>

            <!-- Location Status -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Location Status</h2>

                <!-- Loading State -->
                <div v-if="locationStatus === 'loading'" class="flex items-center gap-3">
                    <Loader2 class="h-5 w-5 animate-spin text-blue-600" />
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Getting your location...</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Please allow location access when prompted</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="locationStatus === 'error'" class="flex items-center gap-3">
                    <div class="rounded-lg bg-red-100 p-2 dark:bg-red-900/30">
                        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <p class="font-medium text-red-900 dark:text-red-400">Location Error</p>
                        <p class="text-sm text-red-600 dark:text-red-300">{{ locationError }}</p>
                        <button
                            @click="getCurrentLocation"
                            class="mt-2 rounded-lg bg-red-600 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                        >
                            Try Again
                        </button>
                    </div>
                </div>

                <!-- Success State -->
                <div v-else class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="font-medium text-green-900 dark:text-green-400">Location Found</p>
                            <p class="text-sm text-green-600 dark:text-green-300">
                                Coordinates: {{ form.latitude.toFixed(6) }}, {{ form.longitude.toFixed(6) }}
                            </p>
                        </div>
                    </div>

                    <!-- Office Proximity -->
                    <div v-if="selectedOffice" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ selectedOffice.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ selectedOffice.address }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <MapPin class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        Distance: {{ distanceToOffice }}m
                                    </span>
                                </div>
                            </div>
                            <div v-if="isInRange" class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div v-else class="rounded-lg bg-red-100 p-2 dark:bg-red-900/30">
                                <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                        </div>

                        <div class="mt-3">
                            <div v-if="isInRange" class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                    ✓ You are within the check-in radius ({{ selectedOffice.radius_meters }}m)
                                </p>
                            </div>
                            <div v-else class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                                <p class="text-sm font-medium text-red-800 dark:text-red-300">
                                    ✗ You need to be within {{ selectedOffice.radius_meters }}m to check in
                                </p>
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    Please move {{ distanceToOffice - selectedOffice.radius_meters }}m closer
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office Locations -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Available Office Locations</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div
                        v-for="office in officeLocations"
                        :key="office.id"
                        class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                        :class="{
                            'border-green-300 bg-green-50 dark:border-green-600 dark:bg-green-900/20':
                                form.office_location_id === office.id.toString()
                        }"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ office.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ office.address }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <MapPin class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        Radius: {{ office.radius_meters }}m
                                    </span>
                                </div>
                            </div>
                            <div v-if="form.office_location_id === office.id.toString()" class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                                <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Map -->
            <div v-if="locationStatus === 'success'" class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Location Map</h2>
                <LeafletMap
                    :locations="officeLocations"
                    :user-location="{ latitude: form.latitude, longitude: form.longitude }"
                    height="300px"
                    :show-radius="true"
                    :interactive="true"
                />
            </div>

            <!-- Check In Action -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ready to Check In?</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ isInRange ? 'You can now check in!' : 'Please move closer to an office location' }}
                        </p>
                    </div>
                    <button
                        @click="submitCheckIn"
                        :disabled="!isInRange || form.processing"
                        class="inline-flex items-center gap-2 rounded-lg px-6 py-3 text-sm font-medium text-white disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="isInRange
                            ? 'bg-green-600 hover:bg-green-700'
                            : 'bg-gray-400 cursor-not-allowed'"
                    >
                        <Clock class="h-4 w-4" />
                        <span v-if="form.processing">Checking In...</span>
                        <span v-else>Check In Now</span>
                    </button>
                </div>

                <!-- Current Time -->
                <div class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Current time: {{ new Date().toLocaleTimeString() }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>