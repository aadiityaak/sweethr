<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Detail Payroll - {{ payroll.user.name }}
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              {{ payroll.period_name }} • ID: {{ payroll.user.employee_id }} • {{ formatDate(payroll.created_at) }}
            </p>
          </div>
          <div class="flex space-x-3">
            <Link
              :href="route('admin.payrolls.index')"
              class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Link>
            <button
              @click="regeneratePayroll"
              class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors"
            >
              <RotateCcw class="w-4 h-4 mr-2" />
              Regenerate
            </button>
            <button
              @click="printPayroll"
              class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
            >
              <Printer class="w-4 h-4 mr-2" />
              Cetak
            </button>
          </div>
        </div>
      </div>

      <!-- Salary Summary -->
      <div class="mb-8 overflow-hidden rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 dark:from-green-900/20 dark:to-emerald-900/20 dark:border-green-800">
        <div class="p-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Gaji Kotor</h3>
              <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(payroll.gross_salary) }}</p>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">Total Potongan</h3>
              <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(payroll.total_deductions) }}</p>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Gaji Bersih</h3>
              <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(payroll.net_salary) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column -->
        <div class="space-y-6">
          <!-- Employee Information -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <User class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                Informasi Karyawan
              </h3>
            </div>
            <div class="p-6 space-y-4">
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
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <Clock class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                Ringkasan Kehadiran
              </h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                  <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ payroll.actual_working_days }}</p>
                  <p class="text-sm text-green-700 dark:text-green-300">Hari Masuk</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">dari {{ payroll.working_days }} hari</p>
                </div>
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                  <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ payroll.overtime_hours }}</p>
                  <p class="text-sm text-blue-700 dark:text-blue-300">Jam Lembur</p>
                </div>
                <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                  <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ Math.round(payroll.late_minutes) }}</p>
                  <p class="text-sm text-orange-700 dark:text-orange-300">Menit Telat</p>
                </div>
                <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                  <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ payroll.absent_days }}</p>
                  <p class="text-sm text-red-700 dark:text-red-300">Hari Alfa</p>
                </div>
              </div>

              <!-- Attendance Percentage -->
              <div class="mt-6">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                  <span>Tingkat Kehadiran</span>
                  <span>{{ attendancePercentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div
                    class="h-2 rounded-full transition-all duration-300"
                    :class="attendancePercentage >= 90 ? 'bg-green-500' : attendancePercentage >= 80 ? 'bg-yellow-500' : 'bg-red-500'"
                    :style="{ width: attendancePercentage + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Salary Breakdown -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <DollarSign class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                Breakdown Penghasilan
              </h3>
            </div>
            <div class="p-6">
              <!-- Base Salary -->
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600 dark:text-gray-400">Gaji Pokok</span>
                  <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(payroll.base_salary) }}</span>
                </div>

                <!-- Allowances -->
                <div v-if="allowanceDetails.length > 0">
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tunjangan:</div>
                  <div v-for="allowance in allowanceDetails" :key="allowance.id" class="flex justify-between items-center pl-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">• {{ allowance.name }}</span>
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">+{{ formatCurrency(allowance.amount) }}</span>
                  </div>
                </div>

                <!-- Overtime -->
                <div v-if="overtimeDetails.length > 0">
                  <div v-for="overtime in overtimeDetails" :key="overtime.id" class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">{{ overtime.name }}</span>
                    <span class="font-medium text-green-600 dark:text-green-400">+{{ formatCurrency(overtime.amount) }}</span>
                  </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <div class="flex justify-between items-center font-semibold">
                  <span class="text-gray-900 dark:text-white">Total Penghasilan</span>
                  <span class="text-green-600 dark:text-green-400">{{ formatCurrency(payroll.gross_salary) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Deductions -->
          <div v-if="deductionDetails.length > 0" class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <Minus class="mr-2 h-5 w-5 text-red-600 dark:text-red-400" />
                Breakdown Potongan
              </h3>
            </div>
            <div class="p-6 space-y-3">
              <div v-for="deduction in deductionDetails" :key="deduction.id" class="flex justify-between items-center">
                <div>
                  <span class="text-gray-600 dark:text-gray-400">{{ deduction.name }}</span>
                  <p v-if="deduction.description" class="text-xs text-gray-500 dark:text-gray-500">{{ deduction.description }}</p>
                </div>
                <span class="font-medium text-red-600 dark:text-red-400">-{{ formatCurrency(deduction.amount) }}</span>
              </div>

              <hr class="border-gray-200 dark:border-gray-700">

              <div class="flex justify-between items-center font-semibold">
                <span class="text-gray-900 dark:text-white">Total Potongan</span>
                <span class="text-red-600 dark:text-red-400">-{{ formatCurrency(payroll.total_deductions) }}</span>
              </div>
            </div>
          </div>

          <!-- Admin Actions -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <Settings class="mr-2 h-5 w-5 text-purple-600 dark:text-purple-400" />
                Admin Actions
              </h3>
            </div>
            <div class="p-6 space-y-3">
              <button
                @click="regeneratePayroll"
                class="w-full flex items-center justify-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors"
              >
                <RotateCcw class="w-4 h-4 mr-2" />
                Regenerate Payroll
              </button>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Menghitung ulang payroll berdasarkan data attendance dan aturan terbaru
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Final Summary -->
      <div class="mt-8 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-8">
          <div class="flex justify-between items-center text-xl font-bold">
            <span class="text-gray-900 dark:text-white">TOTAL GAJI BERSIH</span>
            <span class="text-3xl text-green-600 dark:text-green-400">{{ formatCurrency(payroll.net_salary) }}</span>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            * Gaji sudah dipotong sesuai aturan yang berlaku
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  ArrowLeft,
  RotateCcw,
  Printer,
  User,
  Clock,
  DollarSign,
  Minus,
  Settings
} from 'lucide-vue-next'

interface PayrollDetail {
  id: number
  type: 'allowance' | 'deduction' | 'overtime'
  name: string
  description: string | null
  amount: number
}

interface Payroll {
  id: number
  user: {
    name: string
    employee_id: string
  }
  period_name: string
  period_year: number
  period_month: number
  base_salary: number
  gross_salary: number
  total_deductions: number
  net_salary: number
  working_days: number
  actual_working_days: number
  overtime_hours: number
  late_minutes: number
  absent_days: number
  created_at: string
  details: PayrollDetail[]
}

interface Props {
  payroll: Payroll
}

const props = defineProps<Props>()

const breadcrumbs = [
  { name: 'Dashboard', href: route('admin.dashboard') },
  { name: 'Payroll', href: route('admin.payrolls.index') },
  { name: props.payroll.user.name, href: route('admin.payrolls.show', props.payroll.id) },
]

const allowanceDetails = computed(() => {
  return props.payroll.details.filter(detail => detail.type === 'allowance')
})

const deductionDetails = computed(() => {
  return props.payroll.details.filter(detail => detail.type === 'deduction')
})

const overtimeDetails = computed(() => {
  return props.payroll.details.filter(detail => detail.type === 'overtime')
})

const attendancePercentage = computed(() => {
  if (props.payroll.working_days === 0) return 0
  return Math.round((props.payroll.actual_working_days / props.payroll.working_days) * 100)
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const regeneratePayroll = () => {
  if (confirm(`Yakin ingin regenerate payroll untuk ${props.payroll.user.name}? Data lama akan diganti dengan perhitungan terbaru.`)) {
    router.post(route('admin.payrolls.regenerate', props.payroll.id))
  }
}

const printPayroll = () => {
  window.print()
}
</script>

<style>
@media print {
  .no-print {
    display: none !important;
  }

  body {
    font-size: 12px;
  }

  .print-break {
    page-break-before: always;
  }
}
</style>