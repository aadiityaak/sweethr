<template>
    <Head title="Admin - Kelola Departemen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Departemen</h1>
                    <p class="text-muted-foreground">Kelola struktur organisasi dan departemen perusahaan</p>
                </div>
                <Link
                    href="/admin/departments/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Departemen
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                            <Building class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Departemen</p>
                            <p class="text-2xl font-bold">{{ stats.total_departments }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Departemen Aktif</p>
                            <p class="text-2xl font-bold">{{ stats.active_departments }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Departemen Nonaktif</p>
                            <p class="text-2xl font-bold">{{ stats.inactive_departments }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                            <Users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Dengan Manajer</p>
                            <p class="text-2xl font-bold">{{ stats.departments_with_managers }}</p>
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
                            placeholder="Cari departemen..."
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
                    <div>
                        <Button variant="outline" @click="clearFilters" class="w-full">
                            <FilterX class="mr-2 h-4 w-4" />
                            Reset Filter
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Departments Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Daftar Departemen</h3>
                    <p class="text-sm text-muted-foreground">{{ departments.total }} departemen ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="text-left">
                                <th class="p-4 font-semibold">Departemen</th>
                                <th class="p-4 font-semibold">Kode</th>
                                <th class="p-4 font-semibold">Manajer</th>
                                <th class="p-4 font-semibold">Jumlah Karyawan</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="department in departments.data" :key="department.id" class="border-b hover:bg-muted/50">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-medium text-primary-foreground">
                                            {{ department.code.substring(0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ department.name }}</p>
                                            <p v-if="department.description" class="text-sm text-muted-foreground line-clamp-1">
                                                {{ department.description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <code class="rounded bg-muted px-2 py-1 text-xs">{{ department.code }}</code>
                                </td>
                                <td class="p-4">
                                    <div v-if="department.manager">
                                        <p class="font-medium">{{ department.manager.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ department.manager.employee_id }}</p>
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground">Belum ada manajer</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Users class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm font-medium">{{ department.employees_count }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': department.is_active,
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !department.is_active
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ department.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/departments/${department.id}`"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="`/admin/departments/${department.id}/edit`"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-muted"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            @click="deleteDepartment(department)"
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
                        Menampilkan {{ departments.from || 0 }}-{{ departments.to || 0 }} dari {{ departments.total }} hasil
                    </div>
                    <div class="flex items-center gap-2">
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

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            v-model:open="showDeleteModal"
            title="Hapus Departemen"
            :description="`Yakin ingin menghapus departemen '${selectedDepartment?.name}'? Aksi ini tidak dapat dibatalkan.`"
            confirm-text="Hapus"
            variant="destructive"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import {
    Building,
    CheckCircle,
    XCircle,
    Users,
    Plus,
    FilterX,
    Eye,
    Edit,
    Trash,
} from 'lucide-vue-next';
import { debounce } from 'lodash';

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
    };
}

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
    router.get('/admin/departments', {
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
            showDeleteModal.value = false;
            selectedDepartment.value = null;
        },
        onError: () => {
            toast({
                title: 'Gagal!',
                description: 'Terjadi kesalahan saat menghapus departemen.',
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

.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}
</style>