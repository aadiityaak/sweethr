<template>
    <Head title="Admin - Kelola Departemen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kelola Departemen</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola struktur organisasi dan departemen perusahaan</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            href="/admin/departments/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Plus class="h-4 w-4" />
                            Tambah Departemen
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Departments -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <Building class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Departemen</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_departments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Departments -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Departemen Aktif</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_departments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inactive Departments -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                                <XCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Departemen Nonaktif</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.inactive_departments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- With Managers -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <Users class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Dengan Manajer</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.departments_with_managers }}</p>
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
                            <Input v-model="search" placeholder="Cari departemen..." class="w-full" @input="debouncedSearch" />
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
                        <div>
                            <Button variant="outline" @click="clearFilters" class="w-full">
                                <FilterX class="mr-2 h-4 w-4" />
                                Reset Filter
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departments Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Building class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Daftar Departemen
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ departments.total }} departemen ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('name')"
                                        class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Departemen
                                        <component :is="getSortIcon('name')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('code')"
                                        class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Kode
                                        <component :is="getSortIcon('code')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('manager')"
                                        class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Manajer
                                        <component :is="getSortIcon('manager')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('employees_count')"
                                        class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Jumlah Karyawan
                                        <component :is="getSortIcon('employees_count')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('is_active')"
                                        class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-300"
                                    >
                                        Status
                                        <component :is="getSortIcon('is_active')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="department in departments.data"
                                :key="department.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{
                                                department.code.substring(0, 2)
                                            }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ department.name }}</p>
                                            <p v-if="department.description" class="line-clamp-1 text-sm text-gray-500 dark:text-gray-400">
                                                {{ department.description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{
                                        department.code
                                    }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="department.manager">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ department.manager.name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ department.manager.employee_id }}</p>
                                    </div>
                                    <span v-else class="text-sm text-gray-500 dark:text-gray-400">Belum ada manajer</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Users class="h-4 w-4 text-gray-400" />
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ department.employees_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': department.is_active,
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !department.is_active,
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ department.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/departments/${department.id}`"
                                            class="rounded-lg bg-green-100 p-2 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="`/admin/departments/${department.id}/edit?v=${Date.now()}`"
                                            :preserve-state="false"
                                            :preserve-scroll="false"
                                            class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="deleteDepartment(department)"
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
                <div v-if="departments.total > 10" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ departments.from || 0 }} sampai {{ departments.to || 0 }} dari {{ departments.total }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in departments.links"
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
            title="Hapus Departemen"
            :message="`Yakin ingin menghapus departemen '${selectedDepartment?.name}'? Aksi ini tidak dapat dibatalkan.`"
            confirm-text="Hapus"
            type="danger"
            @confirm="confirmDelete"
            @cancel="showDeleteModal = false; selectedDepartment = null;"
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
import { ArrowUpDown, Building, CheckCircle, ChevronDown, ChevronUp, Edit, Eye, FilterX, Plus, Trash, Users, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Department {
    id: number;
    name: string;
    code: string;
    description?: string;
    is_active: boolean;
    employees_count: number;
    manager?: {
        id: number;
        name: string;
        employee_id: string;
    };
}

interface Stats {
    total_departments: number;
    active_departments: number;
    inactive_departments: number;
    departments_with_managers: number;
}

interface Props {
    departments: {
        data: Department[];
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

type SortField = 'name' | 'code' | 'manager' | 'employees_count' | 'is_active';

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Departemen',
        href: '/admin/departments',
    },
];

// Filter states
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');

// Modal states
const showDeleteModal = ref(false);
const selectedDepartment = ref<Department | null>(null);

// Toast
const { toast } = useToast();

// Methods
const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    router.get(
        '/admin/departments',
        {
            search: search.value,
            status: selectedStatus.value,
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
    router.get('/admin/departments');
};

const deleteDepartment = (department: Department) => {
    if (department.employees_count > 0) {
        toast({
            title: 'Tidak dapat menghapus!',
            description: 'Tidak dapat menghapus departemen yang memiliki karyawan aktif.',
            variant: 'destructive',
        });
        return;
    }

    selectedDepartment.value = department;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!selectedDepartment.value) return;

    router.delete(`/admin/departments/${selectedDepartment.value.id}`, {
        onSuccess: () => {
            toast({
                title: 'Berhasil!',
                description: `Departemen "${selectedDepartment.value?.name}" berhasil dihapus.`,
                variant: 'success',
            });
        },
        onError: (errors) => {
            console.error('Delete department error:', errors);
            const errorMessage = errors.message || 'Terjadi kesalahan saat menghapus departemen.';
            toast({
                title: 'Gagal!',
                description: errorMessage,
                variant: 'destructive',
            });
        },
        onFinish: () => {
            showDeleteModal.value = false;
            selectedDepartment.value = null;
        }
    });
};

// Sorting
const currentSort = computed(() => props.filters.sort || 'created_at');
const currentDirection = computed(() => props.filters.direction || 'desc');

const toggleSort = (field: SortField) => {
    let newDirection: 'asc' | 'desc' | undefined = 'asc';

    if (currentSort.value === field) {
        if (currentDirection.value === 'asc') {
            newDirection = 'desc';
        } else {
            newDirection = undefined;
        }
    }

    router.get(
        '/admin/departments',
        {
            search: search.value || undefined,
            status: selectedStatus.value || undefined,
            sort: newDirection ? field : undefined,
            direction: newDirection || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const getSortIcon = (field: SortField) => {
    if (currentSort.value !== field) {
        return ArrowUpDown;
    }
    return currentDirection.value === 'asc' ? ChevronUp : ChevronDown;
};
</script>

<style>
.pagination-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}

.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}
</style>
