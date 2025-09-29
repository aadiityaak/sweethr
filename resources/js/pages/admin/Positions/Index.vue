<template>
    <Head title="Admin - Kelola Posisi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Posisi</h1>
                    <p class="text-muted-foreground">Kelola posisi dan jabatan dalam organisasi</p>
                </div>
                <Link
                    href="/admin/positions/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    Tambah Posisi
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                            <Briefcase class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Posisi</p>
                            <p class="text-2xl font-bold">{{ stats.total_positions }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Posisi Aktif</p>
                            <p class="text-2xl font-bold">{{ stats.active_positions }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                            <Users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Karyawan Assigned</p>
                            <p class="text-2xl font-bold">{{ stats.employees_count }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900/20">
                            <Building class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Departemen</p>
                            <p class="text-2xl font-bold">{{ departments.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card p-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <Input v-model="search" placeholder="Cari posisi..." class="w-full" @input="debouncedSearch" />
                    </div>
                    <div>
                        <select
                            v-model="selectedDepartment"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Semua Departemen</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
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

            <!-- Positions Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Daftar Posisi</h3>
                    <p class="text-sm text-muted-foreground">{{ positions.total }} posisi ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="text-left">
                                <th class="p-4 font-semibold">Posisi</th>
                                <th class="p-4 font-semibold">Departemen</th>
                                <th class="p-4 font-semibold">Level</th>
                                <th class="p-4 font-semibold">Gaji Pokok</th>
                                <th class="p-4 font-semibold">Karyawan</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="position in positions.data" :key="position.id" class="border-b hover:bg-muted/50">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-medium text-primary-foreground"
                                        >
                                            {{ position.code.substring(0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ position.title }}</p>
                                            <p v-if="position.description" class="line-clamp-1 text-sm text-muted-foreground">
                                                {{ position.description }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-medium">{{ position.department?.name }}</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded bg-secondary text-xs font-medium">
                                            {{ position.level }}
                                        </div>
                                        <span class="text-sm text-muted-foreground">{{ getLevelLabel(position.level) }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-medium">{{ formatCurrency(position.base_salary) }}</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Users class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm font-medium">{{ position.employees_count || 0 }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': position.is_active,
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !position.is_active,
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ position.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/positions/${position.id}`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-muted"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="`/admin/positions/${position.id}/edit`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-muted"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            @click="deletePosition(position)"
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
                <div class="flex items-center justify-between border-t p-4">
                    <div class="text-sm text-muted-foreground">
                        Menampilkan {{ positions.from || 0 }}-{{ positions.to || 0 }} dari {{ positions.total }} hasil
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-for="link in positions.links"
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
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Briefcase, Building, CheckCircle, Edit, Eye, FilterX, Plus, Trash, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface Department {
    id: number;
    name: string;
    code: string;
}

interface Position {
    id: number;
    title: string;
    code: string;
    description?: string;
    level: number;
    base_salary: number;
    is_active: boolean;
    employees_count: number;
    department?: Department;
}

interface Stats {
    total_positions: number;
    active_positions: number;
    employees_count: number;
}

interface Props {
    positions: {
        data: Position[];
        total: number;
        from: number;
        to: number;
        links: any[];
    };
    departments: Department[];
    stats: Stats;
    filters: {
        search?: string;
        department?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Posisi',
        href: '/admin/positions',
    },
];

// Filter states
const search = ref(props.filters.search || '');
const selectedDepartment = ref(props.filters.department || '');

// Methods
const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    router.get(
        '/admin/positions',
        {
            search: search.value,
            department: selectedDepartment.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    selectedDepartment.value = '';
    router.get('/admin/positions');
};

const getLevelLabel = (level: number) => {
    const labels = {
        1: 'Junior',
        2: 'Staff',
        3: 'Senior',
        4: 'Lead',
        5: 'Manager',
        6: 'Director',
    };
    return labels[level] || 'Unknown';
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const deletePosition = (position: Position) => {
    if (position.employees_count > 0) {
        alert('Tidak dapat menghapus posisi yang memiliki karyawan aktif.');
        return;
    }

    if (confirm(`Hapus posisi "${position.title}"? Aksi ini tidak dapat dibatalkan.`)) {
        router.delete(`/admin/positions/${position.id}`);
    }
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
