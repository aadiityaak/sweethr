<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Manajemen Dokumen Karyawan
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              Kelola dan pantau dokumen karyawan perusahaan
            </p>
          </div>
          <div class="flex gap-3">
            <button
              type="button"
              class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
              @click="refreshData"
            >
              <RefreshCw class="mr-2 h-4 w-4" />
              Refresh
            </button>
            <Link
              :href="create.url()"
              class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
            >
              <Plus class="mr-2 h-4 w-4" />
              Upload Dokumen
            </Link>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Dokumen</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Dokumen Aktif</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-orange-500/10">
                <AlertTriangle class="h-6 w-6 text-orange-600 dark:text-orange-400" />
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Akan Kadaluarsa</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.expiring_soon }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
          <div class="p-6">
            <div class="flex items-center">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                <XCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Kadaluarsa</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.expired }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Employee Filter -->
            <div>
              <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Karyawan
              </label>
              <select
                id="user_id"
                v-model="filters.user_id"
                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6"
                @change="applyFilters"
              >
                <option value="">Semua Karyawan</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.employee_id }})
                </option>
              </select>
            </div>

            <!-- Document Type Filter -->
            <div>
              <label for="document_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Jenis Dokumen
              </label>
              <select
                id="document_type_id"
                v-model="filters.document_type_id"
                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 dark:bg-gray-800 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6"
                @change="applyFilters"
              >
                <option value="">Semua Jenis</option>
                <option v-for="docType in documentTypes" :key="docType.id" :value="docType.id">
                  {{ docType.name }}
                </option>
              </select>
            </div>


            <!-- Special Filters -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Filter Khusus
              </label>
              <div class="mt-1 space-y-2">
                <label class="flex items-center">
                  <input
                    v-model="filters.expiring_soon"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-800"
                    @change="applyFilters"
                  >
                  <span class="ml-2 text-sm text-gray-900 dark:text-white">Akan Kadaluarsa</span>
                </label>
                <label class="flex items-center">
                  <input
                    v-model="filters.expired"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-600 dark:border-gray-600 dark:bg-gray-800"
                    @change="applyFilters"
                  >
                  <span class="ml-2 text-sm text-gray-900 dark:text-white">Kadaluarsa</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Clear Filters -->
          <div class="mt-4">
            <button
              type="button"
              class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
              @click="clearFilters"
            >
              Bersihkan Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Documents List -->
      <div class="space-y-6">
        <DocumentCard
          v-for="document in documents.data"
          :key="document.id"
          :document="document"
          @view="viewDocument"
          @download="downloadDocument"
          @edit="editDocument"
          @delete="showDeleteModal"
        />
      </div>

      <!-- Empty State -->
      <div v-if="documents.data.length === 0" class="text-center py-12">
        <FileText class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada dokumen</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Belum ada dokumen yang diupload atau sesuai dengan filter.
        </p>
      </div>

      <!-- Pagination -->
      <div v-if="documents.data.length > 0" class="mt-8">
        <Pagination :links="documents.links" />
      </div>
    </div>


    <!-- Delete Modal -->
    <ConfirmationModal
      v-if="showingDeleteModal"
      title="Hapus Dokumen"
      :message="`Apakah Anda yakin ingin menghapus dokumen '${documentToDelete?.title}'? Tindakan ini tidak dapat dibatalkan.`"
      confirm-text="Hapus"
      confirm-variant="danger"
      @confirm="handleDeleteDocument"
      @cancel="hideDeleteModal"
    />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import {
  FileText,
  Plus,
  RefreshCw,
  CheckCircle,
  XCircle,
  AlertTriangle
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import DocumentCard from '@/components/Documents/DocumentCard.vue'
import { dashboard } from '@/routes/admin'
import { index as documentsIndex, create } from '@/routes/admin/documents'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import Pagination from '@/components/Pagination.vue'
import { useToast } from '@/components/ui/toast/use-toast'

interface Props {
  documents: any
  users: any[]
  documentTypes: any[]
  stats: {
    total: number
    active: number
    expiring_soon: number
    expired: number
  }
  filters: {
    user_id?: string
    document_type_id?: string
    expiring_soon?: boolean
    expired?: boolean
  }
}

const props = defineProps<Props>()
const { toast } = useToast()

const breadcrumbs = [
  { name: 'Dashboard', href: dashboard.url() },
  { name: 'Dokumen Karyawan', href: documentsIndex.url(), current: true },
]

const filters = reactive({
  user_id: props.filters.user_id || '',
  document_type_id: props.filters.document_type_id || '',
  expiring_soon: props.filters.expiring_soon || false,
  expired: props.filters.expired || false,
})


const showingDeleteModal = ref(false)
const documentToDelete = ref(null)

const applyFilters = () => {
  const queryParams = { ...filters }

  // Remove empty values
  Object.keys(queryParams).forEach(key => {
    if (queryParams[key] === '' || queryParams[key] === false) {
      delete queryParams[key]
    }
  })

  router.get(route('admin.documents.index'), queryParams, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  filters.user_id = ''
  filters.document_type_id = ''
  filters.expiring_soon = false
  filters.expired = false
  applyFilters()
}

const refreshData = () => {
  router.reload()
}

const viewDocument = (document: any) => {
  router.visit(route('admin.documents.show', document.id))
}

const downloadDocument = (document: any) => {
  window.open(route('admin.documents.download', document.id), '_blank')
}


const editDocument = (document: any) => {
  router.visit(route('admin.documents.edit', document.id))
}

const showDeleteModal = (document: any) => {
  documentToDelete.value = document
  showingDeleteModal.value = true
}

const hideDeleteModal = () => {
  showingDeleteModal.value = false
  documentToDelete.value = null
}

const handleDeleteDocument = () => {
  router.delete(route('admin.documents.destroy', documentToDelete.value.id), {
    onSuccess: () => {
      toast({
        title: 'Berhasil',
        description: 'Dokumen berhasil dihapus',
      })
      hideDeleteModal()
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