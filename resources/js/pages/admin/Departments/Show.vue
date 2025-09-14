<template>
    <Head :title="`Departemen ${department.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Detail Departemen</h1>
                    <p class="text-muted-foreground">Informasi lengkap departemen "{{ department.name }}"</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="`/admin/departments/${department.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <Edit class="h-4 w-4" />
                        Edit Departemen
                    </Link>
                    <Link
                        href="/admin/departments"
                        class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Department Info Cards -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-semibold mb-4">Informasi Departemen</h3>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Nama Departemen</label>
                                    <p class="text-lg font-semibold">{{ department.name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Kode</label>
                                    <p class="font-mono text-lg">{{ department.code }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Status</label>
                                    <div class="mt-1">
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': department.is_active,
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !department.is_active
                                            }"
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        >
                                            {{ department.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Manajer Departemen</label>
                                    <div v-if="department.manager" class="mt-1">
                                        <p class="font-semibold">{{ department.manager.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ department.manager.employee_id }}</p>
                                        <div v-if="department.manager.phone" class="flex items-center gap-2 mt-1">
                                            <Phone class="h-4 w-4 text-muted-foreground" />
                                            <span class="text-sm">{{ department.manager.phone }}</span>
                                        </div>
                                        <div v-if="department.manager.email" class="flex items-center gap-2 mt-1">
                                            <Mail class="h-4 w-4 text-muted-foreground" />
                                            <span class="text-sm">{{ department.manager.email }}</span>
                                        </div>
                                    </div>
                                    <p v-else class="text-muted-foreground mt-1">Belum ada manajer</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="department.description" class="mt-6">
                            <label class="text-sm font-medium text-muted-foreground">Deskripsi</label>
                            <p class="text-sm mt-1 leading-relaxed">{{ department.description }}</p>
                        </div>
                    </div>

                    <!-- Department Employees -->
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Karyawan Departemen ({{ employees.length }})</h3>
                        </div>

                        <div v-if="employees.length > 0" class="space-y-3">
                            <div
                                v-for="employee in employees"
                                :key="employee.id"
                                class="flex items-center justify-between rounded-md border p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-medium text-primary-foreground">
                                        {{ getInitials(employee.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ employee.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ employee.employee_id }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p v-if="employee.position" class="text-sm font-medium">{{ employee.position.name }}</p>
                                    <p v-if="employee.hire_date" class="text-xs text-muted-foreground">
                                        Bergabung {{ formatDate(employee.hire_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center text-muted-foreground py-8">
                            <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <p>Belum ada karyawan di departemen ini</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-semibold mb-4">Aksi Cepat</h3>
                        <div class="space-y-2">
                            <Link
                                :href="`/admin/departments/${department.id}/edit`"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted"
                            >
                                <Edit class="h-4 w-4" />
                                Edit Departemen
                            </Link>
                            <button
                                @click="toggleDepartmentStatus"
                                class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted"
                            >
                                <Power class="h-4 w-4" />
                                {{ department.is_active ? 'Nonaktifkan' : 'Aktifkan' }} Departemen
                            </button>
                            <button
                                @click="deleteDepartment"
                                :disabled="employees.length > 0"
                                class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-red-600 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <Trash class="h-4 w-4" />
                                Hapus Departemen
                            </button>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-semibold mb-4">Statistik</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Total Karyawan:</span>
                                <span class="font-medium">{{ stats.total_employees }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Total Posisi:</span>
                                <span class="font-medium">{{ stats.positions_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Posisi Aktif:</span>
                                <span class="font-medium">{{ stats.active_positions }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Rata-rata Masa Kerja:</span>
                                <span class="font-medium">{{ Math.round(stats.avg_tenure_months) }} bulan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    Edit,
    ArrowLeft,
    Phone,
    Mail,
    Users,
    Power,
    Trash,
} from 'lucide-vue-next';

interface Department {
    id: number;
    name: string;
    code: string;
    description?: string;
    is_active: boolean;
    manager?: {
        id: number;
        name: string;
        employee_id: string;
        phone?: string;
        email?: string;
    };
}

interface Employee {
    id: number;
    name: string;
    employee_id: string;
    email: string;
    phone?: string;
    hire_date?: string;
    position?: {
        id: number;
        name: string;
    };
    manager?: {
        id: number;
        name: string;
    };
}

interface Stats {
    total_employees: number;
    positions_count: number;
    active_positions: number;
    avg_tenure_months: number;
}

interface Props {
    department: Department;
    employees: Employee[];
    stats: Stats;
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
    {
        title: props.department.name,
        href: `/admin/departments/${props.department.id}`,
    },
];

// Methods
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const toggleDepartmentStatus = () => {
    const action = props.department.is_active ? 'nonaktifkan' : 'aktifkan';
    if (confirm(`Yakin ingin ${action} departemen "${props.department.name}"?`)) {
        router.put(`/admin/departments/${props.department.id}`, {
            ...props.department,
            is_active: !props.department.is_active,
        });
    }
};

const deleteDepartment = () => {
    if (props.employees.length > 0) {
        alert('Tidak dapat menghapus departemen yang memiliki karyawan aktif.');
        return;
    }

    if (confirm(`Yakin ingin menghapus departemen "${props.department.name}"? Aksi ini tidak dapat dibatalkan.`)) {
        router.delete(`/admin/departments/${props.department.id}`);
    }
};
</script>