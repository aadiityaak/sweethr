<script setup lang="ts">
import LeafletMap from '@/components/LeafletMap.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { MapPin, Navigation, Save, Target } from 'lucide-vue-next';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

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
}

interface Props {
    officeLocation: OfficeLocation;
}

const { officeLocation } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Lokasi Kantor',
        href: '/office-locations',
    },
    {
        title: 'Edit Lokasi',
        href: `/office-locations/${officeLocation.id}/edit`,
    },
];

const form = useForm({
    name: officeLocation.name,
    address: officeLocation.address,
    latitude: officeLocation.latitude,
    longitude: officeLocation.longitude,
    radius_meters: officeLocation.radius_meters,
    is_active: officeLocation.is_active,
});

const selectedLocation = ref<{ latitude: number; longitude: number } | null>(null);
const currentLocation = ref<{ latitude: number; longitude: number } | null>(null);

const mapLocations = computed(() => {
    if (!form.latitude || !form.longitude) return [];

    console.log('mapLocations computed:', { lat: form.latitude, lng: form.longitude });
    return [
        {
            id: officeLocation.id,
            name: form.name || 'Location',
            address: form.address || '',
            latitude: Number(form.latitude),
            longitude: Number(form.longitude),
            radius_meters: form.radius_meters,
            is_active: form.is_active,
        },
    ];
});

const getCurrentLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const coords = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                };
                currentLocation.value = coords;
                form.latitude = coords.latitude;
                form.longitude = coords.longitude;
                selectedLocation.value = coords;
            },
            (error) => {
                console.error('Error getting location:', error);
                alert('Unable to get current location. Please set coordinates manually.');
            },
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
};

const onMapClick = async (coordinates: { latitude: number; longitude: number }) => {
    console.log('Map clicked, new coordinates:', coordinates);
    // Limit to 8 decimal places for practical use
    const lat = Number(coordinates.latitude.toFixed(8));
    const lng = Number(coordinates.longitude.toFixed(8));

    form.latitude = lat;
    form.longitude = lng;

    await nextTick();

    selectedLocation.value = {
        latitude: lat,
        longitude: lng,
    };

    console.log('Updated form coordinates:', { lat: form.latitude, lng: form.longitude });
    console.log('Updated selectedLocation:', selectedLocation.value);
};

const submit = () => {
    form.put(`/office-locations/${officeLocation.id}`, {
        onSuccess: () => {
            // Will redirect to index page
        },
    });
};

// Watch for form coordinate changes and update selected location
watch(
    [() => form.latitude, () => form.longitude],
    ([lat, lng]) => {
        if (lat && lng) {
            selectedLocation.value = {
                latitude: Number(lat),
                longitude: Number(lng),
            };
        }
    },
    { immediate: true },
);

onMounted(() => {
    // Set initial coordinates from office location
    selectedLocation.value = {
        latitude: officeLocation.latitude,
        longitude: officeLocation.longitude,
    };
});
</script>

<template>
    <Head :title="`Edit Lokasi Kantor - ${officeLocation.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Lokasi Kantor</h1>
                    <p class="text-gray-600 dark:text-gray-300">Perbarui detail lokasi kantor dan zona absensi</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Form Section -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Location Name -->
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Nama Lokasi * </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="mis., Kantor Pusat Jakarta"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Alamat * </label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                required
                                rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="Masukkan alamat lengkap lokasi kantor"
                            ></textarea>
                            <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</div>
                        </div>

                        <!-- Coordinates -->
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="latitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Latitude * </label>
                                <input
                                    id="latitude"
                                    v-model="form.latitude"
                                    type="number"
                                    step="0.00000001"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    placeholder="-6.2088"
                                />
                                <div v-if="form.errors.latitude" class="mt-1 text-sm text-red-600">{{ form.errors.latitude }}</div>
                            </div>
                            <div>
                                <label for="longitude" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Longitude * </label>
                                <input
                                    id="longitude"
                                    v-model="form.longitude"
                                    type="number"
                                    step="0.00000001"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    placeholder="106.8456"
                                />
                                <div v-if="form.errors.longitude" class="mt-1 text-sm text-red-600">{{ form.errors.longitude }}</div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="getCurrentLocation"
                                class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                            >
                                <Navigation class="h-4 w-4" />
                                Gunakan Lokasi Saat Ini
                            </button>
                        </div>

                        <!-- Check-in Radius -->
                        <div>
                            <label for="radius_meters" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Radius Absensi (meter) *
                            </label>
                            <input
                                id="radius_meters"
                                v-model="form.radius_meters"
                                type="number"
                                min="10"
                                max="1000"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Karyawan harus berada dalam radius ini untuk absensi (10-1000 meter)
                            </p>
                            <div v-if="form.errors.radius_meters" class="mt-1 text-sm text-red-600">{{ form.errors.radius_meters }}</div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="flex items-center gap-2">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi Aktif</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hanya lokasi aktif yang dapat digunakan untuk absensi</p>
                            <div v-if="form.errors.is_active" class="mt-1 text-sm text-red-600">{{ form.errors.is_active }}</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-4 pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                <Save class="h-4 w-4" />
                                {{ form.processing ? 'Memperbarui...' : 'Perbarui Lokasi' }}
                            </button>
                            <a
                                href="/office-locations"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Map Section -->
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pratinjau Lokasi</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Klik di peta untuk memperbarui koordinat</p>
                    </div>

                    <!-- Interactive Leaflet Map -->
                    <LeafletMap
                        v-if="form.latitude && form.longitude"
                        :locations="mapLocations"
                        :selected-location="{ latitude: Number(form.latitude), longitude: Number(form.longitude) }"
                        height="400px"
                        :show-radius="true"
                        :interactive="true"
                        @map-click="onMapClick"
                    />

                    <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <div class="flex items-center gap-2 text-sm">
                            <MapPin class="h-4 w-4 text-blue-600" />
                            <span class="font-medium text-gray-700 dark:text-gray-300">Koordinat Saat Ini:</span>
                        </div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{
                                form.latitude && form.longitude
                                    ? `${Number(form.latitude).toFixed(8)}, ${Number(form.longitude).toFixed(8)}`
                                    : 'Koordinat belum diatur'
                            }}
                        </div>
                        <div class="mt-2 flex items-center gap-2 text-sm">
                            <Target class="h-4 w-4 text-green-600" />
                            <span class="font-medium text-gray-700 dark:text-gray-300">Radius Absensi:</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ form.radius_meters }}m</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
