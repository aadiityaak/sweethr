<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
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
                    <!-- Current Allowances -->
                    <div
                        v-if="currentSetting.allowances && currentSetting.allowances.length > 0"
                        class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <Gift class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                                Tunjangan Aktif
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div
                                    v-for="(allowance, index) in currentSetting.allowances"
                                    :key="index"
                                    class="flex items-center justify-between rounded-lg bg-green-50 p-3 dark:bg-green-900/20"
                                >
                                    <span class="font-medium text-green-900 dark:text-green-100">{{ allowance.name }}</span>
                                    <span class="font-bold text-green-600 dark:text-green-400">{{ formatCurrency(allowance.amount) }}</span>
                                </div>
                                <hr class="border-gray-200 dark:border-gray-700" />
                                <div class="flex items-center justify-between font-semibold">
                                    <span class="text-gray-900 dark:text-white">Total Tunjangan</span>
                                    <span class="text-green-600 dark:text-green-400">{{ formatCurrency(currentSetting.total_allowances || 0) }}</span>
                                </div>
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
import { AlertTriangle, ArrowLeft, CheckCircle, Clock, Edit, FileText, Gift, History, Plus, User } from 'lucide-vue-next';
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
