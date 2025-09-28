<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Pengaturan Gaji - {{ user.name }}
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              ID: {{ user.employee_id }} • {{ user.department?.name }} • {{ user.position?.name }}
            </p>
          </div>
          <Link
            :href="salarySettingsIndex.url()"
            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
          >
            <ArrowLeft class="w-4 h-4 mr-2" />
            Kembali
          </Link>
        </div>
      </div>

      <!-- Current Setting Info -->
      <div v-if="currentSetting" class="mb-8 overflow-hidden rounded-xl bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800">
        <div class="p-6">
          <div class="flex items-center mb-4">
            <InfoIcon class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" />
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Pengaturan Saat Ini</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <p class="text-sm text-blue-700 dark:text-blue-300">Gaji Pokok</p>
              <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ formatCurrency(currentSetting.base_salary) }}</p>
            </div>
            <div>
              <p class="text-sm text-blue-700 dark:text-blue-300">Total Tunjangan</p>
              <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ formatCurrency(currentSetting.total_allowances || 0) }}</p>
            </div>
            <div>
              <p class="text-sm text-blue-700 dark:text-blue-300">Rate Lembur</p>
              <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ currentSetting.overtime_rate }}x</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <DollarSign class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
            {{ currentSetting ? 'Perbarui Pengaturan Gaji' : 'Atur Gaji Baru' }}
          </h3>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Basic Salary -->
          <div>
            <label for="base_salary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Gaji Pokok <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
              </div>
              <input
                id="base_salary"
                v-model="form.base_salary"
                type="number"
                min="0"
                step="1000"
                class="block w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                placeholder="0"
                required
              />
            </div>
            <div v-if="errors.base_salary" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.base_salary }}
            </div>
          </div>

          <!-- Overtime Rate -->
          <div>
            <label for="overtime_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Rate Lembur <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                id="overtime_rate"
                v-model="form.overtime_rate"
                type="number"
                min="1"
                max="5"
                step="0.1"
                class="block w-full pr-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                placeholder="1.5"
                required
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">x</span>
              </div>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Multiplier untuk perhitungan lembur (contoh: 1.5 = 1.5x gaji normal)
            </p>
            <div v-if="errors.overtime_rate" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.overtime_rate }}
            </div>
          </div>

          <!-- Effective Date -->
          <div>
            <label for="effective_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tanggal Berlaku <span class="text-red-500">*</span>
            </label>
            <DatePicker
              v-model="form.effective_date"
              placeholder="Pilih tanggal berlaku"
              class="w-full"
              required
            />
            <div v-if="errors.effective_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.effective_date }}
            </div>
          </div>

          <!-- Allowances -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tunjangan
              </label>
              <button
                type="button"
                @click="addAllowance"
                class="inline-flex items-center px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
              >
                <Plus class="w-4 h-4 mr-1" />
                Tambah Tunjangan
              </button>
            </div>

            <div class="space-y-3">
              <div
                v-for="(allowance, index) in form.allowances"
                :key="index"
                class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
              >
                <div class="flex-1">
                  <input
                    v-model="allowance.name"
                    type="text"
                    placeholder="Nama tunjangan (contoh: Transport)"
                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    required
                  />
                </div>
                <div class="w-40">
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 dark:text-gray-400 text-sm">Rp</span>
                    </div>
                    <input
                      v-model="allowance.amount"
                      type="number"
                      min="0"
                      step="1000"
                      placeholder="0"
                      class="block w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                      required
                    />
                  </div>
                </div>
                <button
                  type="button"
                  @click="removeAllowance(index)"
                  class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>

              <div v-if="form.allowances.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                <Gift class="w-8 h-8 mx-auto mb-2 opacity-50" />
                <p>Belum ada tunjangan yang ditambahkan</p>
              </div>
            </div>

            <div v-if="errors.allowances" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.allowances }}
            </div>
          </div>

          <!-- Total Summary -->
          <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Ringkasan</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Gaji Pokok:</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(form.base_salary || 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Tunjangan:</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(totalAllowances) }}</span>
              </div>
              <div class="border-t border-gray-200 dark:border-gray-700 pt-2">
                <div class="flex justify-between">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Gaji Kotor:</span>
                  <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalGrossSalary) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3">
            <Link
              :href="salarySettingsIndex.url()"
              class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { DatePicker } from '@/components/ui/date-picker'
import { ArrowLeft, DollarSign, Plus, Trash2, Gift, Info as InfoIcon } from 'lucide-vue-next'
import { dashboard } from '@/routes/admin'
import { index as salarySettingsIndex, edit, update } from '@/routes/admin/salary-settings'

interface Allowance {
  name: string
  amount: number
}

interface SalarySetting {
  id: number
  base_salary: number
  allowances: Allowance[]
  overtime_rate: number
  effective_date: string
  total_allowances: number
}

interface User {
  id: number
  name: string
  employee_id: string
  department: { name: string } | null
  position: { name: string } | null
}

interface Props {
  user: User
  currentSetting: SalarySetting | null
  errors: Record<string, string>
}

const props = defineProps<Props>()

const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Pengaturan Gaji', href: '/admin/salary-settings' },
  { title: props.user.name, href: `/admin/salary-settings/${props.user.id}` },
  { title: 'Edit', href: `/admin/salary-settings/${props.user.id}/edit` },
]

// Convert allowances object to array format for editing
const convertAllowancesToArray = (allowances: any) => {
  if (!allowances) return []
  if (Array.isArray(allowances)) return allowances

  // Convert object format {transport: 500000, meal: 300000} to array format
  return Object.entries(allowances).map(([name, amount]) => ({
    name,
    amount: Number(amount)
  }))
}

const form = useForm({
  base_salary: props.currentSetting?.base_salary || 0,
  allowances: convertAllowancesToArray(props.currentSetting?.allowances),
  overtime_rate: props.currentSetting?.overtime_rate || 1.5,
  effective_date: new Date().toISOString().split('T')[0],
})

const processing = ref(false)

const totalAllowances = computed(() => {
  return form.allowances.reduce((sum, allowance) => sum + (allowance.amount || 0), 0)
})

const totalGrossSalary = computed(() => {
  return (form.base_salary || 0) + totalAllowances.value
})

const addAllowance = () => {
  form.allowances.push({ name: '', amount: 0 })
}

const removeAllowance = (index: number) => {
  form.allowances.splice(index, 1)
}

const submit = () => {
  processing.value = true

  // Convert allowances array back to object format for server
  const allowancesObject = form.allowances.reduce((acc: any, allowance: any) => {
    if (allowance.name && allowance.amount) {
      acc[allowance.name] = allowance.amount
    }
    return acc
  }, {})

  // Create data to send with converted allowances
  const formData = {
    base_salary: form.base_salary,
    allowances: allowancesObject,
    overtime_rate: form.overtime_rate,
    effective_date: form.effective_date
  }

  form.transform((data) => formData).put(update.url(props.user.id), {
    onFinish: () => {
      processing.value = false
    }
  })
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}
</script>