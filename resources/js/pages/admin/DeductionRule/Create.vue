<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Tambah Aturan Potongan
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              Buat aturan potongan baru untuk gaji karyawan
            </p>
          </div>
          <Link
            :href="route('admin.deduction-rules.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
          >
            <ArrowLeft class="w-4 h-4 mr-2" />
            Kembali
          </Link>
        </div>
      </div>

      <!-- Form -->
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <Settings class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
            Informasi Aturan Potongan
          </h3>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Nama Aturan <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
              placeholder="Contoh: Potongan Telat"
              required
            />
            <div v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.name }}
            </div>
          </div>

          <!-- Type -->
          <div>
            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tipe Potongan <span class="text-red-500">*</span>
            </label>
            <select
              id="type"
              v-model="form.type"
              class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
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
            <label for="calculation_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Metode Perhitungan <span class="text-red-500">*</span>
            </label>
            <select
              id="calculation_method"
              v-model="form.calculation_method"
              class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
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
            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ getAmountLabel() }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <div v-if="form.calculation_method !== 'percentage'" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
              </div>
              <input
                id="amount"
                v-model="form.amount"
                type="number"
                min="0"
                :step="form.calculation_method === 'percentage' ? '0.1' : '1000'"
                :class="[
                  'block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white',
                  form.calculation_method !== 'percentage' ? 'pl-12 pr-3' : 'pr-8'
                ]"
                placeholder="0"
                required
              />
              <div v-if="form.calculation_method === 'percentage'" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">%</span>
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
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Deskripsi
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
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
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded dark:bg-gray-800 dark:border-gray-600"
            />
            <label for="is_active" class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
              Aktifkan aturan ini
            </label>
          </div>

          <!-- Preview -->
          <div v-if="form.type && form.calculation_method && form.amount" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Preview Perhitungan</h4>
            <p class="text-sm text-blue-700 dark:text-blue-300">
              {{ getPreviewText() }}
            </p>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3">
            <Link
              :href="route('admin.deduction-rules.index')"
              class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ArrowLeft, Settings } from 'lucide-vue-next'

interface Props {
  errors: Record<string, string>
}

const props = defineProps<Props>()

const breadcrumbs = [
  { name: 'Dashboard', href: route('admin.dashboard') },
  { name: 'Aturan Potongan', href: route('admin.deduction-rules.index') },
  { name: 'Tambah Aturan', href: route('admin.deduction-rules.create') },
]

const form = useForm({
  name: '',
  type: '',
  calculation_method: '',
  amount: 0,
  description: '',
  is_active: true,
})

const processing = ref(false)

const getAmountLabel = () => {
  switch (form.calculation_method) {
    case 'fixed':
      return 'Jumlah Tetap'
    case 'per_minute':
      return 'Jumlah per Menit'
    case 'per_hour':
      return 'Jumlah per Jam'
    case 'per_day':
      return 'Jumlah per Hari'
    case 'percentage':
      return 'Persentase'
    default:
      return 'Jumlah'
  }
}

const getAmountHelper = () => {
  switch (form.calculation_method) {
    case 'fixed':
      return 'Jumlah tetap yang akan dipotong'
    case 'per_minute':
      return 'Jumlah yang dipotong untuk setiap menit (contoh: telat)'
    case 'per_hour':
      return 'Jumlah yang dipotong untuk setiap jam'
    case 'per_day':
      return 'Jumlah yang dipotong untuk setiap hari (contoh: tidak masuk)'
    case 'percentage':
      return 'Persentase dari gaji pokok yang akan dipotong'
    default:
      return ''
  }
}

const getPreviewText = () => {
  const typeLabels = {
    late: 'telat',
    absent: 'tidak masuk',
    excess_leave: 'cuti berlebih',
    other: 'lainnya'
  }

  const typeName = typeLabels[form.type] || form.type

  switch (form.calculation_method) {
    case 'fixed':
      return `Setiap kali ${typeName}, akan dipotong ${formatCurrency(form.amount)}`
    case 'per_minute':
      return `Setiap menit ${typeName}, akan dipotong ${formatCurrency(form.amount)}`
    case 'per_hour':
      return `Setiap jam ${typeName}, akan dipotong ${formatCurrency(form.amount)}`
    case 'per_day':
      return `Setiap hari ${typeName}, akan dipotong ${formatCurrency(form.amount)}`
    case 'percentage':
      return `Setiap kali ${typeName}, akan dipotong ${form.amount}% dari gaji pokok`
    default:
      return ''
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const submit = () => {
  processing.value = true
  form.post(route('admin.deduction-rules.store'), {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>