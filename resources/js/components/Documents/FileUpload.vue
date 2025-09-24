<template>
  <div class="space-y-4">
    <!-- Drag & Drop Area -->
    <div
      ref="dropZone"
      :class="[
        'relative rounded-lg border-2 border-dashed p-6 transition-colors',
        isDragOver
          ? 'border-blue-400 bg-blue-50 dark:border-blue-400 dark:bg-blue-950/50'
          : 'border-gray-300 dark:border-gray-600',
        disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
      ]"
      @click="!disabled && fileInputRef?.click()"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
    >
      <input
        ref="fileInputRef"
        type="file"
        class="hidden"
        :accept="acceptedFormats"
        :disabled="disabled"
        @change="handleFileSelect"
      >

      <div class="text-center">
        <Upload class="mx-auto h-12 w-12 text-gray-400" />
        <div class="mt-4">
          <p class="text-sm font-medium text-gray-900 dark:text-white">
            {{ isDragOver ? 'Drop file di sini' : 'Klik atau drag file ke sini' }}
          </p>
          <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Format yang diizinkan: {{ formattedAcceptedFormats }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            Maksimal {{ maxSizeMB }}MB
          </p>
        </div>
      </div>

      <!-- Loading overlay -->
      <div
        v-if="uploading"
        class="absolute inset-0 flex items-center justify-center rounded-lg bg-white/80 dark:bg-gray-900/80"
      >
        <div class="text-center">
          <LoaderCircle class="mx-auto h-8 w-8 animate-spin text-blue-600" />
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Uploading...
          </p>
        </div>
      </div>
    </div>

    <!-- Selected File Preview -->
    <div
      v-if="selectedFile"
      class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="flex h-10 w-10 items-center justify-center rounded bg-blue-100 dark:bg-blue-900">
            <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              {{ selectedFile.name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ formatFileSize(selectedFile.size) }}
            </p>
          </div>
        </div>
        <button
          v-if="!disabled"
          type="button"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          @click="removeFile"
        >
          <X class="h-5 w-5" />
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="rounded-md bg-red-50 p-4 dark:bg-red-950/50">
      <div class="flex">
        <CircleAlert class="h-5 w-5 text-red-400" />
        <div class="ml-3">
          <p class="text-sm text-red-800 dark:text-red-200">{{ error }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Upload, FileText, X, LoaderCircle, CircleAlert } from 'lucide-vue-next'

interface Props {
  modelValue?: File | null
  acceptedFormats?: string[]
  maxSizeMB?: number
  disabled?: boolean
  uploading?: boolean
}

interface Emits {
  (e: 'update:modelValue', file: File | null): void
  (e: 'upload', file: File): void
  (e: 'error', message: string): void
}

const props = withDefaults(defineProps<Props>(), {
  acceptedFormats: () => [
    // Documents
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt', 'ods', 'odp',
    // Images
    'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp',
    // Archives
    'zip', 'rar', '7z',
    // Other formats
    'csv', 'xml', 'json'
  ],
  maxSizeMB: 10,
  disabled: false,
  uploading: false,
})

const emit = defineEmits<Emits>()

const dropZone = ref<HTMLElement>()
const fileInputRef = ref<HTMLInputElement>()
const isDragOver = ref(false)
const selectedFile = ref<File | null>(props.modelValue || null)
const error = ref<string>('')

const acceptedFormats = computed(() => {
  return props.acceptedFormats.map(format => `.${format}`).join(',')
})

const formattedAcceptedFormats = computed(() => {
  return props.acceptedFormats.map(format => format.toUpperCase()).join(', ')
})

const validateFile = (file: File): string | null => {
  // Check file size
  const maxSizeBytes = props.maxSizeMB * 1024 * 1024
  if (file.size > maxSizeBytes) {
    return `File terlalu besar. Maksimal ${props.maxSizeMB}MB`
  }

  // Check file format
  const fileExtension = file.name.split('.').pop()?.toLowerCase()
  if (!fileExtension || !props.acceptedFormats.includes(fileExtension)) {
    return `Format file tidak didukung. Gunakan: ${formattedAcceptedFormats.value}`
  }

  return null
}

const handleFileSelect = (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]

  if (file) {
    const validationError = validateFile(file)
    if (validationError) {
      error.value = validationError
      emit('error', validationError)
      return
    }

    error.value = ''
    selectedFile.value = file
    emit('update:modelValue', file)
    emit('upload', file)
  }
}

const handleDragOver = (event: DragEvent) => {
  if (props.disabled) return
  isDragOver.value = true
}

const handleDragLeave = (event: DragEvent) => {
  if (props.disabled) return
  isDragOver.value = false
}

const handleDrop = (event: DragEvent) => {
  if (props.disabled) return

  isDragOver.value = false
  const files = event.dataTransfer?.files

  if (files && files.length > 0) {
    const file = files[0]
    const validationError = validateFile(file)

    if (validationError) {
      error.value = validationError
      emit('error', validationError)
      return
    }

    error.value = ''
    selectedFile.value = file
    emit('update:modelValue', file)
    emit('upload', file)
  }
}

const removeFile = () => {
  selectedFile.value = null
  error.value = ''
  emit('update:modelValue', null)

  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Prevent default drag behaviors on the window
const preventDefaults = (e: Event) => {
  e.preventDefault()
  e.stopPropagation()
}

onMounted(() => {
  ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    window.addEventListener(eventName, preventDefaults, false)
  })
})

onUnmounted(() => {
  ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    window.removeEventListener(eventName, preventDefaults, false)
  })
})
</script>