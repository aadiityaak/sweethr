<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Riwayat Gaji - {{ user.name }}
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              ID: {{ user.employee_id }} • {{ user.department?.name }} • {{ user.position?.name }}
            </p>
          </div>
          <div class="flex space-x-3">
            <Link
              href="/admin/salary-settings"
              class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Link>
            <Link
              :href="`/admin/salary-settings/${user.id}/edit`"
              class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
            >
              <Edit class="w-4 h-4 mr-2" />
              Edit Gaji
            </Link>
          </div>
        </div>
      </div>

      <!-- Current Salary Overview -->
      <div v-if="currentSetting" class="mb-8 overflow-hidden rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 dark:from-green-900/20 dark:to-emerald-900/20 dark:border-green-800">
        <div class="p-8">
          <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-green-900 dark:text-green-100 mb-2">Pengaturan Gaji Aktif</h2>
            <p class="text-green-700 dark:text-green-300">Berlaku sejak {{ formatDate(currentSetting.effective_date) }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Gaji Pokok</h3>
              <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(currentSetting.base_salary) }}</p>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Total Tunjangan</h3>
              <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(currentSetting.total_allowances || 0) }}</p>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Rate Lembur</h3>
              <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ currentSetting.overtime_rate }}x</p>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Gaji Kotor</h3>
              <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(grossSalary) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- No Salary Setting Warning -->
      <div v-else class="mb-8 overflow-hidden rounded-xl bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800">
        <div class="p-8 text-center">
          <AlertTriangle class="w-16 h-16 mx-auto text-red-600 dark:text-red-400 mb-4" />
          <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">Belum Ada Pengaturan Gaji</h3>
          <p class="text-red-700 dark:text-red-300 mb-4">
            Karyawan ini belum memiliki pengaturan gaji. Silakan buat pengaturan gaji terlebih dahulu.
          </p>
          <Link
            :href="route('admin.salary-settings.edit', user.id)"
            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
          >
            <Plus class="w-4 h-4 mr-2" />
            Buat Pengaturan Gaji
          </Link>
        </div>
      </div>

      <div v-if="currentSetting" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column -->
        <div class="space-y-6">
          <!-- Current Allowances -->
          <div v-if="currentSetting.allowances && currentSetting.allowances.length > 0" class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <Gift class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                Tunjangan Aktif
              </h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div v-for="(allowance, index) in currentSetting.allowances" :key="index" class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                  <span class="font-medium text-green-900 dark:text-green-100">{{ allowance.name }}</span>
                  <span class="font-bold text-green-600 dark:text-green-400">{{ formatCurrency(allowance.amount) }}</span>
                </div>
                <hr class="border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center font-semibold">
                  <span class="text-gray-900 dark:text-white">Total Tunjangan</span>
                  <span class="text-green-600 dark:text-green-400">{{ formatCurrency(currentSetting.total_allowances || 0) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Employee Info -->
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
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">ID Karyawan</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.employee_id }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Departemen</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.department?.name || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jabatan</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.position?.name || '-' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Salary History -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <History class="mr-2 h-5 w-5 text-purple-600 dark:text-purple-400" />
                Riwayat Perubahan Gaji
              </h3>
            </div>
            <div class="p-6">
              <div v-if="salarySettings.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                <FileText class="w-12 h-12 mx-auto mb-4 opacity-50" />
                <p>Belum ada riwayat perubahan gaji</p>
              </div>
              <div v-else class="space-y-4">
                <div
                  v-for="setting in salarySettings"
                  :key="setting.id"
                  class="border rounded-lg p-4"
                  :class="setting.is_active ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/20'"
                >
                  <div class="flex justify-between items-start mb-3">
                    <div>
                      <div class="flex items-center space-x-2 mb-1">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ formatDate(setting.effective_date) }}
                        </span>
                        <span
                          v-if="setting.is_active"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                        >
                          <CheckCircle class="w-3 h-3 mr-1" />
                          Aktif
                        </span>
                      </div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        Dibuat {{ formatDate(setting.created_at) }}
                      </p>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Gaji Pokok</p>
                      <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(setting.base_salary) }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 dark:text-gray-400">Tunjangan</p>
                      <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(setting.total_allowances || 0) }}</p>
                    </div>
                  </div>

                  <div v-if="setting.allowances && setting.allowances.length > 0" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Detail Tunjangan:</p>
                    <div class="grid grid-cols-1 gap-1">
                      <div v-for="(allowance, index) in setting.allowances" :key="index" class="flex justify-between text-xs">
                        <span class="text-gray-600 dark:text-gray-400">• {{ allowance.name }}</span>
                        <span class="font-medium">{{ formatCurrency(allowance.amount) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Overtime Settings -->
          <div v-if="currentSetting" class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <Clock class="mr-2 h-5 w-5 text-orange-600 dark:text-orange-400" />
                Pengaturan Lembur
              </h3>
            </div>
            <div class="p-6">
              <div class="text-center p-6 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-2">{{ currentSetting.overtime_rate }}x</p>
                <p class="text-sm text-orange-700 dark:text-orange-300">Rate Lembur</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                  Dari gaji harian normal
                </p>
              </div>

              <!-- Overtime Calculation Example -->
              <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Contoh Perhitungan:</h4>
                <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                  <p>• Gaji harian: {{ formatCurrency(currentSetting.base_salary / 22) }}</p>
                  <p>• Gaji per jam: {{ formatCurrency((currentSetting.base_salary / 22) / 8) }}</p>
                  <p>• Lembur 1 jam: {{ formatCurrency(((currentSetting.base_salary / 22) / 8) * currentSetting.overtime_rate) }}</p>
                </div>
              </div>
            </div>
          </div>
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
  Edit,
  AlertTriangle,
  Plus,
  Gift,
  User,
  History,
  FileText,
  CheckCircle,
  Clock
} from 'lucide-vue-next'

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
  created_at: string
  is_active: boolean
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
  salarySettings: SalarySetting[]
}

const props = defineProps<Props>()

const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Pengaturan Gaji', href: '/admin/salary-settings' },
  { title: props.user.name, href: `/admin/salary-settings/${props.user.id}` },
]

const currentSetting = computed(() => {
  return props.salarySettings.find(setting => setting.is_active)
})

const grossSalary = computed(() => {
  if (!currentSetting.value) return 0
  return currentSetting.value.base_salary + (currentSetting.value.total_allowances || 0)
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
</script>