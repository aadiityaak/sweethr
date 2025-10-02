<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Aturan Potongan</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola aturan potongan gaji untuk telat, tidak masuk, dan lainnya</p>
                    </div>
                    <Link
                        href="/admin/deduction-rules/create"
                        class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-white transition-colors hover:bg-blue-600"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Aturan
                    </Link>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Potongan Telat -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-red-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-red-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/20">
                                <Clock class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Potongan Telat</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Aturan untuk keterlambatan
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-red-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-red-50/50 px-3 py-2 dark:bg-red-950/30">
                            <span class="text-sm font-medium text-red-700 dark:text-red-400">Total Aturan</span>
                            <span class="text-sm font-semibold text-red-800 dark:text-red-300">{{ lateRules }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ deductionRules.length > 0 ? Math.round((lateRules / deductionRules.length) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-red-500/5 to-rose-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Potongan Alfa -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-orange-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-orange-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 ring-1 ring-orange-500/20">
                                <UserX class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Potongan Alfa</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Aturan untuk tidak masuk
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-orange-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-orange-50/50 px-3 py-2 dark:bg-orange-950/30">
                            <span class="text-sm font-medium text-orange-700 dark:text-orange-400">Total Aturan</span>
                            <span class="text-sm font-semibold text-orange-800 dark:text-orange-300">{{ absentRules }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ deductionRules.length > 0 ? Math.round((absentRules / deductionRules.length) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-orange-500/5 to-amber-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Potongan Cuti -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/10 ring-1 ring-purple-500/20">
                                <Calendar class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Potongan Cuti</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Aturan untuk cuti berlebih
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-purple-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-purple-50/50 px-3 py-2 dark:bg-purple-950/30">
                            <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Total Aturan</span>
                            <span class="text-sm font-semibold text-purple-800 dark:text-purple-300">{{ leaveRules }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ deductionRules.length > 0 ? Math.round((leaveRules / deductionRules.length) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Lainnya -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-500/10 ring-1 ring-gray-500/20">
                                <Settings class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Lainnya</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Aturan lainnya
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-gray-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">Total Aturan</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-300">{{ otherRules }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ deductionRules.length > 0 ? Math.round((otherRules / deductionRules.length) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-gray-500/5 to-slate-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Rules List -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Settings class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Aturan Potongan</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ deductionRules.length }} aturan ditemukan</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Nama Aturan
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Tipe
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Metode Perhitungan
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Jumlah
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="rule in deductionRules"
                                :key="rule.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ rule.name }}</p>
                                        <p v-if="rule.description" class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ rule.description }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="getTypeClass(rule.type)"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        <component :is="getTypeIcon(rule.type)" class="mr-1 h-3 w-3" />
                                        {{ getTypeLabel(rule.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ getCalculationMethodLabel(rule.calculation_method) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatAmount(rule.amount, rule.calculation_method) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        v-if="rule.is_active"
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                                    >
                                        <CheckCircle class="mr-1 h-3 w-3" />
                                        Aktif
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200"
                                    >
                                        <XCircle class="mr-1 h-3 w-3" />
                                        Tidak Aktif
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/deduction-rules/${rule.id}/edit`"
                                            class="rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                            title="Edit aturan"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="confirmDelete(rule)"
                                            class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                            title="Hapus aturan"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Hover effect overlay -->
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Calendar, CheckCircle, Clock, Edit, Plus, Settings, Trash2, UserX, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface DeductionRule {
    id: number;
    name: string;
    type: 'late' | 'absent' | 'excess_leave' | 'other';
    calculation_method: 'fixed' | 'per_minute' | 'per_hour' | 'per_day' | 'percentage';
    amount: number;
    description: string | null;
    is_active: boolean;
}

interface Props {
    deductionRules: DeductionRule[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Aturan Potongan', href: '/admin/deduction-rules' },
];

const lateRules = computed(() => {
    return props.deductionRules.filter((rule) => rule.type === 'late').length;
});

const absentRules = computed(() => {
    return props.deductionRules.filter((rule) => rule.type === 'absent').length;
});

const leaveRules = computed(() => {
    return props.deductionRules.filter((rule) => rule.type === 'excess_leave').length;
});

const otherRules = computed(() => {
    return props.deductionRules.filter((rule) => rule.type === 'other').length;
});

const getTypeLabel = (type: string) => {
    const labels = {
        late: 'Telat',
        absent: 'Tidak Masuk',
        excess_leave: 'Cuti Berlebih',
        other: 'Lainnya',
    };
    return labels[type] || type;
};

const getTypeClass = (type: string) => {
    const classes = {
        late: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        absent: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        excess_leave: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        other: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return classes[type] || classes.other;
};

const getTypeIcon = (type: string) => {
    const icons = {
        late: Clock,
        absent: UserX,
        excess_leave: Calendar,
        other: Settings,
    };
    return icons[type] || icons.other;
};

const getCalculationMethodLabel = (method: string) => {
    const labels = {
        fixed: 'Tetap',
        per_minute: 'Per Menit',
        per_hour: 'Per Jam',
        per_day: 'Per Hari',
        percentage: 'Persentase',
    };
    return labels[method] || method;
};

const formatAmount = (amount: number, method: string) => {
    if (method === 'percentage') {
        return `${amount}%`;
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const confirmDelete = (rule: DeductionRule) => {
    if (confirm(`Apakah Anda yakin ingin menghapus aturan "${rule.name}"?`)) {
        router.delete(`/admin/deduction-rules/${rule.id}`);
    }
};
</script>
