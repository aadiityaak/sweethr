<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Users, Mail, Phone, MapPin, Calendar, MoreHorizontal, Edit, Trash2, UserCheck } from 'lucide-vue-next';
import { ref } from 'vue';

interface Department {
    id: number;
    name: string;
}

interface Position {
    id: number;
    title: string;
    level: number;
}

interface Employee {
    id: number;
    name: string;
    email: string;
    employee_id: string;
    phone?: string;
    hire_date: string;
    employment_status: string;
    is_admin: boolean;
    department?: Department;
    position?: Position;
    profile_photo_url?: string;
}

interface Props {
    employees?: {
        data: Employee[];
        links: any[];
        meta: any;
    };
    departments: Department[];
    positions: Position[];
    filters: {
        search?: string;
        department?: string;
        position?: string;
        status?: string;
    };
}

const { employees, departments, positions, filters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Karyawan',
        href: '/employees',
    },
];

const searchQuery = ref(filters.search || '');
const selectedDepartment = ref(filters.department || '');
const selectedPosition = ref(filters.position || '');
const selectedStatus = ref(filters.status || '');

const filterEmployees = () => {
    router.get('/employees', {
        search: searchQuery.value || undefined,
        department: selectedDepartment.value || undefined,
        position: selectedPosition.value || undefined,
        status: selectedStatus.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const getStatusBadge = (employmentStatus: string) => {
    return employmentStatus === 'active'
        ? 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-600'
        : 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-600';
};

const getInitials = (name: string) => {
    return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Karyawan
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Kelola informasi dan peran karyawan
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            href="/employees/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                        >
                            <Plus class="h-4 w-4" />
                            Tambah Karyawan
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Employees -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Karyawan</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ employees?.meta?.total || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Employees -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <UserCheck class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan Aktif</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ employees?.data?.filter(emp => emp.employment_status === 'active').length || 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departments -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <Users class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Departemen</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ departments.length }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Positions -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-500/10">
                                <Users class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Posisi</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ positions.length }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                @input="filterEmployees"
                                type="text"
                                placeholder="Cari karyawan..."
                                class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                        <select
                            v-model="selectedDepartment"
                            @change="filterEmployees"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                        >
                            <option value="">Semua Departemen</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <select
                            v-model="selectedPosition"
                            @change="filterEmployees"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                        >
                            <option value="">Semua Posisi</option>
                            <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                                {{ pos.title }}
                            </option>
                        </select>
                        <select
                            v-model="selectedStatus"
                            @change="filterEmployees"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Employee List -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <Users class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Direktori Karyawan
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Karyawan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Departemen</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Posisi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Kontak</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Masuk</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="employee in employees?.data || []"
                                :key="employee.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div v-if="employee.profile_photo_url" class="h-10 w-10 rounded-full">
                                            <img
                                                :src="employee.profile_photo_url"
                                                :alt="employee.name"
                                                class="h-10 w-10 rounded-full object-cover"
                                            />
                                        </div>
                                        <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                {{ getInitials(employee.name) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ employee.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ employee.employee_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ employee.department?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ employee.position?.title || '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <Mail class="h-3 w-3" />
                                            {{ employee.email }}
                                        </div>
                                        <div v-if="employee.phone" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <Phone class="h-3 w-3" />
                                            {{ employee.phone }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ formatDate(employee.hire_date) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <span
                                            class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                                            :class="getStatusBadge(employee.employment_status)"
                                        >
                                            {{ employee.employment_status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                        <div v-if="employee.is_admin" class="text-xs text-blue-600 dark:text-blue-400">
                                            Admin
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/employees/${employee.id}/edit`"
                                            class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="employees?.meta?.total > employees?.meta?.per_page" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ employees.meta.from }} sampai {{ employees.meta.to }} dari {{ employees.meta.total }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in employees.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1 text-sm border rounded',
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'text-gray-600 border-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-800'
                                ]"
                                :disabled="!link.url"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>