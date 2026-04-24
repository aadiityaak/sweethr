<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Riwayat Gaji - {{ user.name }}</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            ID: {{ user.employee_id }} • {{ user.department?.name }} • {{ user.position?.name }}
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <Link
                            href="/admin/salary-settings"
                            class="inline-flex items-center rounded-lg bg-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-400"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                        <Link
                            :href="`/admin/salary-settings/${user.id}/edit`"
                            class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-white transition-colors hover:bg-blue-600"
                        >
                            <Edit class="mr-2 h-4 w-4" />
                            Edit Gaji
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Current Salary Overview -->
            <div
                v-if="currentSetting"
                class="mb-8 overflow-hidden rounded-xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 dark:border-green-800 dark:from-green-900/20 dark:to-emerald-900/20"
            >
                <div class="p-8">
                    <div class="mb-6 text-center">
                        <h2 class="mb-2 text-2xl font-bold text-green-900 dark:text-green-100">Pengaturan Gaji Aktif</h2>
                        <p class="text-green-700 dark:text-green-300">Berlaku sejak {{ formatDate(currentSetting.effective_date) }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                        <div class="text-center">
                            <h3 class="mb-2 text-lg font-semibold text-green-900 dark:text-green-100">Gaji Pokok</h3>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(currentSetting.base_salary) }}</p>
                        </div>
                        <div class="text-center">
                            <h3 class="mb-2 text-lg font-semibold text-green-900 dark:text-green-100">Total Tunjangan</h3>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ formatCurrency(currentSetting.total_allowances || 0) }}
                            </p>
                        </div>
                        <div class="text-center">
                            <h3 class="mb-2 text-lg font-semibold text-green-900 dark:text-green-100">Rate Lembur</h3>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ currentSetting.overtime_rate }}x</p>
                        </div>
                        <div class="text-center">
                            <h3 class="mb-2 text-lg font-semibold text-green-900 dark:text-green-100">Gaji Kotor</h3>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(grossSalary) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Salary Setting Warning -->
            <div v-else class="mb-8 overflow-hidden rounded-xl border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
                <div class="p-8 text-center">
                    <AlertTriangle class="mx-auto mb-4 h-16 w-16 text-red-600 dark:text-red-400" />
                    <h3 class="mb-2 text-lg font-semibold text-red-900 dark:text-red-100">Belum Ada Pengaturan Gaji</h3>
                    <p class="mb-4 text-red-700 dark:text-red-300">
                        Karyawan ini belum memiliki pengaturan gaji. Silakan buat pengaturan gaji terlebih dahulu.
                    </p>
                    <Link
                        :href="route('admin.salary-settings.edit', user.id)"
                        class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Buat Pengaturan Gaji
                    </Link>
                </div>
            </div>

            <div v-if="currentSetting" class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Detailed Allowances Table -->
                    <div
                        v-if="currentSetting.allowances && currentSetting.allowances.length > 0"
                        class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <List class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                Detail Tunjangan
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rincian lengkap semua tunjangan yang diterima</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                            No
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                            Nama Tunjangan
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                        >
                                            Jumlah
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                        >
                                            Persentase
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                        >
                                            Keterangan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700">
                                    <tr
                                        v-for="(allowance, index) in currentSetting.allowances"
                                        :key="index"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-900/50"
                                    >
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ allowance.name }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ formatCurrency(allowance.amount) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                                :class="
                                                    allowance.amount >= 500000
                                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                                                        : allowance.amount >= 200000
                                                          ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
                                                          : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                                "
                                            >
                                                {{ Math.round((allowance.amount / currentSetting.total_allowances) * 100) }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                                :class="
                                                    allowance.name === 'meal'
                                                        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
                                                        : allowance.name === 'transport'
                                                          ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                                                          : allowance.name === 'communication'
                                                            ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300'
                                                            : allowance.name === 'pulsa'
                                                              ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
                                                              : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
                                                "
                                            >
                                                {{ allowance.name }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(currentSetting.total_allowances) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Info -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <User class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                Informasi Karyawan
                            </h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ID Karyawan</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.employee_id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Departemen</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.department?.name || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jabatan</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.position?.name || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Salary History -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <History class="mr-2 h-5 w-5 text-purple-600 dark:text-purple-400" />
                                Riwayat Perubahan Gaji
                            </h3>
                        </div>
                        <div class="p-6">
                            <div v-if="salarySettings.length === 0" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                <FileText class="mx-auto mb-4 h-12 w-12 opacity-50" />
                                <p>Belum ada riwayat perubahan gaji</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="setting in salarySettings"
                                    :key="setting.id"
                                    class="rounded-lg border p-4"
                                    :class="
                                        setting.is_active
                                            ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20'
                                            : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20'
                                    "
                                >
                                    <div class="mb-3 flex items-start justify-between">
                                        <div>
                                            <div class="mb-1 flex items-center space-x-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ formatDate(setting.effective_date) }}
                                                </span>
                                                <span
                                                    v-if="setting.is_active"
                                                    class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                                                >
                                                    <CheckCircle class="mr-1 h-3 w-3" />
                                                    Aktif
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Dibuat {{ formatDate(setting.created_at) }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-500 dark:text-gray-400">Gaji Pokok</p>
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(setting.base_salary) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500 dark:text-gray-400">Tunjangan</p>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                {{ formatCurrency(setting.total_allowances || 0) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="setting.allowances && setting.allowances.length > 0"
                                        class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-700"
                                    >
                                        <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Detail Tunjangan:</p>
                                        <div class="grid grid-cols-1 gap-1">
                                            <div v-for="(allowance, index) in setting.allowances" :key="index" class="flex justify-between text-xs">
                                                <span class="text-gray-600 dark:text-gray-400">• {{ allowance.name }}</span>
                                                <span class="font-medium">{{ formatCurrency(allowance.amount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overtime Settings -->
                    <div
                        v-if="currentSetting"
                        class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <Clock class="mr-2 h-5 w-5 text-orange-600 dark:text-orange-400" />
                                Pengaturan Lembur
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="rounded-lg bg-orange-50 p-6 text-center dark:bg-orange-900/20">
                                <p class="mb-2 text-3xl font-bold text-orange-600 dark:text-orange-400">{{ currentSetting.overtime_rate }}x</p>
                                <p class="text-sm text-orange-700 dark:text-orange-300">Rate Lembur</p>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Dari gaji harian normal</p>
                            </div>

                            <!-- Overtime Calculation Example -->
                            <div class="mt-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Contoh Perhitungan:</h4>
                                <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                    <p>• Gaji harian: {{ formatCurrency(currentSetting.base_salary / 22) }}</p>
                                    <p>• Gaji per jam: {{ formatCurrency(currentSetting.base_salary / 22 / 8) }}</p>
                                    <p>• Lembur 1 jam: {{ formatCurrency((currentSetting.base_salary / 22 / 8) * currentSetting.overtime_rate) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, CheckCircle, Clock, Edit, FileText, History, Plus, User } from 'lucide-vue-next';
import { computed } from 'vue';

interface Allowance {
    name: string;
    amount: number;
}

interface SalarySetting {
    id: number;
    base_salary: number;
    allowances: Allowance[];
    overtime_rate: number;
    effective_date: string;
    created_at: string;
    is_active: boolean;
    total_allowances: number;
}

interface User {
    id: number;
    name: string;
    employee_id: string;
    department: { name: string } | null;
    position: { name: string } | null;
}

interface Props {
    user: User;
    salarySettings: SalarySetting[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Pengaturan Gaji', href: '/admin/salary-settings' },
    { title: props.user.name, href: `/admin/salary-settings/${props.user.id}` },
];

const currentSetting = computed(() => {
    return props.salarySettings.find((setting) => setting.is_active);
});

const grossSalary = computed(() => {
    if (!currentSetting.value) return 0;
    return currentSetting.value.base_salary + (currentSetting.value.total_allowances || 0);
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>
