<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Manajemen Payroll
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              Generate dan kelola payroll karyawan
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
            <select
              v-model="selectedMonth"
              @change="changeMonth"
              class="block py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
            >
              <option v-for="month in months" :key="month.value" :value="month.value">
                {{ month.label }}
              </option>
            </select>
            <button
              @click="showGenerateModal = true"
              class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
            >
              <Plus class="w-4 h-4 mr-2" />
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
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <FileText class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
            Payroll {{ getCurrentPeriodName() }}
          </h3>
        </div>

        <div v-if="payrolls.length === 0" class="p-12 text-center">
          <FileText class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Payroll</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-4">Payroll untuk periode ini belum digenerate.</p>
          <button
            @click="showGenerateModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
          >
            <Plus class="w-4 h-4 mr-2" />
            Generate Payroll Sekarang
          </button>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Karyawan
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Gaji Kotor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Potongan
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Gaji Bersih
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Kehadiran
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="payroll in payrolls" :key="payroll.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ payroll.user.name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      ID: {{ payroll.user.employee_id }}
                    </div>
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
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <Link
                      :href="show.url(payroll.id)"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      <Eye class="w-4 h-4" />
                    </Link>
                    <button
                      @click="regeneratePayroll(payroll)"
                      class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                      title="Regenerate"
                    >
                      <RotateCcw class="w-4 h-4" />
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
    <div v-if="showGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-900 rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Generate Payroll {{ getCurrentPeriodName() }}
        </h3>

        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Yakin ingin generate payroll untuk periode {{ getCurrentPeriodName() }}?
          Proses ini akan menghitung gaji semua karyawan berdasarkan data kehadiran dan aturan yang berlaku.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showGenerateModal = false"
            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
          >
            Batal
          </button>
          <button
            @click="confirmGenerate"
            :disabled="generating"
            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50"
          >
            {{ generating ? 'Generating...' : 'Generate' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { dashboard } from '@/routes/admin'
import { index as payrollsIndex, show, generate, regenerate } from '@/routes/admin/payrolls'
import {
  Plus,
  DollarSign,
  Users,
  TrendingDown,
  Clock,
  FileText,
  Eye,
  RotateCcw
} from 'lucide-vue-next'

interface Payroll {
  id: number
  user: {
    name: string
    employee_id: string
  }
  gross_salary: number
  total_deductions: number
  net_salary: number
  working_days: number
  actual_working_days: number
  overtime_hours: number
}

interface Props {
  payrolls: Payroll[]
  currentYear: number
  currentMonth: number
}

const props = defineProps<Props>()

const breadcrumbs = [
  { name: 'Dashboard', href: dashboard.url() },
  { name: 'Payroll', href: payrollsIndex.url() },
]

const selectedYear = ref(props.currentYear)
const selectedMonth = ref(props.currentMonth)
const showGenerateModal = ref(false)
const generating = ref(false)

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
]

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  return [currentYear - 1, currentYear, currentYear + 1]
})

const totalPayroll = computed(() => {
  return props.payrolls.reduce((sum, p) => sum + p.net_salary, 0)
})

const totalDeductions = computed(() => {
  return props.payrolls.reduce((sum, p) => sum + p.total_deductions, 0)
})

const averagePayroll = computed(() => {
  if (props.payrolls.length === 0) return 0
  return totalPayroll.value / props.payrolls.length
})

const getCurrentPeriodName = () => {
  const month = months.find(m => m.value === selectedMonth.value)
  return `${month?.label} ${selectedYear.value}`
}

const changeYear = () => {
  router.get(payrollsIndex.url(), {
    year: selectedYear.value,
    month: selectedMonth.value
  })
}

const changeMonth = () => {
  router.get(payrollsIndex.url(), {
    year: selectedYear.value,
    month: selectedMonth.value
  })
}

const confirmGenerate = () => {
  generating.value = true
  router.post(generate.url(), {
    year: selectedYear.value,
    month: selectedMonth.value
  }, {
    onFinish: () => {
      generating.value = false
      showGenerateModal.value = false
    }
  })
}

const regeneratePayroll = (payroll: Payroll) => {
  if (confirm(`Yakin ingin regenerate payroll untuk ${payroll.user.name}?`)) {
    router.post(regenerate.url(payroll.id))
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}
</script>