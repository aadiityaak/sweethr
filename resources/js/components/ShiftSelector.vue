<script setup lang="ts">
import { useToast } from '@/components/ui/toast/use-toast';
import { Clock } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

interface WorkShift {
    id: number;
    name: string;
    code: string;
    start_time: string;
    end_time: string;
    work_hours: number;
    break_duration: number;
    workdays: number[];
    is_night_shift: boolean;
    assignment_type: string;
    effective_date: string;
    end_date?: string;
}

interface Props {
    userShifts: WorkShift[];
    userId: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    shiftChanged: [shift: WorkShift | null];
}>();

const { toast } = useToast();

// Local storage key for selected shift
const storageKey = computed(() => `selected_shift_id_${props.userId}`);

// Selected shift state
const selectedShiftId = ref<string>('');
const selectedShift = ref<WorkShift | null>(null);

// Format time for display
const formatTime = (time: string) => {
    return time.substring(0, 5); // HH:MM format
};


// Save selected shift to local storage
const saveSelectedShift = (shiftId: string) => {
    try {
        // Check if localStorage is available
        if (typeof localStorage === 'undefined') {
            console.warn('localStorage is not available, shift selection will not persist');
            return;
        }
        
        localStorage.setItem(storageKey.value, shiftId);
        console.log('ShiftSelector - Saved shift to local storage. Key:', storageKey.value, 'ID:', shiftId);
    } catch (error) {
        console.error('Failed to save selected shift to local storage:', error);
        toast({
            title: '⚠️ Gagal Menyimpan Pilihan',
            description: 'Pilihan shift Anda tidak dapat disimpan. Silakan pilih shift lagi saat refresh halaman.',
            variant: 'warning',
            duration: 3000,
        });
    }
};

// Load selected shift from local storage
const loadSelectedShift = () => {
    try {
        // Check if localStorage is available
        if (typeof localStorage === 'undefined') {
            console.warn('localStorage is not available');
            if (props.userShifts.length > 0) {
                const firstShift = props.userShifts[0];
                selectedShiftId.value = firstShift.id.toString();
                selectedShift.value = firstShift;
            }
            return;
        }

        const savedShiftId = localStorage.getItem(storageKey.value);
        console.log('Loading shift from local storage. Key:', storageKey.value, 'Saved ID:', savedShiftId);
        
        if (savedShiftId) {
            // Validate that the saved shift still exists and is active
            const shift = props.userShifts.find(s => s.id.toString() === savedShiftId);
            console.log('Found shift in props:', shift);
            
            if (shift) {
                selectedShiftId.value = savedShiftId;
                selectedShift.value = shift;
                console.log('Successfully loaded selected shift from local storage:', shift);
                return;
            } else {
                // Saved shift is no longer valid, clear it
                localStorage.removeItem(storageKey.value);
                console.log('Saved shift no longer exists, clearing from local storage');
                toast({
                    title: '⚠️ Perubahan Shift',
                    description: 'Shift yang Anda pilih sebelumnya tidak lagi tersedia. Silakan pilih shift baru.',
                    variant: 'warning',
                    duration: 5000,
                });
            }
        }
        
        // If no valid saved shift, select the first available shift
        if (props.userShifts.length > 0) {
            const firstShift = props.userShifts[0];
            selectedShiftId.value = firstShift.id.toString();
            selectedShift.value = firstShift;
            saveSelectedShift(firstShift.id.toString());
            console.log('No saved shift found, selected first shift:', firstShift);
        }
    } catch (error) {
        console.error('Failed to load selected shift from local storage:', error);
        // Fallback to first shift
        if (props.userShifts.length > 0) {
            const firstShift = props.userShifts[0];
            selectedShiftId.value = firstShift.id.toString();
            selectedShift.value = firstShift;
        }
        
        toast({
            title: '❌ Gagal Memuat Shift',
            description: 'Terjadi kesalahan saat memuat data shift. Silakan refresh halaman atau coba lagi nanti.',
            variant: 'destructive',
            duration: 5000,
        });
    }
};

// Handle shift selection change
const handleShiftChange = (shiftId: string) => {
    if (!shiftId) {
        toast({
            title: '⚠️ Pilihan Tidak Valid',
            description: 'Silakan pilih shift yang tersedia.',
            variant: 'warning',
            duration: 3000,
        });
        return;
    }
    
    const shift = props.userShifts.find(s => s.id.toString() === shiftId);
    console.log('ShiftSelector - handleShiftChange called with ID:', shiftId, 'Found shift:', shift);
    
    if (shift) {
        selectedShiftId.value = shiftId;
        selectedShift.value = shift;
        saveSelectedShift(shiftId);
        
        toast({
            title: '✅ Shift Berhasil Dipilih',
            description: `Anda telah memilih shift ${shift.name} (${formatTime(shift.start_time)} - ${formatTime(shift.end_time)})`,
            variant: 'success',
            duration: 3000,
        });
        
        emit('shiftChanged', shift);
    } else {
        toast({
            title: '❌ Shift Tidak Ditemukan',
            description: 'Shift yang Anda pilih tidak tersedia. Silakan pilih shift lain.',
            variant: 'destructive',
            duration: 3000,
        });
    }
};

// Watch for changes in user shifts (e.g., when admin assigns new shifts)
watch(() => props.userShifts, (newShifts) => {
    if (newShifts.length === 0) {
        selectedShiftId.value = '';
        selectedShift.value = null;
        try {
            localStorage.removeItem(storageKey.value);
        } catch (error) {
            console.error('Failed to clear selected shift from local storage:', error);
        }
        return;
    }
    
    // Check if current selected shift is still valid
    const currentShiftStillValid = newShifts.find(s => s.id.toString() === selectedShiftId.value);
    if (!currentShiftStillValid) {
        // Current shift is no longer valid, select first available
        const firstShift = newShifts[0];
        selectedShiftId.value = firstShift.id.toString();
        selectedShift.value = firstShift;
        saveSelectedShift(firstShift.id.toString());
        emit('shiftChanged', firstShift);
    }
}, { immediate: true });

// Initialize on component mount
onMounted(() => {
    console.log('ShiftSelector - Component mounted');
    // Add a small delay to ensure props are fully loaded
    setTimeout(() => {
        console.log('ShiftSelector - Loading selected shift after mount');
        loadSelectedShift();
        if (selectedShift.value) {
            console.log('ShiftSelector - Emitting shiftChanged after mount:', selectedShift.value);
            emit('shiftChanged', selectedShift.value);
        }
    }, 100);
});

// Watch for selectedShift changes and emit to parent
watch(selectedShift, (newShift) => {
    console.log('ShiftSelector - selectedShift changed:', newShift);
    if (newShift) {
        emit('shiftChanged', newShift);
    }
}, { immediate: false });

// Watch for userShifts changes and reload selected shift if needed
watch(() => props.userShifts, (newShifts) => {
    console.log('ShiftSelector - userShifts changed:', newShifts);
    if (newShifts && newShifts.length > 0) {
        // Always load selected shift when userShifts changes
        setTimeout(() => {
            console.log('ShiftSelector - Loading selected shift after userShifts change');
            loadSelectedShift();
        }, 50);
    }
}, { immediate: true });
</script>

<template>
    <div class="rounded-lg border bg-card p-4">
        <div class="mb-3 flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                <Clock class="h-4 w-4 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <h3 class="text-sm font-semibold text-foreground">Pilih Shift Kerja</h3>
                <p class="text-xs text-muted-foreground">Pilih shift untuk hari ini</p>
            </div>
        </div>

        <!-- Shift Selector -->
        <div v-if="userShifts.length > 0" class="space-y-3">
            <select
                :value="selectedShiftId"
                @change="handleShiftChange(($event.target as HTMLSelectElement).value)"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
                <option value="" disabled>Pilih shift...</option>
                <option
                    v-for="shift in userShifts"
                    :key="shift.id"
                    :value="shift.id.toString()"
                >
                    {{ shift.name }} ({{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }})
                </option>
            </select>
        </div>

        <!-- No Shifts Available -->
        <div v-else class="text-center py-4">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/20">
                <Clock class="h-6 w-6 text-amber-600 dark:text-amber-400" />
            </div>
            <h4 class="mt-2 text-sm font-medium text-foreground">Belum Ada Shift</h4>
            <p class="mt-1 text-xs text-muted-foreground">
                Anda belum ditugaskan ke shift kerja mana pun. Hubungi admin untuk penugasan shift.
            </p>
        </div>
    </div>
</template>