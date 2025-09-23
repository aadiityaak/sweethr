<template>
  <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="p-6">
      <div class="flex items-start justify-between">
        <div class="flex items-start space-x-4">
          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50">
            <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
          </div>
          <div class="min-w-0 flex-1">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ document.title }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ document.document_type?.name }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500">
              {{ document.user?.name }} ({{ document.user?.employee_id }})
            </p>
          </div>
        </div>

        <div class="flex flex-col items-end space-y-2">
          <DocumentStatus
            :status="document.status"
            :is-expiring-soon="isExpiringSoon"
          />

          <!-- Actions Dropdown -->
          <div class="relative">
            <button
              type="button"
              class="flex items-center rounded-full p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
              @click="showActions = !showActions"
            >
              <MoreHorizontal class="h-5 w-5" />
            </button>

            <div
              v-if="showActions"
              v-click-outside="() => showActions = false"
              class="absolute right-0 z-10 mt-1 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800 dark:ring-white/10"
            >
              <button
                type="button"
                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                @click="$emit('view', document)"
              >
                <Eye class="mr-3 h-4 w-4" />
                Lihat Detail
              </button>

              <button
                type="button"
                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                @click="$emit('download', document)"
              >
                <Download class="mr-3 h-4 w-4" />
                Download
              </button>

              <button
                v-if="document.status === 'pending'"
                type="button"
                class="flex w-full items-center px-4 py-2 text-sm text-green-700 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20"
                @click="$emit('approve', document)"
              >
                <CheckCircle class="mr-3 h-4 w-4" />
                Setujui
              </button>

              <button
                v-if="document.status === 'pending'"
                type="button"
                class="flex w-full items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                @click="$emit('reject', document)"
              >
                <XCircle class="mr-3 h-4 w-4" />
                Tolak
              </button>

              <button
                type="button"
                class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                @click="$emit('edit', document)"
              >
                <SquarePen class="mr-3 h-4 w-4" />
                Edit
              </button>

              <button
                type="button"
                class="flex w-full items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                @click="$emit('delete', document)"
              >
                <Trash2 class="mr-3 h-4 w-4" />
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Document Details -->
      <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
        <div>
          <span class="text-gray-500 dark:text-gray-400">File:</span>
          <span class="ml-2 text-gray-900 dark:text-white">{{ document.file_name }}</span>
        </div>
        <div>
          <span class="text-gray-500 dark:text-gray-400">Ukuran:</span>
          <span class="ml-2 text-gray-900 dark:text-white">{{ formatFileSize(document.file_size) }}</span>
        </div>
        <div v-if="document.issued_date">
          <span class="text-gray-500 dark:text-gray-400">Tanggal Terbit:</span>
          <span class="ml-2 text-gray-900 dark:text-white">{{ formatDate(document.issued_date) }}</span>
        </div>
        <div v-if="document.expiry_date">
          <span class="text-gray-500 dark:text-gray-400">Kadaluarsa:</span>
          <span :class="[
            'ml-2',
            isExpired ? 'text-red-600 dark:text-red-400' :
            isExpiringSoon ? 'text-orange-600 dark:text-orange-400' :
            'text-gray-900 dark:text-white'
          ]">
            {{ formatDate(document.expiry_date) }}
          </span>
        </div>
      </div>

      <!-- Description -->
      <div v-if="document.description" class="mt-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          {{ document.description }}
        </p>
      </div>

      <!-- Upload Info -->
      <div class="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span>Diupload oleh {{ document.uploaded_by?.name }}</span>
        <span>{{ formatDateTime(document.created_at) }}</span>
      </div>

      <!-- Approval Info -->
      <div v-if="document.status === 'approved' && document.approved_by" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
        Disetujui oleh {{ document.approved_by.name }} pada {{ formatDateTime(document.approved_at) }}
      </div>

      <!-- Rejection Reason -->
      <div v-if="document.status === 'rejected' && document.rejection_reason" class="mt-4 rounded-md bg-red-50 p-3 dark:bg-red-900/20">
        <p class="text-sm text-red-800 dark:text-red-200">
          <strong>Alasan Penolakan:</strong> {{ document.rejection_reason }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  FileText,
  MoreHorizontal,
  Eye,
  Download,
  CheckCircle,
  XCircle,
  SquarePen,
  Trash2
} from 'lucide-vue-next'
import DocumentStatus from './DocumentStatus.vue'

interface Document {
  id: number
  title: string
  file_name: string
  file_size: number
  issued_date?: string
  expiry_date?: string
  status: 'pending' | 'approved' | 'rejected' | 'expired'
  description?: string
  created_at: string
  approved_at?: string
  rejection_reason?: string
  document_type?: {
    id: number
    name: string
  }
  user?: {
    id: number
    name: string
    employee_id: string
  }
  uploaded_by?: {
    id: number
    name: string
  }
  approved_by?: {
    id: number
    name: string
  }
}

interface Props {
  document: Document
}

interface Emits {
  (e: 'view', document: Document): void
  (e: 'download', document: Document): void
  (e: 'approve', document: Document): void
  (e: 'reject', document: Document): void
  (e: 'edit', document: Document): void
  (e: 'delete', document: Document): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const showActions = ref(false)

const isExpired = computed(() => {
  if (!props.document.expiry_date) return false
  return new Date(props.document.expiry_date) < new Date()
})

const isExpiringSoon = computed(() => {
  if (!props.document.expiry_date || isExpired.value) return false
  const expiryDate = new Date(props.document.expiry_date)
  const today = new Date()
  const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000)
  return expiryDate <= thirtyDaysFromNow
})

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Click outside directive
const vClickOutside = {
  beforeMount(el: HTMLElement, binding: any) {
    el.clickOutsideEvent = (event: Event) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el: HTMLElement) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>