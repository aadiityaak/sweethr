<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import LeafletMap from '@/components/LeafletMap.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Clock, Edit, Map, MapPin, Navigation, Plus, Search, Target, Trash2, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
        title: 'Lokasi Kantor',
        href: '/office-locations',
    },
];

const searchQuery = ref(filters.search || '');
const selectedStatus = ref(filters.status || '');

const filterLocations = () => {
    router.get(
        '/office-locations',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
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

const selectedLocation = ref<OfficeLocation | null>(null);
const showMapModal = ref(false);

// Confirmation modal state
const showDeleteModal = ref(false);
const locationToDelete = ref<OfficeLocation | null>(null);
const isDeleting = ref(false);

// Computed property for delete message
const deleteMessage = computed(() => {
    if (!locationToDelete.value) return '';
    return `Apakah Anda yakin ingin menghapus lokasi "${locationToDelete.value.name}"? Tindakan ini tidak dapat dibatalkan.`;
});

const viewOnMap = (location: OfficeLocation) => {
    selectedLocation.value = location;
    showMapModal.value = true;
};

const closeMapModal = () => {
    showMapModal.value = false;
    selectedLocation.value = null;
};

const deleteLocation = (location: OfficeLocation) => {
    locationToDelete.value = location;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!locationToDelete.value) return;

    isDeleting.value = true;

    router.delete(`/office-locations/${locationToDelete.value.id}`, {
        onSuccess: () => {
            // Reset modal state
            showDeleteModal.value = false;
            locationToDelete.value = null;
            isDeleting.value = false;
            // Refresh data after successful deletion
            router.reload();
        },
        onError: (errors) => {
            console.error('Error deleting location:', errors);
            isDeleting.value = false;
            // Keep modal open to show error or add error handling
        },
    });
};

const cancelDelete = () => {
    if (!isDeleting.value) {
        showDeleteModal.value = false;
        locationToDelete.value = null;
    }
};
</script>

<template>
    <Head title="Lokasi Kantor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Lokasi Kantor</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola lokasi kantor dan zona absensi</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            href="/office-locations/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Plus class="h-4 w-4" />
                            Tambah Lokasi
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Locations -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <MapPin class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Lokasi</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Semua lokasi kantor</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ stats.total_locations }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Aktif</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ stats.active_locations }}</span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Active Locations -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <Target class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Lokasi Aktif</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Lokasi yang digunakan</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Aktif</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                {{ stats.active_locations }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total_locations ? Math.round((stats.active_locations / stats.total_locations) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Coverage Area -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/10 ring-1 ring-purple-500/20">
                                <Navigation class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Area Cakupan</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total radius area</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-purple-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-purple-50/50 px-3 py-2 dark:bg-purple-950/30">
                            <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Total</span>
                            <span class="text-sm font-semibold text-purple-800 dark:text-purple-300"> {{ stats.total_radius_coverage }}m² </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total_locations ? Math.round(stats.total_radius_coverage / stats.total_locations) : 0 }}m²
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Today's Check-ins -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                                <Clock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Absensi Hari Ini</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Check-in hari ini</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-amber-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-amber-50/50 px-3 py-2 dark:bg-amber-950/30">
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Total</span>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                {{ stats.today_checkins }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Per Lokasi</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.active_locations ? Math.round(stats.today_checkins / stats.active_locations) : 0 }}
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="group relative mb-10 overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30"
            >
                <div class="border-b border-gray-200/50 p-6 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-500/10 ring-1 ring-gray-500/20">
                            <Search class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter & Pencarian</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Filter lokasi kantor berdasarkan kriteria</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="relative">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                @input="filterLocations"
                                type="text"
                                placeholder="Cari lokasi..."
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-3 pl-10 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            />
                        </div>
                        <select
                            v-model="selectedStatus"
                            @change="filterLocations"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                        <div class="flex gap-2">
                            <button
                                @click="filterLocations"
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-gray-500/5 to-slate-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
            </div>

            <!-- Map Overview -->
            <div
                class="group relative mb-10 overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Map class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Peta Lokasi Kantor</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Ringkasan semua lokasi kantor</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Map showing all locations -->
                    <LeafletMap
                        v-if="officeLocations?.data && officeLocations.data.length > 0"
                        :locations="officeLocations.data"
                        height="300px"
                        :show-radius="true"
                        :interactive="true"
                    />
                    <div
                        v-else
                        class="flex h-64 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800"
                    >
                        <div class="text-center">
                            <MapPin class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada lokasi kantor ditemukan</p>
                            <Link
                                href="/office-locations/create"
                                class="mt-2 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                <Plus class="h-4 w-4" />
                                Tambah Lokasi Pertama
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Office Locations List -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <MapPin class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Lokasi Kantor</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ officeLocations?.meta?.total || 0 }} lokasi ditemukan</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Lokasi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Koordinat</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Radius</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Statistik</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="location in officeLocations?.data || []"
                                :key="location.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                            <MapPin class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ location.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">ID: {{ location.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="max-w-xs">
                                        <p class="line-clamp-2 text-sm text-gray-600 dark:text-gray-300">
                                            {{ location.address }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        <p>{{ formatCoordinates(location.latitude, location.longitude) }}</p>
                                        <button
                                            @click="openInGoogleMaps(location)"
                                            class="mt-1 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            Buka di Maps
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    >
                                        {{ location.radius_meters }}m
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-sm">
                                            <Users class="h-3 w-3 text-gray-400" />
                                            <span class="text-gray-600 dark:text-gray-300"> {{ location.employees_count || 0 }} karyawan </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <Clock class="h-3 w-3 text-gray-400" />
                                            <span class="text-gray-600 dark:text-gray-300">
                                                {{ location.today_checkins_count || 0 }} absensi hari ini
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                                        :class="getStatusBadge(location.is_active)"
                                    >
                                        {{ location.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="viewOnMap(location)"
                                            class="rounded-lg bg-green-100 p-2 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50"
                                            title="Lihat di Peta"
                                        >
                                            <Map class="h-4 w-4" />
                                        </button>
                                        <Link
                                            :href="`/office-locations/${location.id}/edit?v=${Date.now()}`"
                                            :preserve-state="false"
                                            :preserve-scroll="false"
                                            class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="deleteLocation(location)"
                                            title="Hapus Lokasi"
                                            class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="!officeLocations?.data?.length" class="flex h-64 items-center justify-center bg-gray-50 dark:bg-gray-800/50">
                    <div class="text-center">
                        <MapPin class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                        <p class="mb-2 text-gray-500 dark:text-gray-400">Tidak ada lokasi kantor ditemukan</p>
                        <Link
                            href="/office-locations/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Plus class="h-4 w-4" />
                            Tambah Lokasi Pertama
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="officeLocations?.meta?.total > officeLocations?.meta?.per_page" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ officeLocations?.meta?.from || 0 }} sampai {{ officeLocations?.meta?.to || 0 }} dari
                            {{ officeLocations?.meta?.total || 0 }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in officeLocations?.links || []"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'rounded border px-3 py-1 text-sm',
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800',
                                ]"
                                :disabled="!link.url"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Map Modal -->
        <div
            v-if="showMapModal && selectedLocation"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click="closeMapModal"
            style="z-index: 9999 !important"
        >
            <div
                class="relative z-[10000] w-full max-w-4xl rounded-xl border-0 bg-white p-6 shadow-2xl dark:bg-gray-950"
                @click.stop
                style="z-index: 10000 !important"
            >
                <div class="mb-4 flex items-center justify-between">
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
                            Buka di Google Maps
                        </button>
                    </div>
                    <button
                        @click="closeMapModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            type="danger"
            title="Hapus Lokasi Kantor"
            :message="deleteMessage"
            confirm-text="Hapus"
            cancel-text="Batal"
            :processing="isDeleting"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </AppLayout>
</template>
