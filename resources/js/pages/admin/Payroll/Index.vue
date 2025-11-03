<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Payroll</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Generate dan kelola payroll karyawan
                            <span
                                v-if="activePeriod"
                                class="ml-2 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"
                            >
                                <Calendar class="mr-1 h-3 w-3" />
                                {{ activePeriod.formatted_period }}
                            </span>
                        </p>
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
                        <Link
                            :href="route('admin.payroll-periods.index')"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <Calendar class="mr-2 h-4 w-4" />
                            Periode Gaji
                        </Link>
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
            <div class="mb-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Payroll -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <DollarSign class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Payroll</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total gaji bersih periode ini</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Total</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ formatCurrency(totalPayroll) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ payrolls.length }}</span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Total Deductions -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-orange-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-orange-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 ring-1 ring-orange-500/20">
                                <TrendingDown class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Potongan</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total potongan periode ini</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-orange-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-orange-50/50 px-3 py-2 dark:bg-orange-950/30">
                            <span class="text-sm font-medium text-orange-700 dark:text-orange-400">Potongan</span>
                            <span class="text-sm font-semibold text-orange-800 dark:text-orange-300">{{ formatCurrency(totalDeductions) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ totalPayroll > 0 ? Math.round((totalDeductions / (totalPayroll + totalDeductions)) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-orange-500/5 to-red-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Average Salary -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Rata-rata Gaji</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Rata-rata gaji bersih</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Rata-rata</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ formatCurrency(averagePayroll) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ payrolls.length }}</span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Payroll Status -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/10 ring-1 ring-purple-500/20">
                                <FileText class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Status Payroll</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Status payroll periode ini</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-purple-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-purple-50/50 px-3 py-2 dark:bg-purple-950/30">
                            <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Status</span>
                            <span class="text-sm font-semibold text-purple-800 dark:text-purple-300">
                                {{ payrolls.length > 0 ? 'Generated' : 'Pending' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Periode</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ getCurrentPeriodName() }}</span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Payroll List -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <FileText class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Payroll</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Payroll {{ getCurrentPeriodName() }} • {{ payrolls.length }} karyawan
                            </p>
                        </div>
                    </div>
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
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Karyawan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Gaji Kotor</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Potongan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Gaji Bersih</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Kehadiran</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="payroll in payrolls"
                                :key="payroll.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ payroll.user?.name || 'Unknown User' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ payroll.user?.employee_id || 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(payroll.gross_salary) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                        -{{ formatCurrency(payroll.total_deductions) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                        {{ formatCurrency(payroll.net_salary) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ payroll.actual_working_days }}/{{ payroll.working_days }} hari
                                        <div v-if="payroll.overtime_hours > 0" class="text-xs text-blue-600 dark:text-blue-400">
                                            +{{ payroll.overtime_hours }}h lembur
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="route('admin.payrolls.show', payroll.id)"
                                            class="relative z-10 rounded-lg bg-blue-100 p-2 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="regeneratePayroll(payroll)"
                                            class="relative z-10 rounded-lg bg-orange-100 p-2 text-orange-600 hover:bg-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-900/50"
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

                <!-- Hover effect overlay -->
                <div
                    class="pointer-events-none absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
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
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { AlertCircle, Calendar, CheckCircle, Clock, DollarSign, Eye, FileText, Plus, RotateCcw, TrendingDown, X } from 'lucide-vue-next';
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
    activePeriod?: {
        id: number;
        name: string;
        formatted_period: string;
        is_active: boolean;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Payroll', href: '/admin/payrolls' },
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
    router.get(route('admin.payrolls.index'), {
        year: selectedYear.value,
        month: selectedMonth.value,
    });
};

const changeMonth = () => {
    router.get(route('admin.payrolls.index'), {
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
        route('admin.payrolls.generate'),
        {
            year: selectedYear.value,
            month: selectedMonth.value,
        },
        {
            onSuccess: (page) => {
                generating.value = false;
                showGenerateModal.value = false;
                // Use success message from server if available
                const successMessage = (page.props.flash as any)?.success || 'Payroll berhasil digenerate!';
                showToastMessage(successMessage, 'success');
            },
            onError: (errors: any) => {
                generating.value = false;
                showGenerateModal.value = false;

                // Handle different types of errors
                let errorMessage = 'Terjadi kesalahan saat generate payroll';
                const errorDetails: string[] = [];

                if (errors.validation) {
                    errorMessage = errors.validation;
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
    if (confirm(`Yakin ingin regenerate payroll untuk ${payroll.user?.name || 'Unknown User'}?`)) {
        router.post(
            route('admin.payrolls.regenerate', payroll.id),
            {},
            {
                onSuccess: () => {
                    showToastMessage(`Payroll ${payroll.user?.name || 'Unknown User'} berhasil diregenerate!`, 'success');
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
