<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detail Payroll - {{ payroll.user.name }}</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ payroll.period_name }} • ID: {{ payroll.user.employee_id }} • {{ formatDate(payroll.created_at) }}
                        </p>
                    </div>
                    <div class="no-print flex space-x-3">
                        <Link
                            :href="route('admin.payrolls.index')"
                            class="inline-flex items-center rounded-lg bg-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-400"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                        <button
                            @click="regeneratePayroll"
                            :disabled="isRegenerating"
                            :class="[
                                'inline-flex items-center rounded-lg px-4 py-2 text-white transition-colors',
                                isRegenerating ? 'cursor-not-allowed bg-orange-400' : 'bg-orange-500 hover:bg-orange-600',
                            ]"
                        >
                            <RotateCcw :class="['mr-2 h-4 w-4', isRegenerating ? 'animate-spin' : '']" />
                            {{ isRegenerating ? 'Regenerating...' : 'Regenerate' }}
                        </button>
                        <button
                            @click="printPayroll"
                            class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-white transition-colors hover:bg-blue-600"
                        >
                            <Printer class="mr-2 h-4 w-4" />
                            Cetak
                        </button>
                    </div>
                </div>
            </div>

            <!-- Salary Summary -->
            <div
                class="no-print mb-8 overflow-hidden rounded-xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 dark:border-green-800 dark:from-green-900/20 dark:to-emerald-900/20"
            >
                <div class="p-8">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="text-center">
                            <h3 class="mb-2 text-lg font-semibold text-green-900 dark:text-green-100">Gaji Kotor</h3>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(payroll.gross_salary) }}</p>
                        </div>
                        <div class="text-center">
                            <h3 class="mb-2 text-lg font-semibold text-red-900 dark:text-red-100">Total Potongan</h3>
                            <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(payroll.total_deductions) }}</p>
                        </div>
                        <div class="text-center">
                            <h3 class="mb-2 text-lg font-semibold text-blue-900 dark:text-blue-100">Gaji Bersih</h3>
                            <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(payroll.net_salary) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Employee Information -->
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
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ payroll.user.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ID Karyawan</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ payroll.user.employee_id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode Gaji</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ payroll.period_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Generate</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(payroll.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Summary -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <Clock class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                                Ringkasan Kehadiran
                            </h3>
                        </div>
                        <div class="p-6">
                            <!-- Desktop Grid View -->
                            <div class="hidden md:block">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-green-50 p-4 text-center dark:bg-green-900/20">
                                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ payroll.actual_working_days }}</p>
                                        <p class="text-sm text-green-700 dark:text-green-300">Hari Masuk</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">dari {{ payroll.working_days }} hari</p>
                                    </div>
                                    <div class="rounded-lg bg-blue-50 p-4 text-center dark:bg-blue-900/20">
                                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ payroll.overtime_hours }}</p>
                                        <p class="text-sm text-blue-700 dark:text-blue-300">Jam Lembur</p>
                                    </div>
                                    <div class="rounded-lg bg-orange-50 p-4 text-center dark:bg-orange-900/20">
                                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ Math.round(payroll.late_minutes) }}</p>
                                        <p class="text-sm text-orange-700 dark:text-orange-300">Menit Telat</p>
                                    </div>
                                    <div class="rounded-lg bg-red-50 p-4 text-center dark:bg-red-900/20">
                                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ payroll.absent_days }}</p>
                                        <p class="text-sm text-red-700 dark:text-red-300">Hari Alfa</p>
                                    </div>
                                </div>

                                <!-- Attendance Percentage -->
                                <div class="mt-6">
                                    <div class="mb-2 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                        <span>Tingkat Kehadiran</span>
                                        <span>{{ attendancePercentage }}%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div
                                            class="h-2 rounded-full transition-all duration-300"
                                            :class="
                                                attendancePercentage >= 90
                                                    ? 'bg-green-500'
                                                    : attendancePercentage >= 80
                                                      ? 'bg-yellow-500'
                                                      : 'bg-red-500'
                                            "
                                            :style="{ width: attendancePercentage + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Print Table View -->
                            <div class="md:hidden print:block">
                                <table class="w-full border-collapse border border-gray-300">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Keterangan</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right font-semibold">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">Hari Kerja</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ payroll.working_days }} hari</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">Hari Masuk</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">
                                                {{ payroll.actual_working_days }} hari
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">Jam Lembur</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ payroll.overtime_hours }} jam</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">Menit Telat</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">
                                                {{ Math.round(payroll.late_minutes) }} menit
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2">Hari Alfa</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ payroll.absent_days }} hari</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2 font-semibold">Tingkat Kehadiran</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-bold">{{ attendancePercentage }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Salary Breakdown -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <DollarSign class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                                Breakdown Penghasilan
                            </h3>
                        </div>
                        <div class="p-6">
                            <!-- Base Salary -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Gaji Pokok</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(payroll.base_salary) }}</span>
                                </div>

                                <!-- Allowances -->
                                <div v-if="allowanceDetails.length > 0">
                                    <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tunjangan:</div>
                                    <div v-for="allowance in allowanceDetails" :key="allowance.id" class="flex items-center justify-between pl-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">• {{ allowance.name }}</span>
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400"
                                            >+{{ formatCurrency(allowance.amount) }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Overtime -->
                                <div v-if="overtimeDetails.length > 0">
                                    <div v-for="overtime in overtimeDetails" :key="overtime.id" class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ overtime.name }}</span>
                                        <span class="font-medium text-green-600 dark:text-green-400">+{{ formatCurrency(overtime.amount) }}</span>
                                    </div>
                                </div>

                                <hr class="border-gray-200 dark:border-gray-700" />

                                <div class="flex items-center justify-between font-semibold">
                                    <span class="text-gray-900 dark:text-white">Total Penghasilan</span>
                                    <span class="text-green-600 dark:text-green-400">{{ formatCurrency(payroll.gross_salary) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deductions -->
                    <div
                        v-if="deductionDetails.length > 0"
                        class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <Minus class="mr-2 h-5 w-5 text-red-600 dark:text-red-400" />
                                Breakdown Potongan
                            </h3>
                        </div>
                        <div class="space-y-3 p-6">
                            <div v-for="deduction in deductionDetails" :key="deduction.id" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">{{ deduction.name }}</span>
                                        <p v-if="deduction.description" class="text-xs text-gray-500 dark:text-gray-500">
                                            {{ deduction.description }}
                                        </p>
                                    </div>
                                    <span class="font-medium text-red-600 dark:text-red-400">-{{ formatCurrency(deduction.amount) }}</span>
                                </div>

                                <!-- Show calculation details for attendance-based deductions -->
                                <div
                                    v-if="deduction.calculation_basis && (deduction.name.includes('Telat') || deduction.name.includes('Tidak Masuk'))"
                                    class="ml-4 rounded-lg bg-gray-50 p-2 text-xs dark:bg-gray-800"
                                >
                                    <div class="text-gray-600 dark:text-gray-400">
                                        <!-- Late deduction calculation -->
                                        <div v-if="deduction.calculation_basis.minutes !== undefined">
                                            <span
                                                >Detail: {{ deduction.calculation_basis.minutes }} menit × Rp
                                                {{
                                                    formatCurrency(deduction.calculation_basis.rate || 0)
                                                        .replace('Rp', '')
                                                        .replace(/\D/g, '')
                                                }}/menit</span
                                            >
                                            <div v-if="deduction.calculation_basis.hours" class="text-gray-500 dark:text-gray-500">
                                                ({{ deduction.calculation_basis.hours }} jam)
                                            </div>
                                        </div>

                                        <!-- Absent deduction calculation -->
                                        <div v-else-if="deduction.calculation_basis.days !== undefined">
                                            <span
                                                >Detail: {{ deduction.calculation_basis.days }} hari × Rp
                                                {{
                                                    formatCurrency(deduction.calculation_basis.rate || 0)
                                                        .replace('Rp', '')
                                                        .replace(/\D/g, '')
                                                }}/hari</span
                                            >
                                        </div>

                                        <!-- Percentage deduction calculation -->
                                        <div v-else-if="deduction.calculation_basis.method === 'percentage'">
                                            <span
                                                >Detail: {{ deduction.calculation_basis.rate }}% × Rp
                                                {{
                                                    formatCurrency(deduction.calculation_basis.base_salary || 0)
                                                        .replace('Rp', '')
                                                        .replace(/\D/g, '')
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700" />

                            <div class="flex items-center justify-between font-semibold">
                                <span class="text-gray-900 dark:text-white">Total Potongan</span>
                                <span class="text-red-600 dark:text-red-400">-{{ formatCurrency(payroll.total_deductions) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final Summary -->
            <div class="mt-8 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-8">
                    <div class="flex items-center justify-between text-xl font-bold">
                        <span class="text-gray-900 dark:text-white">TOTAL GAJI BERSIH</span>
                        <span class="text-3xl text-green-600 dark:text-green-400">{{ formatCurrency(payroll.net_salary) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">* Gaji sudah dipotong sesuai aturan yang berlaku</p>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div
            v-if="showToast"
            :class="[
                'no-print fixed top-4 right-4 z-[9999] flex min-w-[300px] items-center rounded-lg border-2 p-4 shadow-xl transition-all duration-300',
                toastType === 'success' ? 'border-green-600 bg-green-500 text-white' : 'border-red-600 bg-red-500 text-white',
            ]"
            style="z-index: 9999"
        >
            <CheckCircle v-if="toastType === 'success'" class="mr-3 h-5 w-5 flex-shrink-0" />
            <AlertCircle v-if="toastType === 'error'" class="mr-3 h-5 w-5 flex-shrink-0" />
            <span class="flex-1 font-medium">{{ toastMessage }}</span>
            <button @click="hideToast" class="ml-3 rounded p-1 hover:bg-black/20">
                <X class="h-4 w-4" />
            </button>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, CheckCircle, Clock, DollarSign, Minus, Printer, RotateCcw, User, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface PayrollDetail {
    id: number;
    type: 'allowance' | 'deduction' | 'overtime';
    name: string;
    description: string | null;
    amount: number;
    calculation_basis?: {
        minutes?: number;
        hours?: number;
        days?: number;
        method?: string;
        rate?: number;
        base_salary?: number;
        rule_id?: number;
    };
}

interface Payroll {
    id: number;
    user: {
        name: string;
        employee_id: string;
    };
    period_name: string;
    period_year: number;
    period_month: number;
    base_salary: number;
    gross_salary: number;
    total_deductions: number;
    net_salary: number;
    working_days: number;
    actual_working_days: number;
    overtime_hours: number;
    late_minutes: number;
    absent_days: number;
    created_at: string;
    details: PayrollDetail[];
}

interface Props {
    payroll: Payroll;
}

const props = defineProps<Props>();

// Toast state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error'>('success');

// Loading state
const isRegenerating = ref(false);
const lastClickTime = ref(0);

const breadcrumbs = [
    { name: 'Dashboard', href: '/admin/dashboard' },
    { name: 'Payroll', href: '/admin/payrolls' },
    { name: props.payroll.user.name, href: `/admin/payrolls/${props.payroll.id}` },
];

const allowanceDetails = computed(() => {
    return props.payroll.details.filter((detail) => detail.type === 'allowance');
});

const deductionDetails = computed(() => {
    return props.payroll.details.filter((detail) => detail.type === 'deduction');
});

const overtimeDetails = computed(() => {
    return props.payroll.details.filter((detail) => detail.type === 'overtime');
});

const attendancePercentage = computed(() => {
    if (props.payroll.working_days === 0) return 0;
    return Math.round((props.payroll.actual_working_days / props.payroll.working_days) * 100);
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

// Toast functions
const showToastMessage = (message: string, type: 'success' | 'error') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;

    console.log('Toast shown:', { message, type, show: showToast.value });

    // Auto hide after 5 seconds
    setTimeout(() => {
        showToast.value = false;
    }, 5000);
};

const hideToast = () => {
    showToast.value = false;
};

const regeneratePayroll = () => {
    // Prevent double-click and rapid clicks
    const now = Date.now();
    if (isRegenerating.value || now - lastClickTime.value < 2000) {
        console.log('Request blocked - too frequent or already processing');
        return;
    }
    lastClickTime.value = now;

    if (confirm(`Yakin ingin regenerate payroll untuk ${props.payroll.user.name}? Data lama akan diganti dengan perhitungan terbaru.`)) {
        isRegenerating.value = true;

        // Set timeout to prevent infinite loading
        const timeoutId = setTimeout(() => {
            if (isRegenerating.value) {
                isRegenerating.value = false;
                showToastMessage('Request timeout - coba lagi nanti', 'error');
            }
        }, 30000); // 30 seconds timeout

        router.post(
            route('admin.payrolls.regenerate', props.payroll.id),
            {},
            {
                preserveState: false,
                preserveScroll: true,
                onBefore: () => {
                    console.log('Starting regenerate request...');
                    return true;
                },
                onStart: () => {
                    console.log('Request started');
                },
                onProgress: (progress) => {
                    console.log('Progress:', progress);
                },
                onSuccess: (page) => {
                    clearTimeout(timeoutId);
                    console.log('Regenerate success:', page);
                    isRegenerating.value = false;

                    // Check for flash messages
                    if (page.props.flash?.success) {
                        showToastMessage(page.props.flash.success, 'success');
                    } else {
                        showToastMessage(`Payroll ${props.payroll.user.name} berhasil diregenerate!`, 'success');
                    }
                },
                onError: (errors) => {
                    clearTimeout(timeoutId);
                    console.error('Regenerate error:', errors);
                    isRegenerating.value = false;

                    let errorMessage = 'Terjadi kesalahan saat regenerate payroll';

                    // Check for validation errors
                    if (errors.error) {
                        errorMessage = errors.error;
                    } else if (Object.keys(errors).length > 0) {
                        errorMessage = Object.values(errors).flat().join(', ');
                    }

                    showToastMessage(errorMessage, 'error');
                },
                onFinish: () => {
                    clearTimeout(timeoutId);
                    isRegenerating.value = false;
                    console.log('Request finished');
                },
            },
        );
    }
};

const printPayroll = () => {
    window.print();
};
</script>

<style>
@media print {
    /* Hide elements that shouldn't be printed */
    .no-print,
    nav,
    .breadcrumb,
    button,
    .flex.space-x-3,
    .bg-gradient-to-r {
        display: none !important;
    }

    /* Reset everything to black and white */
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        color: #000 !important;
        background: white !important;
        border-color: #000 !important;
    }

    body {
        font-family: 'Times New Roman', serif !important;
        font-size: 12px;
        line-height: 1.5;
        color: #000 !important;
        background: white !important;
        margin: 0;
        padding: 0;
    }

    /* Page layout */
    .mx-auto {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
    }

    /* Header styling - slip gaji style */
    h1 {
        font-size: 16px !important;
        font-weight: bold !important;
        color: #000 !important;
        margin: 0 0 20px 0 !important;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Salary summary - traditional slip format */
    .mb-8.overflow-hidden.rounded-xl {
        border: 2px solid #000 !important;
        border-radius: 0 !important;
        margin: 20px 0 !important;
        background: white !important;
    }

    .p-8 {
        padding: 15px !important;
        text-align: center;
    }

    .grid.grid-cols-1.md\\:grid-cols-3 {
        display: block !important;
        text-align: center;
    }

    .grid.grid-cols-1.md\\:grid-cols-3 > div {
        display: inline-block;
        width: 32%;
        margin: 0 0.5%;
        vertical-align: top;
        border: 1px solid #000;
        padding: 10px 5px;
        margin-bottom: 10px;
    }

    /* Remove all background colors and gradients */
    .bg-gradient-to-r,
    .from-green-50,
    .to-emerald-50,
    .border-green-200,
    .dark\\:from-green-900\\/20,
    .dark\\:to-emerald-900\\/20,
    .dark\\:border-green-800 {
        background: white !important;
        border-color: #000 !important;
    }

    /* Cards styling - simple black borders */
    .overflow-hidden {
        border: 1px solid #000 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin-bottom: 15px !important;
        background: white !important;
    }

    .px-6.py-4 {
        background: white !important;
        border-bottom: 1px solid #000 !important;
        padding: 8px 12px !important;
    }

    .text-lg.font-semibold {
        font-size: 14px !important;
        font-weight: bold !important;
        color: #000 !important;
        text-transform: uppercase;
    }

    .p-6 {
        padding: 12px !important;
    }

    /* Table styling - simple lines */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 10px 0 !important;
        border: 1px solid #000 !important;
    }

    th,
    td {
        border: 1px solid #000 !important;
        padding: 8px !important;
        text-align: left !important;
        font-size: 11px !important;
        color: #000 !important;
        background: white !important;
    }

    th {
        font-weight: bold !important;
        text-align: center !important;
        text-transform: uppercase;
    }

    /* Grid layout adjustments */
    .grid {
        display: block !important;
    }

    .grid.grid-cols-1.lg\\:grid-cols-2 > div {
        margin-bottom: 15px !important;
        display: block !important;
        width: 100% !important;
    }

    .grid.grid-cols-2 {
        display: block !important;
    }

    .grid.grid-cols-2 > div {
        display: inline-block !important;
        width: 48% !important;
        margin-right: 2% !important;
        vertical-align: top !important;
    }

    /* Text styling - all black */
    .text-sm,
    .text-lg,
    .text-3xl,
    .text-4xl {
        color: #000 !important;
    }

    .text-sm {
        font-size: 10px !important;
    }

    .text-lg {
        font-size: 12px !important;
    }

    .text-3xl,
    .text-4xl {
        font-size: 16px !important;
        font-weight: bold !important;
    }

    /* Remove all colored text */
    .text-green-900,
    .text-green-600,
    .text-red-900,
    .text-red-600,
    .text-blue-900,
    .text-blue-600,
    .text-gray-500,
    .text-gray-600,
    .text-gray-900,
    .dark\\:text-green-100,
    .dark\\:text-green-400,
    .dark\\:text-red-100,
    .dark\\:text-red-400,
    .dark\\:text-blue-100,
    .dark\\:text-blue-400,
    .dark\\:text-gray-400,
    .dark\\:text-white {
        color: #000 !important;
    }

    /* Icons - hide completely */
    svg {
        display: none !important;
    }

    /* Currency and numbers */
    .font-bold {
        font-weight: bold !important;
    }

    /* Remove all spacing that might cause issues */
    .space-y-4 > * + *,
    .space-y-6 > * + * {
        margin-top: 10px !important;
    }

    .mb-8 {
        margin-bottom: 15px !important;
    }

    .mt-1 {
        margin-top: 5px !important;
    }

    /* Slip header info */
    .mt-1.text-gray-600 {
        font-size: 11px !important;
        color: #000 !important;
        text-align: center !important;
        margin-bottom: 15px !important;
    }

    /* Print Table Specific Styles */
    .print\\:block {
        display: block !important;
    }

    .hidden.md\\:block {
        display: none !important;
    }

    /* Force table visibility in print */
    .md\\:hidden.print\\:block {
        display: block !important;
    }

    /* Page breaks */
    .print-break {
        page-break-before: always;
    }
}
</style>
