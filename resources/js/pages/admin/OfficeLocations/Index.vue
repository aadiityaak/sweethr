<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, MapPin, Clock, Users, Edit, Trash2, Map, Navigation, Target } from 'lucide-vue-next';
import { ref } from 'vue';
import LeafletMap from '@/components/LeafletMap.vue';

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    employees_count?: number;
    today_checkins_count?: number;
}

interface Props {
    officeLocations?: {
        data: OfficeLocation[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
        status?: string;
    };
    stats: {
        total_locations: number;
        active_locations: number;
        total_radius_coverage: number;
        today_checkins: number;
    };
}

const { officeLocations, filters, stats } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Office Locations',
        href: '/office-locations',
    },
];

const searchQuery = ref(filters.search || '');
const selectedStatus = ref(filters.status || '');

const filterLocations = () => {
    router.get('/office-locations', {
        search: searchQuery.value || undefined,
        status: selectedStatus.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const getStatusBadge = (isActive: boolean) => {
    return isActive
        ? 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-600'
        : 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-600';
};

const formatCoordinates = (lat: number | string | null, lng: number | string | null) => {
    const latNum = typeof lat === 'string' ? parseFloat(lat) : lat;
    const lngNum = typeof lng === 'string' ? parseFloat(lng) : lng;

    if (!latNum || !lngNum || isNaN(latNum) || isNaN(lngNum)) {
        return 'Invalid coordinates';
    }

    return `${latNum.toFixed(6)}, ${lngNum.toFixed(6)}`;
};

const openInGoogleMaps = (location: OfficeLocation) => {
    const url = `https://www.google.com/maps?q=${location.latitude},${location.longitude}`;
    window.open(url, '_blank');
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

const selectedLocation = ref<OfficeLocation | null>(null);
const showMapModal = ref(false);

const viewOnMap = (location: OfficeLocation) => {
    selectedLocation.value = location;
    showMapModal.value = true;
};

const closeMapModal = () => {
    showMapModal.value = false;
    selectedLocation.value = null;
};
</script>

<template>
    <Head title="Office Locations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Office Locations</h1>
                    <p class="text-gray-600 dark:text-gray-300">Manage office locations and attendance zones</p>
                </div>
                <Link
                    href="/office-locations/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    <Plus class="h-4 w-4" />
                    Add Location
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <MapPin class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Total Locations</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.total_locations }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <Target class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Active Locations</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.active_locations }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <Navigation class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Coverage Area</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.total_radius_coverage }}m²</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <Clock class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Today's Check-ins</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.today_checkins }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            @input="filterLocations"
                            type="text"
                            placeholder="Search locations..."
                            class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <select
                        v-model="selectedStatus"
                        @change="filterLocations"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="flex gap-2">
                        <button
                            @click="filterLocations"
                            class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Apply
                        </button>
                    </div>
                </div>
            </div>

            <!-- Office Locations List -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-950">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Locations</h2>
                </div>

                <div class="grid gap-4 p-6 pt-0 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="location in officeLocations?.data || []"
                        :key="location.id"
                        class="rounded-lg border border-gray-200 p-6 dark:border-gray-700"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <MapPin class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ location.name }}</h3>
                                    <span
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                                        :class="getStatusBadge(location.is_active)"
                                    >
                                        {{ location.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    @click="viewOnMap(location)"
                                    class="rounded-lg bg-green-100 p-2 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50"
                                    title="View on Map"
                                >
                                    <Map class="h-4 w-4" />
                                </button>
                                <Link
                                    :href="`/office-locations/${location.id}/edit`"
                                    class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                >
                                    <Edit class="h-4 w-4" />
                                </Link>
                                <button class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    <MapPin class="inline h-4 w-4" />
                                    {{ location.address }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Coordinates:</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ formatCoordinates(location.latitude, location.longitude) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Check-in Radius:</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ location.radius_meters }}m</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <div class="flex items-center gap-4">
                                    <div class="text-center">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ location.employees_count || 0 }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Employees</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ location.today_checkins_count || 0 }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Today's Check-ins</p>
                                    </div>
                                </div>
                                <button
                                    @click="openInGoogleMaps(location)"
                                    class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <Navigation class="inline h-4 w-4 mr-1" />
                                    Directions
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="officeLocations?.meta?.total > officeLocations?.meta?.per_page" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Showing {{ officeLocations.meta.from }} to {{ officeLocations.meta.to }} of {{ officeLocations.meta.total }} results
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in officeLocations.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1 text-sm border rounded',
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'text-gray-600 border-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-800'
                                ]"
                                :disabled="!link.url"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Modal -->
        <div
            v-if="showMapModal && selectedLocation"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click="closeMapModal"
        >
            <div
                class="w-full max-w-4xl rounded-xl border-0 bg-white p-6 dark:bg-gray-950"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ selectedLocation.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ selectedLocation.address }}</p>
                    </div>
                    <button
                        @click="closeMapModal"
                        class="rounded-lg bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        ×
                    </button>
                </div>

                <!-- Interactive Leaflet Map -->
                <LeafletMap
                    :locations="[selectedLocation]"
                    :selected-location="selectedLocation"
                    height="400px"
                    :show-radius="true"
                    :interactive="true"
                />

                <div class="mt-4 flex justify-between">
                    <div class="flex gap-2">
                        <button
                            @click="openInGoogleMaps(selectedLocation)"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Navigation class="h-4 w-4" />
                            Open in Google Maps
                        </button>
                    </div>
                    <button
                        @click="closeMapModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>