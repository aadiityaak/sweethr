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
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Karyawan</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ users.meta?.total || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sudah Diatur</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ usersWithSalary }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-500/10">
                                <AlertCircle class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Belum Diatur</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ usersWithoutSalary }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-500/10">
                                <DollarSign class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Gaji</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(averageSalary) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee List -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Users class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Daftar Karyawan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th
                                    @click="toggleSort('name')"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <div class="flex items-center gap-2">
                                        Karyawan
                                        <component :is="getSortIcon('name')" class="h-4 w-4" />
                                    </div>
                                </th>
                                <th
                                    @click="toggleSort('employee_id')"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <div class="flex items-center gap-2">
                                        ID Karyawan
                                        <component :is="getSortIcon('employee_id')" class="h-4 w-4" />
                                    </div>
                                </th>
                                <th
                                    @click="toggleSort('department')"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <div class="flex items-center gap-2">
                                        Departemen
                                        <component :is="getSortIcon('department')" class="h-4 w-4" />
                                    </div>
                                </th>
                                <th
                                    @click="toggleSort('position')"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <div class="flex items-center gap-2">
                                        Jabatan
                                        <component :is="getSortIcon('position')" class="h-4 w-4" />
                                    </div>
                                </th>
                                <th
                                    @click="toggleSort('current_salary')"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <div class="flex items-center gap-2">
                                        Gaji Pokok
                                        <component :is="getSortIcon('current_salary')" class="h-4 w-4" />
                                    </div>
                                </th>
                                <th
                                    @click="toggleSort('total_allowances')"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <div class="flex items-center gap-2">
                                        Total Tunjangan
                                        <component :is="getSortIcon('total_allowances')" class="h-4 w-4" />
                                    </div>
                                </th>
                                <th
                                    @click="toggleSort('has_salary_setting')"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <div class="flex items-center gap-2">
                                        Status
                                        <component :is="getSortIcon('has_salary_setting')" class="h-4 w-4" />
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr v-for="user in users?.data || []" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
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
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="edit.url(user.id)"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            :href="show.url(user.id)"
                                            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
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
                <div v-if="users?.links && users.links.length > 3" class="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-3 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <Link
                            v-if="users.links[0]?.url"
                            :href="users.links[0].url"
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="users.links[users.links.length - 1]?.url"
                            :href="users.links[users.links.length - 1].url"
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ users.meta?.from || 0 }}</span>
                                to
                                <span class="font-medium">{{ users.meta?.to || 0 }}</span>
                                of
                                <span class="font-medium">{{ users.meta?.total || 0 }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <Link
                                    v-for="(link, index) in users.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    :class="[
                                        'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                        link.active
                                            ? 'z-10 bg-blue-600 text-white focus:z-20'
                                            : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                        !link.url ? 'cursor-not-allowed opacity-50' : '',
                                        index === 0 ? 'rounded-l-md' : '',
                                        index === users.links.length - 1 ? 'rounded-r-md' : '',
                                        'border border-gray-300 dark:border-gray-600',
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
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
    { name: 'Dashboard', href: dashboard.url() },
    { name: 'Pengaturan Gaji', href: salarySettingsIndex.url() },
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
        }
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

    const total = usersWithSalaryData.reduce((sum, user) => sum + user.current_salary, 0);
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
