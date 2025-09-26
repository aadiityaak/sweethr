<template>
  <div class="relative rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
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
          <div class="flex items-center space-x-2">
            <div v-if="isExpired" class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800 dark:bg-red-900/20 dark:text-red-400">
              Kadaluarsa
            </div>
            <div v-else-if="isExpiringSoon" class="inline-flex items-center rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900/20 dark:text-orange-400">
              Akan Kadaluarsa
            </div>
            <div v-else class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
              Aktif
            </div>
          </div>

          <!-- Actions Dropdown -->
          <div class="relative">
            <button
              ref="buttonRef"
              type="button"
              class="flex items-center rounded-full p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
              @click="toggleActions"
            >
              <MoreHorizontal class="h-5 w-5" />
            </button>
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

    </div>

    <!-- Fixed positioned dropdown using Teleport -->
    <Teleport to="body">
      <div
        v-if="showActions"
        class="fixed z-50 w-48 rounded-md bg-white py-1 shadow-xl ring-1 ring-black ring-opacity-5 dark:bg-gray-800 dark:ring-white/10"
        :style="{ top: `${dropdownPosition.top}px`, left: `${dropdownPosition.left}px` }"
      >
        <button
          type="button"
          class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
          @click="handleView"
        >
          <Eye class="mr-3 h-4 w-4" />
          Lihat Detail
        </button>

        <button
          type="button"
          class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
          @click="handleDownload"
        >
          <Download class="mr-3 h-4 w-4" />
          Download
        </button>

        <button
          type="button"
          class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
          @click="handleEdit"
        >
          <SquarePen class="mr-3 h-4 w-4" />
          Edit
        </button>

        <button
          type="button"
          class="flex w-full items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
          @click="handleDelete"
        >
          <Trash2 class="mr-3 h-4 w-4" />
          Hapus
        </button>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import {
  FileText,
  MoreHorizontal,
  Eye,
  Download,
  SquarePen,
  Trash2
} from 'lucide-vue-next'

interface Document {
  id: number
  title: string
  file_name: string
  file_size: number
  issued_date?: string
  expiry_date?: string
  is_active: boolean
  description?: string
  created_at: string
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
}

interface Props {
  document: Document
}

interface Emits {
  (e: 'view', document: Document): void
  (e: 'download', document: Document): void
  (e: 'edit', document: Document): void
  (e: 'delete', document: Document): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const showActions = ref(false)
const buttonRef = ref<HTMLButtonElement>()
const dropdownPosition = ref({ top: 0, left: 0 })

const calculateDropdownPosition = () => {
  if (buttonRef.value) {
    const rect = buttonRef.value.getBoundingClientRect()
    dropdownPosition.value = {
      top: rect.bottom + window.scrollY + 4,
      left: rect.right + window.scrollX - 192 // 192px = w-48 (48 * 4)
    }
  }
}

const toggleActions = async () => {
  showActions.value = !showActions.value
  if (showActions.value) {
    await nextTick()
    calculateDropdownPosition()
  }
}

const handleView = () => {
  showActions.value = false
  emit('view', props.document)
}

const handleDownload = () => {
  showActions.value = false
  emit('download', props.document)
}

const handleEdit = () => {
  showActions.value = false
  emit('edit', props.document)
}

const handleDelete = () => {
  showActions.value = false
  emit('delete', props.document)
}

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

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
  if (buttonRef.value && !buttonRef.value.contains(event.target as Node)) {
    showActions.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

</script>