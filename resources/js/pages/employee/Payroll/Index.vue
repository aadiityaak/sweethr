<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Slip Gaji Saya
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              Riwayat slip gaji dan detail pembayaran
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <select
              v-model="selectedYear"
              @change="changeYear"
              class="block py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
            >
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Current Year Summary -->
      <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                <DollarSign class="h-6 w-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Gaji {{ selectedYear }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(yearlyTotal) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                <Calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Slip Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ currentMonthPayroll ? 'Tersedia' : 'Belum Ada' }}</p>
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
                <FileText class="h-6 w-6 text-purple-600 dark:text-purple-400" />
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Slip Tersedia</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ payrolls.length }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payroll History -->
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <FileText class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
            Riwayat Slip Gaji {{ selectedYear }}
          </h3>
        </div>

        <div v-if="payrolls.length === 0" class="p-12 text-center">
          <FileText class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Slip Gaji</h3>
          <p class="text-gray-500 dark:text-gray-400">Slip gaji untuk tahun {{ selectedYear }} belum tersedia.</p>
        </div>

        <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="payroll in payrolls"
            :key="payroll.id"
            class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-4">
                  <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                    <DollarSign class="h-6 w-6 text-green-600 dark:text-green-400" />
                  </div>
                  <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ payroll.period_name }}
                    </h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(payroll.created_at) }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex items-center space-x-6">
                <!-- Salary Details -->
                <div class="text-right">
                  <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Gaji Kotor</p>
                      <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(payroll.gross_salary) }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Potongan</p>
                      <p class="font-semibold text-red-600 dark:text-red-400">-{{ formatCurrency(payroll.total_deductions) }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Gaji Bersih</p>
                      <p class="font-bold text-green-600 dark:text-green-400 text-lg">{{ formatCurrency(payroll.net_salary) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Action Button -->
                <Link
                  :href="route('employee.payrolls.show', payroll.id)"
                  class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
                >
                  <Eye class="w-4 h-4 mr-2" />
                  Lihat Detail
                </Link>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div class="flex items-center text-gray-600 dark:text-gray-400">
                <Clock class="w-4 h-4 mr-2" />
                <span>{{ payroll.actual_working_days }}/{{ payroll.working_days }} hari kerja</span>
              </div>
              <div v-if="payroll.overtime_hours > 0" class="flex items-center text-blue-600 dark:text-blue-400">
                <Plus class="w-4 h-4 mr-2" />
                <span>{{ payroll.overtime_hours }} jam lembur</span>
              </div>
              <div v-if="payroll.late_minutes > 0" class="flex items-center text-orange-600 dark:text-orange-400">
                <AlertCircle class="w-4 h-4 mr-2" />
                <span>{{ Math.round(payroll.late_minutes) }} menit telat</span>
              </div>
              <div v-if="payroll.absent_days > 0" class="flex items-center text-red-600 dark:text-red-400">
                <UserX class="w-4 h-4 mr-2" />
                <span>{{ payroll.absent_days }} hari tidak masuk</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  DollarSign,
  Calendar,
  TrendingDown,
  FileText,
  Eye,
  Clock,
  Plus,
  AlertCircle,
  UserX
} from 'lucide-vue-next'

interface Payroll {
  id: number
  period_name: string
  period_year: number
  period_month: number
  gross_salary: number
  total_deductions: number
  net_salary: number
  working_days: number
  actual_working_days: number
  overtime_hours: number
  late_minutes: number
  absent_days: number
  created_at: string
}

interface Props {
  payrolls: Payroll[]
  currentYear: number
}

const props = defineProps<Props>()

const breadcrumbs = [
  { name: 'Dashboard', href: route('welcome') },
  { name: 'Slip Gaji', href: route('employee.payrolls.index') },
]

const selectedYear = ref(props.currentYear)

const availableYears = computed(() => {
  const years = new Set(props.payrolls.map(p => p.period_year))
  const currentYear = new Date().getFullYear()
  years.add(currentYear)
  return Array.from(years).sort((a, b) => b - a)
})

const yearlyTotal = computed(() => {
  return props.payrolls
    .filter(p => p.period_year === selectedYear.value)
    .reduce((sum, p) => sum + p.net_salary, 0)
})

const totalDeductions = computed(() => {
  return props.payrolls
    .filter(p => p.period_year === selectedYear.value)
    .reduce((sum, p) => sum + p.total_deductions, 0)
})

const currentMonthPayroll = computed(() => {
  const now = new Date()
  return props.payrolls.find(p =>
    p.period_year === now.getFullYear() &&
    p.period_month === now.getMonth() + 1
  )
})

const changeYear = () => {
  router.get(route('employee.payrolls.index'), { year: selectedYear.value })
}

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
</script>