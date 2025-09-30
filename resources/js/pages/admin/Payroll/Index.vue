<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Payroll</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Generate dan kelola payroll karyawan</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <select
                            v-model="selectedYear"
                            @change="changeYear"
                            class="block rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option v-for="year in availableYears" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                        <select
                            v-model="selectedMonth"
                            @change="changeMonth"
                            class="block rounded-lg border border-gray-300 bg-white px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option v-for="month in months" :key="month.value" :value="month.value">
                                {{ month.label }}
                            </option>
                        </select>
                        <button
                            @click="showGenerateModal = true"
                            class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-white transition-colors hover:bg-blue-600"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Generate Payroll
                        </button>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <DollarSign class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Payroll</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalPayroll) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ payrolls.length }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-500/10">
                                <TrendingDown class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Potongan</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalDeductions) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <Clock class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Gaji</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(averagePayroll) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll List -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <FileText class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Payroll {{ getCurrentPeriodName() }}
                    </h3>
                </div>

                <div v-if="payrolls.length === 0" class="p-12 text-center">
                    <FileText class="mx-auto mb-4 h-16 w-16 text-gray-400 dark:text-gray-600" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Belum Ada Payroll</h3>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">Payroll untuk periode ini belum digenerate.</p>
                    <button
                        @click="showGenerateModal = true"
                        class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-white transition-colors hover:bg-blue-600"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Generate Payroll Sekarang
                    </button>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Gaji Kotor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Potongan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Gaji Bersih
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Kehadiran
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr v-for="payroll in payrolls" :key="payroll.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ payroll.user.name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ payroll.user.employee_id }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(payroll.gross_salary) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                        -{{ formatCurrency(payroll.total_deductions) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                        {{ formatCurrency(payroll.net_salary) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ payroll.actual_working_days }}/{{ payroll.working_days }} hari
                                        <div v-if="payroll.overtime_hours > 0" class="text-xs text-blue-600 dark:text-blue-400">
                                            +{{ payroll.overtime_hours }}h lembur
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="`/admin/payrolls/${payroll.id}`"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="regeneratePayroll(payroll)"
                                            class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                                            title="Regenerate"
                                        >
                                            <RotateCcw class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Generate Modal -->
        <div v-if="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Generate Payroll {{ getCurrentPeriodName() }}</h3>

                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Yakin ingin generate payroll untuk periode {{ getCurrentPeriodName() }}? Proses ini akan menghitung gaji semua karyawan
                    berdasarkan data kehadiran dan aturan yang berlaku.
                </p>

                <div class="flex justify-end space-x-3">
                    <button
                        @click="showGenerateModal = false"
                        class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-400"
                    >
                        Batal
                    </button>
                    <button
                        @click="confirmGenerate"
                        :disabled="generating"
                        class="rounded-lg bg-blue-500 px-4 py-2 text-white transition-colors hover:bg-blue-600 disabled:opacity-50"
                    >
                        {{ generating ? 'Generating...' : 'Generate' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div
            v-if="showToast"
            :class="[
                'fixed top-4 right-4 z-50 max-w-md rounded-lg p-4 shadow-lg transition-all duration-300',
                toastType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white',
            ]"
        >
            <div class="flex items-start">
                <CheckCircle v-if="toastType === 'success'" class="mt-0.5 mr-2 h-5 w-5 flex-shrink-0" />
                <AlertCircle v-if="toastType === 'error'" class="mt-0.5 mr-2 h-5 w-5 flex-shrink-0" />
                <div class="flex-1">
                    <div class="mb-1 text-sm font-medium">
                        {{ toastType === 'success' ? 'Berhasil!' : 'Error!' }}
                    </div>
                    <div class="text-sm whitespace-pre-line">{{ toastMessage }}</div>
                    <div v-if="toastDetails.length > 0" class="mt-2 text-xs opacity-90">
                        <div class="mb-1 font-medium">Detail:</div>
                        <ul class="list-inside list-disc space-y-1">
                            <li v-for="detail in toastDetails" :key="detail">{{ detail }}</li>
                        </ul>
                    </div>
                </div>
                <button @click="hideToast" class="ml-2 flex-shrink-0">
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, Clock, DollarSign, Eye, FileText, Plus, RotateCcw, TrendingDown, Users, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Payroll {
    id: number;
    user: {
        name: string;
        employee_id: string;
    };
    gross_salary: number;
    total_deductions: number;
    net_salary: number;
    working_days: number;
    actual_working_days: number;
    overtime_hours: number;
}

interface Props {
    payrolls: Payroll[];
    currentYear: number;
    currentMonth: number;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { name: 'Dashboard', href: '/admin/dashboard' },
    { name: 'Payroll', href: '/admin/payrolls' },
];

const selectedYear = ref(props.currentYear);
const selectedMonth = ref(props.currentMonth);
const showGenerateModal = ref(false);
const generating = ref(false);

// Toast state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error'>('success');
const toastDetails = ref<string[]>([]);

const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    return [currentYear - 1, currentYear, currentYear + 1];
});

const totalPayroll = computed(() => {
    return props.payrolls.reduce((sum, p) => sum + Number(p.net_salary || 0), 0);
});

const totalDeductions = computed(() => {
    return props.payrolls.reduce((sum, p) => sum + Number(p.total_deductions || 0), 0);
});

const averagePayroll = computed(() => {
    if (props.payrolls.length === 0) return 0;
    return totalPayroll.value / props.payrolls.length;
});

const getCurrentPeriodName = () => {
    const month = months.find((m) => m.value === selectedMonth.value);
    return `${month?.label} ${selectedYear.value}`;
};

const changeYear = () => {
    router.get('/admin/payrolls', {
        year: selectedYear.value,
        month: selectedMonth.value,
    });
};

const changeMonth = () => {
    router.get('/admin/payrolls', {
        year: selectedYear.value,
        month: selectedMonth.value,
    });
};

// Toast functions
const showToastMessage = (message: string, type: 'success' | 'error', details: string[] = []) => {
    toastMessage.value = message;
    toastType.value = type;
    toastDetails.value = details;
    showToast.value = true;

    // Auto hide after 8 seconds for errors (longer for reading), 5 seconds for success
    setTimeout(
        () => {
            showToast.value = false;
        },
        type === 'error' ? 8000 : 5000,
    );
};

const hideToast = () => {
    showToast.value = false;
    toastDetails.value = [];
};

const confirmGenerate = () => {
    generating.value = true;
    router.post(
        '/admin/payrolls/generate',
        {
            year: selectedYear.value,
            month: selectedMonth.value,
        },
        {
            onSuccess: (page) => {
                generating.value = false;
                showGenerateModal.value = false;
                // Use success message from server if available
                const successMessage = page.props?.flash?.success || 'Payroll berhasil digenerate!';
                showToastMessage(successMessage, 'success');
            },
            onError: (errors, page) => {
                generating.value = false;
                showGenerateModal.value = false;

                // Handle different types of errors
                let errorMessage = 'Terjadi kesalahan saat generate payroll';
                const errorDetails: string[] = [];

                if (errors.validation) {
                    errorMessage = errors.validation;

                    // Extract details from flash data
                    const validationDetails = page?.props?.flash?.validation_details;
                    if (validationDetails) {
                        if (validationDetails.missing_salary) {
                            errorDetails.push(`Belum ada pengaturan gaji: ${validationDetails.missing_salary.join(', ')}`);
                        }
                        if (validationDetails.invalid_salary) {
                            errorDetails.push(`Gaji tidak valid: ${validationDetails.invalid_salary.join(', ')}`);
                        }
                    }
                } else if (errors.error) {
                    errorMessage = errors.error;
                } else {
                    // Handle other error formats
                    const errorValues = Object.values(errors).flat();
                    if (errorValues.length > 0) {
                        errorMessage = errorValues.join(', ');
                    }
                }

                showToastMessage(errorMessage, 'error', errorDetails);
            },
            onFinish: () => {
                generating.value = false;
                showGenerateModal.value = false;
            },
        },
    );
};

const regeneratePayroll = (payroll: Payroll) => {
    if (confirm(`Yakin ingin regenerate payroll untuk ${payroll.user.name}?`)) {
        router.post(
            `/admin/payrolls/${payroll.id}/regenerate`,
            {},
            {
                onSuccess: () => {
                    showToastMessage(`Payroll ${payroll.user.name} berhasil diregenerate!`, 'success');
                },
                onError: (errors) => {
                    const errorMessage = Object.values(errors).flat().join(', ') || 'Terjadi kesalahan saat regenerate payroll';
                    showToastMessage(errorMessage, 'error');
                },
            },
        );
    }
};

const formatCurrency = (amount: number | string | null | undefined) => {
    const numAmount = Number(amount || 0);
    if (isNaN(numAmount)) return 'Rp0';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(numAmount);
};
</script>
