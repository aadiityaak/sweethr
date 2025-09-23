<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Detail Dokumen
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              Informasi lengkap dokumen karyawan
            </p>
          </div>
          <div class="flex gap-3">
            <Link
              :href="route('admin.documents.index')"
              class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
            >
              <ArrowLeft class="mr-2 h-4 w-4" />
              Kembali
            </Link>
            <button
              type="button"
              class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
              @click="downloadDocument"
            >
              <Download class="mr-2 h-4 w-4" />
              Download
            </button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Document Info -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                  <FileText class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                  Informasi Dokumen
                </h3>
                <DocumentStatus
                  :status="document.status"
                  :is-expiring-soon="isExpiringSoon"
                />
              </div>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Judul</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.title }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Dokumen</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.document_type?.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama File</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.file_name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ukuran File</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatFileSize(document.file_size) }}</dd>
                </div>
                <div v-if="document.issued_date">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Terbit</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(document.issued_date) }}</dd>
                </div>
                <div v-if="document.expiry_date">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Kadaluarsa</dt>
                  <dd :class="[
                    'mt-1 text-sm',
                    isExpired ? 'text-red-600 dark:text-red-400' :
                    isExpiringSoon ? 'text-orange-600 dark:text-orange-400' :
                    'text-gray-900 dark:text-white'
                  ]">
                    {{ formatDate(document.expiry_date) }}
                    <span v-if="isExpired" class="ml-2 text-xs">(Kadaluarsa)</span>
                    <span v-else-if="isExpiringSoon" class="ml-2 text-xs">(Akan kadaluarsa dalam {{ daysUntilExpiry }} hari)</span>
                  </dd>
                </div>
              </dl>

              <div v-if="document.description" class="mt-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.description }}</dd>
              </div>

              <div v-if="document.notes" class="mt-6">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.notes }}</dd>
              </div>
            </div>
          </div>

          <!-- Employee Info -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <User class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" />
                Informasi Karyawan
              </h3>
            </div>
            <div class="p-6">
              <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.user?.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID Karyawan</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ document.user?.employee_id }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Upload & Approval Info -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <Clock class="mr-2 h-5 w-5 text-purple-600 dark:text-purple-400" />
                Riwayat Dokumen
              </h3>
            </div>
            <div class="p-6">
              <div class="flow-root">
                <ul role="list" class="-mb-8">
                  <!-- Upload Event -->
                  <li>
                    <div class="relative pb-8">
                      <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                            <Upload class="h-4 w-4 text-white" />
                          </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                          <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                              Dokumen diupload oleh <span class="font-medium text-gray-900 dark:text-white">{{ document.uploaded_by?.name }}</span>
                            </p>
                          </div>
                          <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDateTime(document.created_at) }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>

                  <!-- Approval Event -->
                  <li v-if="document.status === 'approved' && document.approved_by">
                    <div class="relative">
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                            <CheckCircle class="h-4 w-4 text-white" />
                          </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                          <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                              Dokumen disetujui oleh <span class="font-medium text-gray-900 dark:text-white">{{ document.approved_by.name }}</span>
                            </p>
                          </div>
                          <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDateTime(document.approved_at) }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>

                  <!-- Rejection Event -->
                  <li v-if="document.status === 'rejected'">
                    <div class="relative">
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                            <XCircle class="h-4 w-4 text-white" />
                          </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                          <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                              Dokumen ditolak
                            </p>
                            <div v-if="document.rejection_reason" class="mt-2 rounded-md bg-red-50 p-3 dark:bg-red-900/20">
                              <p class="text-sm text-red-800 dark:text-red-200">
                                <strong>Alasan:</strong> {{ document.rejection_reason }}
                              </p>
                            </div>
                          </div>
                          <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDateTime(document.updated_at) }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
          <!-- Actions -->
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Aksi
              </h3>
            </div>
            <div class="p-6 space-y-4">
              <!-- Approval Actions -->
              <div v-if="document.status === 'pending'" class="space-y-3">
                <button
                  type="button"
                  class="w-full inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                  @click="approveDocument"
                >
                  <CheckCircle class="mr-2 h-4 w-4" />
                  Setujui Dokumen
                </button>
                <button
                  type="button"
                  class="w-full inline-flex items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                  @click="showRejectModal = true"
                >
                  <XCircle class="mr-2 h-4 w-4" />
                  Tolak Dokumen
                </button>
              </div>

              <!-- Other Actions -->
              <Link
                :href="route('admin.documents.edit', document.id)"
                class="w-full inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
              >
                <SquarePen class="mr-2 h-4 w-4" />
                Edit Dokumen
              </Link>

              <button
                type="button"
                class="w-full inline-flex items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                @click="showDeleteModal = true"
              >
                <Trash2 class="mr-2 h-4 w-4" />
                Hapus Dokumen
              </button>
            </div>
          </div>

          <!-- Document Type Info -->
          <div v-if="document.document_type" class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Jenis Dokumen
              </h3>
            </div>
            <div class="p-6">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ document.document_type.name }}</h4>
              <p v-if="document.document_type.description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ document.document_type.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rejection Modal -->
    <ConfirmationModal
      v-if="showRejectModal"
      title="Tolak Dokumen"
      :message="`Apakah Anda yakin ingin menolak dokumen '${document.title}'?`"
      confirm-text="Tolak"
      confirm-variant="danger"
      @confirm="handleRejectDocument"
      @cancel="showRejectModal = false"
    >
      <div class="mt-4">
        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Alasan Penolakan
        </label>
        <textarea
          id="rejection_reason"
          v-model="rejectionReason"
          rows="3"
          class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6"
          placeholder="Masukkan alasan penolakan..."
          required
        />
      </div>
    </ConfirmationModal>

    <!-- Delete Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      title="Hapus Dokumen"
      :message="`Apakah Anda yakin ingin menghapus dokumen '${document.title}'? Tindakan ini tidak dapat dibatalkan.`"
      confirm-text="Hapus"
      confirm-variant="danger"
      @confirm="handleDeleteDocument"
      @cancel="showDeleteModal = false"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import {
  ArrowLeft,
  Download,
  FileText,
  User,
  Clock,
  Upload,
  CheckCircle,
  XCircle,
  SquarePen,
  Trash2
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import DocumentStatus from '@/components/Documents/DocumentStatus.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import { useToast } from '@/components/ui/toast/use-toast'

interface Props {
  document: any
}

const props = defineProps<Props>()
const { toast } = useToast()

const showRejectModal = ref(false)
const rejectionReason = ref('')
const showDeleteModal = ref(false)

const breadcrumbs = [
  { name: 'Dashboard', href: route('admin.dashboard') },
  { name: 'Dokumen Karyawan', href: route('admin.documents.index') },
  { name: 'Detail Dokumen', href: route('admin.documents.show', props.document.id), current: true },
]

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

const daysUntilExpiry = computed(() => {
  if (!props.document.expiry_date) return 0
  const expiryDate = new Date(props.document.expiry_date)
  const today = new Date()
  const diffTime = expiryDate.getTime() - today.getTime()
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
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

const downloadDocument = () => {
  window.open(route('admin.documents.download', props.document.id), '_blank')
}

const approveDocument = () => {
  router.patch(route('admin.documents.approve', props.document.id), {}, {
    onSuccess: () => {
      toast({
        title: 'Berhasil',
        description: 'Dokumen berhasil disetujui',
      })
    },
    onError: () => {
      toast({
        title: 'Gagal',
        description: 'Terjadi kesalahan saat menyetujui dokumen',
        variant: 'destructive',
      })
    }
  })
}

const handleRejectDocument = () => {
  if (!rejectionReason.value.trim()) {
    toast({
      title: 'Error',
      description: 'Alasan penolakan harus diisi',
      variant: 'destructive',
    })
    return
  }

  router.patch(route('admin.documents.reject', props.document.id), {
    rejection_reason: rejectionReason.value
  }, {
    onSuccess: () => {
      toast({
        title: 'Berhasil',
        description: 'Dokumen berhasil ditolak',
      })
      showRejectModal.value = false
      rejectionReason.value = ''
    },
    onError: () => {
      toast({
        title: 'Gagal',
        description: 'Terjadi kesalahan saat menolak dokumen',
        variant: 'destructive',
      })
    }
  })
}

const handleDeleteDocument = () => {
  router.delete(route('admin.documents.destroy', props.document.id), {
    onSuccess: () => {
      toast({
        title: 'Berhasil',
        description: 'Dokumen berhasil dihapus',
      })
    },
    onError: () => {
      toast({
        title: 'Gagal',
        description: 'Terjadi kesalahan saat menghapus dokumen',
        variant: 'destructive',
      })
    }
  })
}
</script>