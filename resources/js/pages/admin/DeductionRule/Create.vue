<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Aturan Potongan</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Buat aturan potongan baru untuk gaji karyawan</p>
                    </div>
                    <Link
                        :href="deductionRulesIndex.url()"
                        class="inline-flex items-center rounded-lg bg-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-400"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Settings class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Informasi Aturan Potongan
                    </h3>
                </div>

                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Aturan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Contoh: Potongan Telat"
                            required
                        />
                        <div v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.name }}
                        </div>
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tipe Potongan <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="">Pilih tipe potongan</option>
                            <option value="late">Telat</option>
                            <option value="absent">Tidak Masuk</option>
                            <option value="excess_leave">Cuti Berlebih</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <div v-if="errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.type }}
                        </div>
                    </div>

                    <!-- Calculation Method -->
                    <div>
                        <label for="calculation_method" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Metode Perhitungan <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="calculation_method"
                            v-model="form.calculation_method"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="">Pilih metode perhitungan</option>
                            <option value="fixed">Jumlah Tetap</option>
                            <option value="per_minute">Per Menit</option>
                            <option value="per_hour">Per Jam</option>
                            <option value="per_day">Per Hari</option>
                            <option value="percentage">Persentase dari Gaji</option>
                        </select>
                        <div v-if="errors.calculation_method" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.calculation_method }}
                        </div>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ getAmountLabel() }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div
                                v-if="form.calculation_method !== 'percentage'"
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                            >
                                <span class="text-gray-500 sm:text-sm dark:text-gray-400">Rp</span>
                            </div>
                            <input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                min="0"
                                :step="form.calculation_method === 'percentage' ? '0.1' : '1000'"
                                :class="[
                                    'block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white',
                                    form.calculation_method !== 'percentage' ? 'pr-3 pl-12' : 'pr-8',
                                ]"
                                placeholder="0"
                                required
                            />
                            <div
                                v-if="form.calculation_method === 'percentage'"
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
                            >
                                <span class="text-gray-500 sm:text-sm dark:text-gray-400">%</span>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ getAmountHelper() }}
                        </p>
                        <div v-if="errors.amount" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.amount }}
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Deskripsi </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Deskripsi optional untuk aturan ini..."
                        ></textarea>
                        <div v-if="errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.description }}
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                        />
                        <label for="is_active" class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300"> Aktifkan aturan ini </label>
                    </div>

                    <!-- Preview -->
                    <div v-if="form.type && form.calculation_method && form.amount" class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <h4 class="mb-2 text-sm font-medium text-blue-900 dark:text-blue-100">Preview Perhitungan</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            {{ getPreviewText() }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="deductionRulesIndex.url()"
                            class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-400"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="rounded-lg bg-blue-500 px-6 py-2 text-white transition-colors hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ processing ? 'Menyimpan...' : 'Simpan Aturan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { create, index as deductionRulesIndex, store } from '@/routes/admin/deduction-rules';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Settings } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    errors: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { name: 'Dashboard', href: dashboard.url() },
    { name: 'Aturan Potongan', href: deductionRulesIndex.url() },
    { name: 'Tambah Aturan', href: create.url() },
];

const form = useForm({
    name: '',
    type: '',
    calculation_method: '',
    amount: 0,
    description: '',
    is_active: true,
});

const processing = ref(false);

const getAmountLabel = () => {
    switch (form.calculation_method) {
        case 'fixed':
            return 'Jumlah Tetap';
        case 'per_minute':
            return 'Jumlah per Menit';
        case 'per_hour':
            return 'Jumlah per Jam';
        case 'per_day':
            return 'Jumlah per Hari';
        case 'percentage':
            return 'Persentase';
        default:
            return 'Jumlah';
    }
};

const getAmountHelper = () => {
    switch (form.calculation_method) {
        case 'fixed':
            return 'Jumlah tetap yang akan dipotong';
        case 'per_minute':
            return 'Jumlah yang dipotong untuk setiap menit (contoh: telat)';
        case 'per_hour':
            return 'Jumlah yang dipotong untuk setiap jam';
        case 'per_day':
            return 'Jumlah yang dipotong untuk setiap hari (contoh: tidak masuk)';
        case 'percentage':
            return 'Persentase dari gaji pokok yang akan dipotong';
        default:
            return '';
    }
};

const getPreviewText = () => {
    const typeLabels = {
        late: 'telat',
        absent: 'tidak masuk',
        excess_leave: 'cuti berlebih',
        other: 'lainnya',
    };

    const typeName = typeLabels[form.type] || form.type;

    switch (form.calculation_method) {
        case 'fixed':
            return `Setiap kali ${typeName}, akan dipotong ${formatCurrency(form.amount)}`;
        case 'per_minute':
            return `Setiap menit ${typeName}, akan dipotong ${formatCurrency(form.amount)}`;
        case 'per_hour':
            return `Setiap jam ${typeName}, akan dipotong ${formatCurrency(form.amount)}`;
        case 'per_day':
            return `Setiap hari ${typeName}, akan dipotong ${formatCurrency(form.amount)}`;
        case 'percentage':
            return `Setiap kali ${typeName}, akan dipotong ${form.amount}% dari gaji pokok`;
        default:
            return '';
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const submit = () => {
    processing.value = true;
    form.post(store.url(), {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
