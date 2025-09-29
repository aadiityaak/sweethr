<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
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
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ users.length }}</p>
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
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    ID Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Departemen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Jabatan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Gaji Pokok
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Total Tunjangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
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
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { edit, index as salarySettingsIndex, show } from '@/routes/admin/salary-settings';
import { Link } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, DollarSign, Edit, Eye, Users, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    employee_id: string;
    department: string | null;
    position: string | null;
    current_salary: number;
    total_allowances: number;
    has_salary_setting: boolean;
}

interface Props {
    users: User[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { name: 'Dashboard', href: dashboard.url() },
    { name: 'Pengaturan Gaji', href: salarySettingsIndex.url() },
];

const usersWithSalary = computed(() => {
    return props.users.filter((user) => user.has_salary_setting).length;
});

const usersWithoutSalary = computed(() => {
    return props.users.filter((user) => !user.has_salary_setting).length;
});

const averageSalary = computed(() => {
    const usersWithSalaryData = props.users.filter((user) => user.has_salary_setting);
    if (usersWithSalaryData.length === 0) return 0;

    const total = usersWithSalaryData.reduce((sum, user) => sum + user.current_salary, 0);
    return Math.round(total / usersWithSalaryData.length);
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>
