<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Slip Gaji {{ payroll.period_name }}
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              {{ payroll.user.name }} • {{ formatDate(payroll.created_at) }}
            </p>
          </div>
          <div class="flex space-x-3">
            <Link
              :href="route('employee.payrolls.index')"
              class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Link>
            <button
              @click="printSlip"
              class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
            >
              <Printer class="w-4 h-4 mr-2" />
              Cetak
            </button>
          </div>
        </div>
      </div>

      <!-- Salary Summary -->
      <div class="mb-8 overflow-hidden rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 dark:from-blue-900/20 dark:to-indigo-900/20 dark:border-blue-800">
        <div class="p-8">
          <div class="text-center">
            <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-100 mb-2">Gaji Bersih</h2>
            <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(payroll.net_salary) }}</p>
            <p class="text-sm text-blue-700 dark:text-blue-300 mt-2">
              Setelah potongan {{ formatCurrency(payroll.total_deductions) }}
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column -->
        <div class="space-y-6">
          <!-- Basic Information -->
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
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ payroll.user.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ payroll.period_name }}</p>
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
                Rincian Gaji
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
                  <span class="text-gray-900 dark:text-white">Gaji Kotor</span>
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
                Potongan
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
            * Gaji akan ditransfer ke rekening yang terdaftar
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  ArrowLeft,
  Printer,
  User,
  Clock,
  DollarSign,
  Minus
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
  { name: 'Dashboard', href: route('welcome') },
  { name: 'Slip Gaji', href: route('employee.payrolls.index') },
  { name: props.payroll.period_name, href: route('employee.payrolls.show', props.payroll.id) },
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

const printSlip = () => {
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