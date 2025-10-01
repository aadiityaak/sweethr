<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown, Edit, Mail, Phone, Plus, Search, Trash2, UserCheck, Users } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

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
        sort?: string;
        direction?: string;
    };
    flash?: {
        success?: string;
        error?: string;
    };
}

const { employees, departments, positions, filters, flash } = defineProps<Props>();

const { toast } = useToast();

// Handle flash messages
onMounted(() => {
    if (flash?.success) {
        toast({
            title: 'Berhasil!',
            description: flash.success,
            variant: 'success',
            duration: 4000,
        });
    }

    if (flash?.error) {
        toast({
            title: 'Error!',
            description: flash.error,
            variant: 'destructive',
            duration: 5000,
        });
    }
});

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

// Delete functionality
const employeeToDelete = ref<Employee | null>(null);
const showDeleteDialog = ref(false);

// Sorting functionality
const sortField = ref(filters.sort || '');
const sortDirection = ref(filters.direction || 'asc');

const filterEmployees = () => {
    router.get(
        '/employees',
        {
            search: searchQuery.value || undefined,
            department: selectedDepartment.value || undefined,
            position: selectedPosition.value || undefined,
            status: selectedStatus.value || undefined,
            sort: sortField.value || undefined,
            direction: sortDirection.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
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

    filterEmployees();
};

// Get sort icon for column header
const getSortIcon = (field: string) => {
    if (sortField.value !== field) {
        return ArrowUpDown;
    }
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
};

const getStatusCircle = (employmentStatus: string) => {
    switch (employmentStatus) {
        case 'active':
            return 'bg-green-500';
        case 'inactive':
            return 'bg-red-500';
        case 'terminated':
            return 'bg-red-500';
        default:
            return 'bg-gray-500';
    }
};

// Delete functions
const confirmDelete = (employee: Employee) => {
    employeeToDelete.value = employee;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    employeeToDelete.value = null;
    showDeleteDialog.value = false;
};

const deleteEmployee = () => {
    if (!employeeToDelete.value) return;

    console.log('Attempting to delete employee:', employeeToDelete.value);

    router.delete(`/employees/${employeeToDelete.value.id}`, {
        onStart: () => {
            console.log('Delete request started');
        },
        onSuccess: () => {
            console.log('Delete request successful');
            // Server will redirect to employees.index with flash message
            // No need for manual toast since it will be handled by flash message
            cancelDelete();
        },
        onError: (errors) => {
            console.error('Delete request failed:', errors);
            toast({
                title: 'Error!',
                description: 'Gagal menghapus karyawan. Silakan coba lagi.',
                variant: 'destructive',
                duration: 5000,
            });
        },
        onFinish: () => {
            console.log('Delete request finished');
        },
    });
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Karyawan</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola informasi dan peran karyawan</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            href="/employees/create"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
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
                                    {{ employees?.data?.filter((emp) => emp.employment_status === 'active').length || 0 }}
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

                <!-- Inactive Employees -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-yellow-500/10">
                                <Users class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan Tidak Aktif</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ employees?.data?.filter((emp) => emp.employment_status === 'inactive').length || 0 }}
                                </p>
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
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                @input="filterEmployees"
                                type="text"
                                placeholder="Cari karyawan..."
                                class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-10 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
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
                            <option value="terminated">Dihentikan</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Employee List -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Users class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Direktori Karyawan
                    </h3>
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
                                            sortField === 'name' ? 'text-blue-600 dark:text-blue-400' : ''
                                        ]"
                                    >
                                        Karyawan
                                        <component :is="getSortIcon('name')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('department')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'department' ? 'text-blue-600 dark:text-blue-400' : ''
                                        ]"
                                    >
                                        Departemen
                                        <component :is="getSortIcon('department')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('position')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'position' ? 'text-blue-600 dark:text-blue-400' : ''
                                        ]"
                                    >
                                        Posisi
                                        <component :is="getSortIcon('position')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('email')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'email' ? 'text-blue-600 dark:text-blue-400' : ''
                                        ]"
                                    >
                                        Kontak
                                        <component :is="getSortIcon('email')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('hire_date')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'hire_date' ? 'text-blue-600 dark:text-blue-400' : ''
                                        ]"
                                    >
                                        Tanggal Masuk
                                        <component :is="getSortIcon('hire_date')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="sortBy('status')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            sortField === 'status' ? 'text-blue-600 dark:text-blue-400' : ''
                                        ]"
                                    >
                                        Status
                                        <component :is="getSortIcon('status')" class="h-4 w-4" />
                                    </button>
                                </th>
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
                                            <img :src="employee.profile_photo_url" :alt="employee.name" class="h-10 w-10 rounded-full object-cover" />
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
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-3 w-3 rounded-full"
                                                :class="getStatusCircle(employee.employment_status)"
                                                :title="employee.employment_status === 'active' ? 'Aktif' : 'Tidak Aktif'"
                                            ></div>
                                            <div v-if="employee.is_admin" class="text-xs text-blue-600 dark:text-blue-400">Admin</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/employees/${employee.id}/edit?v=${Date.now()}`"
                                            :preserve-state="false"
                                            :preserve-scroll="false"
                                            class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="confirmDelete(employee)"
                                            class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                        >
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
                            Menampilkan {{ employees?.meta?.from || 0 }} sampai {{ employees?.meta?.to || 0 }} dari {{ employees?.meta?.total || 0 }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in employees?.links || []"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'rounded border px-3 py-1 text-sm',
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800',
                                ]"
                                :disabled="!link.url"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <div
            v-if="showDeleteDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click="cancelDelete"
        >
            <div
                class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800"
                @click.stop
            >
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <Trash2 class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Hapus Karyawan</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-gray-700 dark:text-gray-300">
                        Apakah Anda yakin ingin menghapus karyawan
                        <span class="font-semibold">{{ employeeToDelete?.name }}</span>?
                    </p>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Semua data yang terkait dengan karyawan ini akan terhapus secara permanen.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="cancelDelete"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Batal
                    </button>
                    <button
                        @click="deleteEmployee"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800"
                    >
                        Hapus Karyawan
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
