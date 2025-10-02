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
            <div class="mb-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Shifts -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Shift</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Semua shift terdaftar
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ stats.total_shifts }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Aktif</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ stats.active_shifts }}</span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Active Shifts -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Shift Aktif</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Shift yang sedang aktif
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Aktif</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ stats.active_shifts }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total_shifts ? Math.round((stats.active_shifts / stats.total_shifts) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Inactive Shifts -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                                <XCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Shift Nonaktif</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Shift yang tidak aktif
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-amber-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-amber-50/50 px-3 py-2 dark:bg-amber-950/30">
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Nonaktif</span>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ stats.inactive_shifts }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total_shifts ? Math.round((stats.inactive_shifts / stats.total_shifts) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Employees Assigned -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/10 ring-1 ring-purple-500/20">
                                <Users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Karyawan Ter-assign</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Karyawan yang di-assign ke shift
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-purple-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-purple-50/50 px-3 py-2 dark:bg-purple-950/30">
                            <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Ter-assign</span>
                            <span class="text-sm font-semibold text-purple-800 dark:text-purple-300">{{ stats.employees_assigned }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata/Shift</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.active_shifts ? Math.round(stats.employees_assigned / stats.active_shifts) : 0 }}
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"
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
                            <FilterX class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter & Pencarian</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Filter shift berdasarkan kriteria</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <Input v-model="search" placeholder="Cari shift..." class="w-full" @input="debouncedSearch" />
                        </div>
                        <div>
                            <select
                                v-model="selectedStatus"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-gray-500/5 to-slate-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
            </div>

            <!-- Work Shifts Table -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Clock class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Shift Kerja</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ workShifts.total }} shift ditemukan</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('name')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'name' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Nama Shift
                                        <component :is="getSortIcon('name')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('code')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'code' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Kode
                                        <component :is="getSortIcon('code')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('start_time')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'start_time' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Waktu
                                        <component :is="getSortIcon('start_time')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('work_hours')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'work_hours' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Durasi Kerja
                                        <component :is="getSortIcon('work_hours')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Hari Kerja</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('is_active')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'is_active' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Status
                                        <component :is="getSortIcon('is_active')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('employee_shifts_count')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'employee_shifts_count' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Karyawan
                                        <component :is="getSortIcon('employee_shifts_count')" class="h-4 w-4" />
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
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
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
import { ArrowDown, ArrowUp, ArrowUpDown, CheckCircle, Clock, Edit, Eye, FilterX, Moon, Plus, Trash, Users, XCircle } from 'lucide-vue-next';
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
        sort?: string;
        direction?: string;
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
const sortField = ref(props.filters.sort || 'name');
const sortDirection = ref(props.filters.direction || 'asc');

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
    router.visit('/admin/work-shifts', {
        method: 'get',
        data: {
            search: search.value,
            status: selectedStatus.value,
            sort: sortField.value,
            direction: sortDirection.value,
        },
        preserveState: true,
        replace: true,
    });
}, 300);

const applyFilters = () => {
    router.visit('/admin/work-shifts', {
        method: 'get',
        data: {
            search: search.value,
            status: selectedStatus.value,
            sort: sortField.value,
            direction: sortDirection.value,
        },
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    sortField.value = 'name';
    sortDirection.value = 'asc';
    router.visit('/admin/work-shifts', {
        method: 'get',
        data: {},
        preserveState: true,
        replace: true,
    });
};

// Sorting function
const sortBy = (field: string) => {
    if (sortField.value === field) {
        // Toggle direction if same field
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // Set new field and default to ascending
        sortField.value = field;
        sortDirection.value = 'asc';
    }

    // Use router.visit instead of router.get to avoid page reload
    router.visit('/admin/work-shifts', {
        method: 'get',
        data: {
            search: search.value,
            status: selectedStatus.value,
            sort: sortField.value,
            direction: sortDirection.value,
        },
        preserveState: true,
        replace: true,
    });
};

// Get sort icon for column header
const getSortIcon = (field: string) => {
    if (sortField.value !== field) {
        return ArrowUpDown;
    }
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
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
