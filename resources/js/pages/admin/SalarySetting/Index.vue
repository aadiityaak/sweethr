<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pengaturan Gaji</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola gaji pokok dan tunjangan karyawan</p>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Employees -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Karyawan</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Semua karyawan terdaftar</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ users.meta?.total || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white"> {{ users.meta?.total ? 100 : 0 }}% </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Employees with Salary Set -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Sudah Diatur</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Karyawan dengan gaji</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Diatur</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                {{ usersWithSalary }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ users.meta?.total ? Math.round((usersWithSalary / users.meta.total) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Employees without Salary Set -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                                <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Belum Diatur</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Karyawan tanpa gaji</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-amber-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-amber-50/50 px-3 py-2 dark:bg-amber-950/30">
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Belum</span>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                {{ usersWithoutSalary }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ users.meta?.total ? Math.round((usersWithoutSalary / users.meta.total) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Average Salary -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/10 ring-1 ring-purple-500/20">
                                <DollarSign class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Rata-rata Gaji</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Gaji pokok rata-rata</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-purple-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-purple-50/50 px-3 py-2 dark:bg-purple-950/30">
                            <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Rata-rata</span>
                            <span class="text-sm font-semibold text-purple-800 dark:text-purple-300">
                                {{ formatCurrency(averageSalary) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ usersWithSalary }}
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Employee List -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Users class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Gaji Karyawan</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ users.meta?.total || 0 }} karyawan ditemukan</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('name')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            filters.sort === 'name' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Karyawan
                                        <component :is="getSortIcon('name')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('employee_id')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            filters.sort === 'employee_id' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        ID Karyawan
                                        <component :is="getSortIcon('employee_id')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('department')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            filters.sort === 'department' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Departemen
                                        <component :is="getSortIcon('department')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('position')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            filters.sort === 'position' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Jabatan
                                        <component :is="getSortIcon('position')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('current_salary')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            filters.sort === 'current_salary' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Gaji Pokok
                                        <component :is="getSortIcon('current_salary')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('total_allowances')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            filters.sort === 'total_allowances' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Total Tunjangan
                                        <component :is="getSortIcon('total_allowances')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    <button
                                        @click="toggleSort('has_salary_setting')"
                                        :class="[
                                            'flex items-center gap-2 hover:text-gray-700 dark:hover:text-gray-200',
                                            filters.sort === 'has_salary_setting' ? 'text-blue-600 dark:text-blue-400' : '',
                                        ]"
                                    >
                                        Status
                                        <component :is="getSortIcon('has_salary_setting')" class="h-4 w-4" />
                                    </button>
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users?.data || []"
                                :key="user.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ user.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ user.employee_id }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ user.department || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ user.position || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(user.current_salary) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(user.total_allowances) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="user.has_salary_setting"
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                                    >
                                        <CheckCircle class="mr-1 h-3 w-3" />
                                        Sudah Diatur
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200"
                                    >
                                        <XCircle class="mr-1 h-3 w-3" />
                                        Belum Diatur
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="edit.url(user.id)"
                                            class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="show.url(user.id)"
                                            class="rounded-lg bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-900/30 dark:text-gray-400 dark:hover:bg-gray-900/50"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users?.links && users.links.length > 3" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ users.meta?.from || 0 }} sampai {{ users.meta?.to || 0 }} dari {{ users.meta?.total || 0 }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="(link, index) in users.links"
                                :key="index"
                                :href="link.url || '#'"
                                :class="[
                                    'rounded border px-3 py-1 text-sm',
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800',
                                    !link.url ? 'cursor-not-allowed opacity-50' : '',
                                ]"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { edit, index as salarySettingsIndex, show } from '@/routes/admin/salary-settings';
import { Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowDownUp, CheckCircle, ChevronDown, ChevronUp, DollarSign, Edit, Eye, Users, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    employee_id: string;
    department: string | null;
    position: string | null;
    current_salary: number | null;
    total_allowances: number | null;
    has_salary_setting: boolean;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        meta: any;
    };
    filters: {
        sort?: string;
        direction?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard.url() },
    { title: 'Pengaturan Gaji', href: salarySettingsIndex.url() },
];

// Sorting state
type SortField = 'name' | 'employee_id' | 'department' | 'position' | 'current_salary' | 'total_allowances' | 'has_salary_setting';

const toggleSort = (field: SortField) => {
    const currentSort = props.filters.sort;
    const currentDirection = props.filters.direction;

    let newDirection: 'asc' | 'desc' | null = 'asc';

    if (currentSort === field) {
        // Cycle through: asc -> desc -> null
        if (currentDirection === 'asc') {
            newDirection = 'desc';
        } else if (currentDirection === 'desc') {
            newDirection = null;
        }
    }

    // Navigate with query parameters
    router.get(
        salarySettingsIndex.url(),
        {
            sort: newDirection ? field : undefined,
            direction: newDirection || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const getSortIcon = (field: SortField) => {
    if (props.filters.sort !== field) return ArrowDownUp;
    return props.filters.direction === 'asc' ? ChevronUp : ChevronDown;
};

const usersWithSalary = computed(() => {
    if (!props.users?.data) return 0;
    return props.users.data.filter((user) => user.has_salary_setting).length;
});

const usersWithoutSalary = computed(() => {
    if (!props.users?.data) return 0;
    return props.users.data.filter((user) => !user.has_salary_setting).length;
});

const averageSalary = computed(() => {
    if (!props.users?.data) return 0;

    const usersWithSalaryData = props.users.data.filter((user) => {
        return user.has_salary_setting && user.current_salary != null && user.current_salary > 0;
    });

    if (usersWithSalaryData.length === 0) return 0;

    const total = usersWithSalaryData.reduce((sum, user) => sum + (user.current_salary || 0), 0);
    return Math.round(total / usersWithSalaryData.length);
});

const formatCurrency = (amount: number | null | undefined) => {
    if (!amount || isNaN(amount)) return 'Rp0';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>
