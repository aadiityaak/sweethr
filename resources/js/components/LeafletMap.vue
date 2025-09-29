<script setup lang="ts">
import type { Circle, CircleMarker, LatLngExpression, Map as LeafletMap } from 'leaflet';
import { onMounted, ref, watch } from 'vue';

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
    is_active: boolean;
}

interface Props {
    locations: OfficeLocation[];
    selectedLocation?:
        | OfficeLocation
        | {
              latitude: number;
              longitude: number;
          };
    userLocation?: {
        latitude: number;
        longitude: number;
    };
    height?: string;
    showRadius?: boolean;
    interactive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    height: '400px',
    showRadius: true,
    interactive: true,
});

const emit = defineEmits<{
    'map-click': [coordinates: { latitude: number; longitude: number }];
}>();

const mapContainer = ref<HTMLDivElement>();
let map: LeafletMap | null = null;
let markers: CircleMarker[] = [];
let radiusCircles: Circle[] = [];
let userMarker: CircleMarker | null = null;

const initializeMap = async () => {
    if (!mapContainer.value) return;

    // Dynamic import of Leaflet to avoid SSR issues
    const L = await import('leaflet');

    // Fix for default marker icons in Leaflet
    delete (L.Icon.Default.prototype as any)._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    });

    // Center map on Indonesia (Jakarta) by default
    const defaultCenter: LatLngExpression = [-6.2088, 106.8456];
    const center = props.selectedLocation
        ? ([props.selectedLocation.latitude, props.selectedLocation.longitude] as LatLngExpression)
        : props.locations.length > 0
          ? ([props.locations[0].latitude, props.locations[0].longitude] as LatLngExpression)
          : defaultCenter;

    map = L.map(mapContainer.value, {
        zoomControl: props.interactive,
        dragging: props.interactive,
        touchZoom: props.interactive,
        scrollWheelZoom: props.interactive,
        doubleClickZoom: props.interactive,
        boxZoom: props.interactive,
        keyboard: props.interactive,
    }).setView(center, props.selectedLocation ? 16 : 10);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    // Add map click event listener if interactive
    if (props.interactive) {
        map.on('click', (e: any) => {
            emit('map-click', {
                latitude: e.latlng.lat,
                longitude: e.latlng.lng,
            });
        });
    }

    // Add office location markers
    addOfficeMarkers(L);

    // Add user location marker if provided
    if (props.userLocation) {
        addUserMarker(L);
    }

    // Fit bounds to show all markers if multiple locations
    if (props.locations.length > 1 && !props.selectedLocation) {
        const group = new L.FeatureGroup([...markers, ...radiusCircles]);
        map.fitBounds(group.getBounds().pad(0.1));
    }
};

const addOfficeMarkers = (L: any) => {
    if (!map) return;

    // Clear existing markers
    markers.forEach((marker) => map?.removeLayer(marker));
    radiusCircles.forEach((circle) => map?.removeLayer(circle));
    markers = [];
    radiusCircles = [];

    props.locations.forEach((location) => {
        if (!map) return;

        // Create office marker
        const marker = L.circleMarker([location.latitude, location.longitude], {
            radius: 10,
            fillColor: location.is_active ? '#22c55e' : '#ef4444',
            color: location.is_active ? '#16a34a' : '#dc2626',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8,
        });

        // Add popup with office info
        marker.bindPopup(`
            <div class="p-2">
                <h3 class="font-semibold text-gray-900">${location.name}</h3>
                <p class="text-sm text-gray-600 mt-1">${location.address}</p>
                <p class="text-xs text-gray-500 mt-1">Check-in radius: ${location.radius_meters}m</p>
                <p class="text-xs mt-1">
                    <span class="inline-flex px-2 py-1 text-xs rounded-full ${location.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${location.is_active ? 'Active' : 'Inactive'}
                    </span>
                </p>
            </div>
        `);

        marker.addTo(map);
        markers.push(marker);

        // Add radius circle if enabled
        if (props.showRadius) {
            const radiusCircle = L.circle([location.latitude, location.longitude], {
                radius: location.radius_meters,
                color: location.is_active ? '#22c55e' : '#ef4444',
                weight: 2,
                opacity: 0.6,
                fillOpacity: 0.1,
            });

            radiusCircle.addTo(map);
            radiusCircles.push(radiusCircle);
        }
    });
};

const addUserMarker = (L: any) => {
    if (!map || !props.userLocation) return;

    // Remove existing user marker
    if (userMarker) {
        map.removeLayer(userMarker);
    }

    // Create user location marker
    userMarker = L.circleMarker([props.userLocation.latitude, props.userLocation.longitude], {
        radius: 8,
        fillColor: '#3b82f6',
        color: '#1d4ed8',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.8,
    });

    userMarker.bindPopup(`
        <div class="p-2">
            <h3 class="font-semibold text-blue-900">Your Location</h3>
            <p class="text-xs text-blue-600 mt-1">
                ${props.userLocation.latitude.toFixed(6)}, ${props.userLocation.longitude.toFixed(6)}
            </p>
        </div>
    `);

    userMarker.addTo(map);
};

onMounted(() => {
    initializeMap();
});

watch(
    () => props.locations,
    async () => {
        if (map) {
            const L = await import('leaflet');
            addOfficeMarkers(L);
        }
    },
    { deep: true },
);

watch(
    () => props.userLocation,
    async () => {
        if (map) {
            const L = await import('leaflet');
            addUserMarker(L);
        }
    },
    { deep: true },
);

watch(
    () => props.selectedLocation,
    async (newLocation) => {
        console.log('selectedLocation changed:', newLocation);
        if (map && newLocation) {
            const L = await import('leaflet');
            // Update map center and zoom to selected location
            map.setView([newLocation.latitude, newLocation.longitude], 16);
            // Re-add markers to update the display
            addOfficeMarkers(L);
        }
    },
    { deep: true },
);

// Cleanup on unmount
onMounted(() => {
    return () => {
        if (map) {
            map.remove();
            map = null;
        }
    };
});
</script>

<template>
    <div ref="mapContainer" class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700" :style="{ height: height }"></div>
</template>
