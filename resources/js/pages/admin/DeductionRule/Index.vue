<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
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
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                                <Clock class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Potongan Telat</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ lateRules }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-500/10">
                                <UserX class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Potongan Alfa</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ absentRules }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <Calendar class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Potongan Cuti</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ leaveRules }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-500/10">
                                <Settings class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Lainnya</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ otherRules }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rules List -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Settings class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Daftar Aturan Potongan
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Nama Aturan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Tipe
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Metode Perhitungan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Jumlah
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
                            <tr v-for="rule in deductionRules" :key="rule.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ rule.name }}
                                    </div>
                                    <div v-if="rule.description" class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ rule.description }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getTypeClass(rule.type)"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        <component :is="getTypeIcon(rule.type)" class="mr-1 h-3 w-3" />
                                        {{ getTypeLabel(rule.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ getCalculationMethodLabel(rule.calculation_method) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatAmount(rule.amount, rule.calculation_method) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="`/admin/deduction-rules/${rule.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-blue-500 px-3 py-1.5 text-xs text-white transition-colors hover:bg-blue-600"
                                            title="Edit aturan"
                                        >
                                            <Edit class="mr-1 h-3 w-3" />
                                            Edit
                                        </Link>
                                        <button
                                            @click="confirmDelete(rule)"
                                            class="inline-flex items-center rounded-md bg-red-500 px-3 py-1.5 text-xs text-white transition-colors hover:bg-red-600"
                                            title="Hapus aturan"
                                        >
                                            <Trash2 class="mr-1 h-3 w-3" />
                                            Hapus
                                        </button>
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
