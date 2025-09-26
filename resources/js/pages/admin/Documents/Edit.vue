<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Edit Dokumen
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              Perbarui informasi dokumen karyawan
            </p>
          </div>
          <div class="flex gap-3">
            <Link
              :href="route('admin.documents.show', document.id)"
              class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
            >
              <ArrowLeft class="mr-2 h-4 w-4" />
              Kembali
            </Link>
          </div>
        </div>
      </div>

      <!-- Edit Form -->
      <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <SquarePen class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
            Edit Dokumen
          </h3>
        </div>
        <div class="p-6">
          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Current Document Info -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Dokumen Saat Ini</h4>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                  <span class="text-sm text-gray-500 dark:text-gray-400">Karyawan:</span>
                  <span class="ml-2 text-sm text-gray-900 dark:text-white">
                    {{ document.user?.name }} ({{ document.user?.employee_id }})
                  </span>
                </div>
                <div>
                  <span class="text-sm text-gray-500 dark:text-gray-400">Jenis:</span>
                  <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ document.document_type?.name }}</span>
                </div>
                <div>
                  <span class="text-sm text-gray-500 dark:text-gray-400">File:</span>
                  <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ document.file_name }}</span>
                </div>
                <div>
                  <span class="text-sm text-gray-500 dark:text-gray-400">Status:</span>
                  <span class="ml-2 inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                    Aktif
                  </span>
                </div>
              </div>
            </div>

            <!-- Document Title -->
            <div>
              <label for="title" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                Judul Dokumen <span class="text-red-500">*</span>
              </label>
              <Input
                id="title"
                v-model="form.title"
                type="text"
                class="mt-2"
                :class="{ 'ring-red-300 focus:ring-red-600 dark:ring-red-600': form.errors.title }"
                placeholder="Masukkan judul dokumen"
              />
              <InputError class="mt-2" :message="form.errors.title" />
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                Deskripsi
              </label>
              <Textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="mt-2"
                placeholder="Masukkan deskripsi dokumen (opsional)"
              />
              <InputError class="mt-2" :message="form.errors.description" />
            </div>


            <!-- Notes -->
            <div>
              <label for="notes" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                Catatan
              </label>
              <Textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="mt-2"
                placeholder="Masukkan catatan tambahan (opsional)"
              />
              <InputError class="mt-2" :message="form.errors.notes" />
            </div>

            <!-- Warning for pending documents -->
            <div v-if="document.status === 'pending'" class="rounded-md bg-yellow-50 p-4 dark:bg-yellow-900/20">
              <div class="flex">
                <AlertTriangle class="h-5 w-5 text-yellow-400" />
                <div class="ml-3">
                  <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    Dokumen masih menunggu persetujuan. Perubahan akan memerlukan persetujuan ulang.
                  </p>
                </div>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 pt-6 dark:border-white/10">
              <Link
                :href="route('admin.documents.show', document.id)"
                class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300"
              >
                Batal
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                <Save v-else class="mr-2 h-4 w-4" />
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Replace File Section -->
      <div class="mt-8 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <Upload class="mr-2 h-5 w-5 text-orange-600 dark:text-orange-400" />
            Ganti File (Opsional)
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Upload file baru untuk mengganti file yang sudah ada. File lama akan terhapus.
          </p>
        </div>
        <div class="p-6">
          <div class="rounded-md bg-yellow-50 p-4 mb-6 dark:bg-yellow-900/20">
            <div class="flex">
              <AlertTriangle class="h-5 w-5 text-yellow-400" />
              <div class="ml-3">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                  <strong>Peringatan:</strong> Mengganti file akan mengubah status dokumen menjadi "Menunggu Persetujuan"
                  dan memerlukan persetujuan ulang dari admin.
                </p>
              </div>
            </div>
          </div>

          <FileUpload
            v-model="newFile"
            :accepted-formats="allowedExtensions"
            :max-size-m-b="maxFileSize"
            @error="handleUploadError"
          />

          <div v-if="newFile" class="mt-6">
            <button
              type="button"
              :disabled="uploadingNewFile"
              class="inline-flex items-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 disabled:opacity-50 disabled:cursor-not-allowed"
              @click="replaceFile"
            >
              <LoaderCircle v-if="uploadingNewFile" class="mr-2 h-4 w-4 animate-spin" />
              <Upload v-else class="mr-2 h-4 w-4" />
              {{ uploadingNewFile ? 'Mengganti File...' : 'Ganti File Sekarang' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { ArrowLeft, SquarePen, Save, Upload, LoaderCircle, AlertTriangle } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import FileUpload from '@/components/Documents/FileUpload.vue'
import Input from '@/components/ui/input/Input.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import InputError from '@/components/InputError.vue'
import { useToast } from '@/components/ui/toast/use-toast'

interface Props {
  document: any
}

const props = defineProps<Props>()
const { toast } = useToast()

const breadcrumbs = [
  { name: 'Dashboard', href: route('admin.dashboard') },
  { name: 'Manajemen Karyawan', href: '#' },
  { name: 'Dokumen Karyawan', href: route('admin.documents.index') },
  { name: 'Detail Dokumen', href: route('admin.documents.show', props.document.id) },
  { name: 'Edit Dokumen', href: route('admin.documents.edit', props.document.id), current: true },
]

const form = useForm({
  title: props.document.title || '',
  description: props.document.description || '',
  notes: props.document.notes || '',
})

const newFile = ref<File | null>(null)
const uploadingNewFile = ref(false)


// Get allowed file extensions and max size from document type (if available)
const allowedExtensions = computed(() => {
  return props.document.document_type?.allowed_extensions || ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']
})

const maxFileSize = computed(() => {
  return props.document.document_type?.max_file_size_mb || 10
})

const handleUploadError = (message: string) => {
  toast({
    title: 'Error Upload',
    description: message,
    variant: 'destructive',
  })
}

const submitForm = () => {
  form.put(route('admin.documents.update', props.document.id), {
    onSuccess: () => {
      toast({
        title: 'Berhasil',
        description: 'Dokumen berhasil diperbarui',
      })
    },
    onError: () => {
      toast({
        title: 'Gagal',
        description: 'Terjadi kesalahan saat memperbarui dokumen',
        variant: 'destructive',
      })
    }
  })
}

const replaceFile = () => {
  if (!newFile.value) return

  uploadingNewFile.value = true

  const formData = new FormData()
  formData.append('file', newFile.value)
  formData.append('_method', 'PUT')

  // Use native fetch instead of Inertia for file upload
  fetch(route('admin.documents.update', props.document.id), {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
  .then(response => {
    if (response.ok) {
      toast({
        title: 'Berhasil',
        description: 'File berhasil diganti',
      })
      // Reload page to show updated document
      window.location.reload()
    } else {
      throw new Error('Upload failed')
    }
  })
  .catch(() => {
    toast({
      title: 'Gagal',
      description: 'Terjadi kesalahan saat mengganti file',
      variant: 'destructive',
    })
  })
  .finally(() => {
    uploadingNewFile.value = false
  })
}
</script>