<template>
    <Head title="Admin - Kelola Shift Kerja" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Shift Kerja</h1>
                    <p class="text-muted-foreground">Kelola jadwal shift dan penugasan karyawan</p>
                </div>
                <Link
                    href="/admin/work-shifts/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Shift
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                            <Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Shift</p>
                            <p class="text-2xl font-bold">{{ stats.total_shifts }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Shift Aktif</p>
                            <p class="text-2xl font-bold">{{ stats.active_shifts }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Shift Nonaktif</p>
                            <p class="text-2xl font-bold">{{ stats.inactive_shifts }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                            <Users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Karyawan Ter-assign</p>
                            <p class="text-2xl font-bold">{{ stats.employees_assigned }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card p-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <Input
                            v-model="search"
                            placeholder="Cari shift..."
                            class="w-full"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <select
                            v-model="selectedStatus"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" @click="clearFilters" class="w-full">
                            <FilterX class="mr-2 h-4 w-4" />
                            Reset
                        </Button>
                        <Link
                            href="/admin/work-shifts-employees"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-secondary px-3 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                        >
                            <Users class="h-4 w-4" />
                            Kelola Penugasan
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Work Shifts Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Daftar Shift Kerja</h3>
                    <p class="text-sm text-muted-foreground">{{ workShifts.total }} shift ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="text-left">
                                <th class="p-4 font-semibold">Nama Shift</th>
                                <th class="p-4 font-semibold">Kode</th>
                                <th class="p-4 font-semibold">Waktu</th>
                                <th class="p-4 font-semibold">Durasi Kerja</th>
                                <th class="p-4 font-semibold">Hari Kerja</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Karyawan</th>
                                <th class="p-4 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="shift in workShifts.data" :key="shift.id" class="border-b hover:bg-muted/50">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-medium text-primary-foreground">
                                            {{ shift.code }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ shift.name }}</p>
                                            <p v-if="shift.is_night_shift" class="text-xs text-amber-600">
                                                <Moon class="inline h-3 w-3 mr-1" />
                                                Shift Malam
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <code class="rounded bg-muted px-2 py-1 text-xs">{{ shift.code }}</code>
                                </td>
                                <td class="p-4">
                                    <div class="text-sm">
                                        <p class="font-medium">{{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}</p>
                                        <p class="text-muted-foreground">Istirahat: {{ shift.break_duration }} menit</p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-medium">{{ formatWorkHours(shift.work_hours) }}</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="day in shift.workdays"
                                            :key="day"
                                            class="inline-flex h-6 w-6 items-center justify-center rounded bg-primary/10 text-xs font-medium text-primary"
                                        >
                                            {{ getDayInitial(day) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': shift.is_active,
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !shift.is_active
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ shift.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Users class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm font-medium">{{ shift.employee_shifts_count }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/work-shifts/${shift.id}`"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="`/admin/work-shifts/${shift.id}/edit`"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            @click="deleteShift(shift)"
                                            class="h-8 w-8 p-0 text-red-600 hover:bg-red-50"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between p-4 border-t">
                    <div class="text-sm text-muted-foreground">
                        Menampilkan {{ workShifts.from || 0 }}-{{ workShifts.to || 0 }} dari {{ workShifts.total }} hasil
                    </div>
                    <div class="flex items-center gap-2">
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
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Clock,
    CheckCircle,
    XCircle,
    Users,
    Plus,
    FilterX,
    Eye,
    Edit,
    Trash,
    Moon,
} from 'lucide-vue-next';
import { debounce } from 'lodash';

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
    router.get('/admin/work-shifts', {
        search: search.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    router.get('/admin/work-shifts');
};

const deleteShift = (shift: WorkShift) => {
    if (shift.employee_shifts_count > 0) {
        alert('Tidak dapat menghapus shift yang memiliki penugasan aktif.');
        return;
    }

    if (confirm(`Hapus shift "${shift.name}"?`)) {
        router.delete(`/admin/work-shifts/${shift.id}`);
    }
};
</script>

<style>
.pagination-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>