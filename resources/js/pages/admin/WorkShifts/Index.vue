<template>
    <Head title="Admin - Kelola Shift Kerja" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kelola Shift Kerja</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola jadwal shift dan penugasan karyawan</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            href="/admin/work-shifts/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Plus class="h-4 w-4" />
                            Tambah Shift
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Shifts -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <Clock class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Shift</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_shifts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Shifts -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Shift Aktif</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_shifts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inactive Shifts -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                                <XCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Shift Nonaktif</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.inactive_shifts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees Assigned -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <Users class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan Ter-assign</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.employees_assigned }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Input v-model="search" placeholder="Cari shift..." class="w-full" @input="debouncedSearch" />
                        </div>
                        <div>
                            <select
                                v-model="selectedStatus"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                                @change="applyFilters"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" @click="clearFilters" class="flex-1">
                                <FilterX class="mr-2 h-4 w-4" />
                                Reset
                            </Button>
                            <Link
                                href="/admin/work-shifts-employees"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                <Users class="h-4 w-4" />
                                Kelola Penugasan
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Shifts Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Clock class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Daftar Shift Kerja
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ workShifts.total }} shift ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left">
                                    <button
                                        @click="handleSort('name')"
                                        class="group inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        Nama Shift
                                        <span v-if="sortField === 'name'" class="ml-1">
                                            <ChevronUp v-if="sortDirection === 'asc'" class="h-4 w-4" />
                                            <ChevronDown v-else class="h-4 w-4" />
                                        </span>
                                        <span v-else class="ml-1 opacity-0 group-hover:opacity-100">
                                            <ChevronUp class="h-4 w-4" />
                                        </span>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button
                                        @click="handleSort('code')"
                                        class="group inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        Kode
                                        <span v-if="sortField === 'code'" class="ml-1">
                                            <ChevronUp v-if="sortDirection === 'asc'" class="h-4 w-4" />
                                            <ChevronDown v-else class="h-4 w-4" />
                                        </span>
                                        <span v-else class="ml-1 opacity-0 group-hover:opacity-100">
                                            <ChevronUp class="h-4 w-4" />
                                        </span>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button
                                        @click="handleSort('start_time')"
                                        class="group inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        Waktu
                                        <span v-if="sortField === 'start_time'" class="ml-1">
                                            <ChevronUp v-if="sortDirection === 'asc'" class="h-4 w-4" />
                                            <ChevronDown v-else class="h-4 w-4" />
                                        </span>
                                        <span v-else class="ml-1 opacity-0 group-hover:opacity-100">
                                            <ChevronUp class="h-4 w-4" />
                                        </span>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button
                                        @click="handleSort('work_hours')"
                                        class="group inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        Durasi Kerja
                                        <span v-if="sortField === 'work_hours'" class="ml-1">
                                            <ChevronUp v-if="sortDirection === 'asc'" class="h-4 w-4" />
                                            <ChevronDown v-else class="h-4 w-4" />
                                        </span>
                                        <span v-else class="ml-1 opacity-0 group-hover:opacity-100">
                                            <ChevronUp class="h-4 w-4" />
                                        </span>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Hari Kerja</th>
                                <th class="px-6 py-3 text-left">
                                    <button
                                        @click="handleSort('is_active')"
                                        class="group inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        Status
                                        <span v-if="sortField === 'is_active'" class="ml-1">
                                            <ChevronUp v-if="sortDirection === 'asc'" class="h-4 w-4" />
                                            <ChevronDown v-else class="h-4 w-4" />
                                        </span>
                                        <span v-else class="ml-1 opacity-0 group-hover:opacity-100">
                                            <ChevronUp class="h-4 w-4" />
                                        </span>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left">
                                    <button
                                        @click="handleSort('employee_shifts_count')"
                                        class="group inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        Karyawan
                                        <span v-if="sortField === 'employee_shifts_count'" class="ml-1">
                                            <ChevronUp v-if="sortDirection === 'asc'" class="h-4 w-4" />
                                            <ChevronDown v-else class="h-4 w-4" />
                                        </span>
                                        <span v-else class="ml-1 opacity-0 group-hover:opacity-100">
                                            <ChevronUp class="h-4 w-4" />
                                        </span>
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="shift in workShifts.data"
                                :key="shift.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ shift.name }}</p>
                                        <p v-if="shift.is_night_shift" class="text-xs text-amber-600 dark:text-amber-400">
                                            <Moon class="mr-1 inline h-3 w-3" />
                                            Shift Malam
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{
                                        shift.code
                                    }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}
                                        </p>
                                        <p class="text-gray-500 dark:text-gray-400">Istirahat: {{ shift.break_duration }} menit</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">{{ formatWorkHours(shift.work_hours) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="day in shift.workdays"
                                            :key="day"
                                            class="inline-flex h-6 w-6 items-center justify-center rounded bg-blue-100 text-xs font-medium text-blue-600 dark:bg-blue-900/20 dark:text-blue-400"
                                        >
                                            {{ getDayInitial(day) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': shift.is_active,
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !shift.is_active,
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ shift.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Users class="h-4 w-4 text-gray-400" />
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ shift.employee_shifts_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/work-shifts/${shift.id}`"
                                            class="rounded-lg bg-green-100 p-2 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="`/admin/work-shifts/${shift.id}/edit`"
                                            class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="deleteShift(shift)"
                                            class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="workShifts.total > 10" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ workShifts.from || 0 }} sampai {{ workShifts.to || 0 }} dari {{ workShifts.total }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in workShifts.links"
                                :key="link.label"
                                :disabled="!link.url"
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                                class="pagination-btn"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Hapus Shift"
            :message="`Yakin ingin menghapus shift '${selectedShift?.name}'? Aksi ini tidak dapat dibatalkan.`"
            confirm-text="Hapus"
            type="danger"
            @confirm="confirmDelete"
            @cancel="showDeleteModal = false"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { CheckCircle, ChevronDown, ChevronUp, Clock, Edit, Eye, FilterX, Moon, Plus, Trash, Users, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface WorkShift {
    id: number;
    name: string;
    code: string;
    start_time: string;
    end_time: string;
    work_hours: number;
    break_duration: number;
    overtime_multiplier: number;
    workdays: number[];
    is_night_shift: boolean;
    is_active: boolean;
    employee_shifts_count: number;
}

interface Stats {
    total_shifts: number;
    active_shifts: number;
    inactive_shifts: number;
    employees_assigned: number;
}

interface Props {
    workShifts: {
        data: WorkShift[];
        total: number;
        from: number;
        to: number;
        links: any[];
    };
    stats: Stats;
    filters: {
        search?: string;
        status?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Manajemen Shift',
        href: '/admin/work-shifts',
    },
];

// Filter states
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');

// Sorting states
const sortField = ref(props.filters.sort_field || 'name');
const sortDirection = ref(props.filters.sort_direction || 'asc');

// Modal states
const showDeleteModal = ref(false);
const selectedShift = ref<WorkShift | null>(null);

// Toast
const { toast } = useToast();

// Methods
const formatTime = (time: string) => {
    return time.substring(0, 5); // HH:MM format
};

const formatWorkHours = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}j ${mins}m`;
};

const getDayInitial = (dayNumber: number) => {
    const days = ['M', 'S', 'S', 'R', 'K', 'J', 'S']; // Minggu, Senin, Selasa, dst
    return days[dayNumber];
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    router.get(
        '/admin/work-shifts',
        {
            search: search.value,
            status: selectedStatus.value,
            sort_field: sortField.value,
            sort_direction: sortDirection.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    sortField.value = 'name';
    sortDirection.value = 'asc';
    router.get('/admin/work-shifts');
};

const handleSort = (field: string) => {
    if (sortField.value === field) {
        // Toggle direction if same field
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // Set new field and default to asc
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    
    applyFilters();
};

const deleteShift = (shift: WorkShift) => {
    if (shift.employee_shifts_count > 0) {
        toast({
            title: 'Tidak dapat menghapus!',
            description: 'Tidak dapat menghapus shift yang memiliki penugasan aktif.',
            variant: 'destructive',
        });
        return;
    }

    selectedShift.value = shift;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!selectedShift.value) return;

    router.delete(`/admin/work-shifts/${selectedShift.value.id}`, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            toast({
                title: 'Berhasil!',
                description: `Shift "${selectedShift.value?.name}" berhasil dihapus.`,
                variant: 'success',
            });
            showDeleteModal.value = false;
            selectedShift.value = null;
        },
        onError: () => {
            toast({
                title: 'Gagal!',
                description: 'Terjadi kesalahan saat menghapus shift.',
                variant: 'destructive',
            });
        },
    });
};
</script>

<style>
.pagination-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
