<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Dokumen</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola dan pantau dokumen karyawan perusahaan</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700"
                            @click="refreshData"
                        >
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Refresh
                        </button>
                        <Link
                            :href="create.url()"
                            class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Upload Dokumen
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Documents -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                            <FileText class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Dokumen</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total || 0 }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Semua dokumen</p>
                    </div>
                </div>

                <!-- Active Documents -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                            <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Dokumen Aktif</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.active || 0 }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Masih berlaku</p>
                    </div>
                </div>

                <!-- Expired Documents -->
                <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10">
                            <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Kadaluarsa</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total - stats.active || 0 }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Perlu diperbaharui</p>
                    </div>
                </div>

                <!-- Active Percentage -->
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20">
                            <FileText class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-blue-100">Tingkat Keaktifan</p>
                        <p class="mt-1 text-3xl font-bold text-white">{{ stats.total ? Math.round((stats.active / stats.total) * 100) : 0 }}%</p>
                        <p class="mt-1 text-xs text-blue-100">Dokumen aktif</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <AlertTriangle class="mr-2 h-5 w-5 text-gray-600 dark:text-gray-400" />
                        Filter & Pencarian
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Employee Filter -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
                            <select
                                id="user_id"
                                v-model="filters.user_id"
                                class="mt-2 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset focus:ring-2 focus:ring-blue-600 focus:ring-inset sm:text-sm dark:bg-gray-800 dark:text-white dark:ring-gray-600"
                                @change="applyFilters"
                            >
                                <option value="">Semua Karyawan</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.employee_id }})</option>
                            </select>
                        </div>

                        <!-- Document Type Filter -->
                        <div>
                            <label for="document_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Dokumen</label>
                            <select
                                id="document_type_id"
                                v-model="filters.document_type_id"
                                class="mt-2 block w-full rounded-lg border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset focus:ring-2 focus:ring-blue-600 focus:ring-inset sm:text-sm dark:bg-gray-800 dark:text-white dark:ring-gray-600"
                                @change="applyFilters"
                            >
                                <option value="">Semua Jenis</option>
                                <option v-for="docType in documentTypes" :key="docType.id" :value="docType.id">
                                    {{ docType.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <div class="mt-4">
                        <button
                            type="button"
                            class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                            @click="clearFilters"
                        >
                            Bersihkan Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Documents Table -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <FileText class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Daftar Dokumen
                    </h3>
                </div>

                <!-- Empty State -->
                <div v-if="documents.data.length === 0" class="py-12 text-center">
                    <FileText class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada dokumen</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada dokumen yang diupload atau sesuai dengan filter.</p>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Judul & Karyawan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Jenis Dokumen
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    File Info
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Tanggal
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr v-for="document in documents.data" :key="document.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-500/10">
                                            <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ document.title }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ document.user?.name }} · {{ document.user?.employee_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ document.document_type?.name }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ document.file_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatFileSize(document.file_size) }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <div v-if="document.issued_date" class="text-gray-900 dark:text-white">
                                        Terbit: {{ formatDate(document.issued_date) }}
                                    </div>
                                    <div
                                        v-if="document.expiry_date"
                                        :class="[
                                            'text-sm',
                                            isExpired(document)
                                                ? 'text-red-600 dark:text-red-400'
                                                : isExpiringSoon(document)
                                                  ? 'text-orange-600 dark:text-orange-400'
                                                  : 'text-gray-900 dark:text-white',
                                        ]"
                                    >
                                        Exp: {{ formatDate(document.expiry_date) }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        v-if="isExpired(document)"
                                        class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800 dark:bg-red-900/20 dark:text-red-400"
                                    >
                                        Kadaluarsa
                                    </span>
                                    <span
                                        v-else-if="isExpiringSoon(document)"
                                        class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800 dark:bg-orange-900/20 dark:text-orange-400"
                                    >
                                        Segera Exp
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/20 dark:text-green-400"
                                    >
                                        Aktif
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg bg-blue-50 p-2 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                            @click="viewDocument(document)"
                                            title="Lihat Detail"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg bg-green-50 p-2 text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
                                            @click="downloadDocument(document)"
                                            title="Download"
                                        >
                                            <Download class="h-4 w-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg bg-gray-50 p-2 text-gray-700 hover:bg-gray-100 dark:bg-gray-900/20 dark:text-gray-400 dark:hover:bg-gray-900/30"
                                            @click="editDocument(document)"
                                            title="Edit"
                                        >
                                            <SquarePen class="h-4 w-4" />
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg bg-red-50 p-2 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                                            @click="showDeleteModal(document)"
                                            title="Hapus"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="documents.data.length > 0" class="mt-8">
                <Pagination :links="documents.links" />
            </div>
        </div>

        <!-- Delete Modal -->
        <ConfirmationModal
            :show="showingDeleteModal"
            title="Hapus Dokumen"
            :message="`Apakah Anda yakin ingin menghapus dokumen '${documentToDelete?.title}'? Tindakan ini tidak dapat dibatalkan.`"
            confirm-text="Hapus"
            cancel-text="Batal"
            type="danger"
            @confirm="handleDeleteDocument"
            @cancel="hideDeleteModal"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import Pagination from '@/components/Pagination.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { create, destroy, index as documentsIndex, download, edit, show } from '@/routes/admin/documents';
import { Link, router } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle, Download, Eye, FileText, Plus, RefreshCw, SquarePen, Trash2 } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface Props {
    documents: any;
    users: any[];
    documentTypes: any[];
    stats: {
        total: number;
        active: number;
    };
    filters: {
        user_id?: string;
        document_type_id?: string;
    };
}

const props = defineProps<Props>();
const { toast } = useToast();

const breadcrumbs = [
    { title: 'Dashboard', href: dashboard.url() },
    { title: 'Manajemen Karyawan', href: '#' },
    { title: 'Dokumen Karyawan', href: documentsIndex.url() },
];

const filters = reactive({
    user_id: props.filters.user_id || '',
    document_type_id: props.filters.document_type_id || '',
});

const showingDeleteModal = ref(false);
const documentToDelete = ref(null);

const applyFilters = () => {
    const queryParams = { ...filters };

    // Remove empty values
    Object.keys(queryParams).forEach((key) => {
        if (queryParams[key] === '') {
            delete queryParams[key];
        }
    });

    router.get(documentsIndex.url(), queryParams, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.user_id = '';
    filters.document_type_id = '';
    applyFilters();
};

const refreshData = () => {
    router.reload();
};

const viewDocument = (document: any) => {
    router.visit(show.url(document.id));
};

const downloadDocument = (document: any) => {
    toast({
        title: 'Download dimulai',
        description: `Mengunduh dokumen: ${document.title}`,
        duration: 2000,
    });
    window.open(download.url(document.id), '_blank');
};

const editDocument = (document: any) => {
    router.visit(edit.url(document.id));
};

const showDeleteModal = (document: any) => {
    documentToDelete.value = document;
    showingDeleteModal.value = true;
};

const hideDeleteModal = () => {
    showingDeleteModal.value = false;
    documentToDelete.value = null;
};

const handleDeleteDocument = () => {
    router.delete(destroy.url(documentToDelete.value.id), {
        onSuccess: () => {
            toast({
                title: 'Berhasil',
                description: 'Dokumen berhasil dihapus',
                duration: 3000,
            });
            hideDeleteModal();
        },
        onError: () => {
            toast({
                title: 'Gagal',
                description: 'Terjadi kesalahan saat menghapus dokumen',
                variant: 'destructive',
                duration: 4000,
            });
        },
    });
};

const isExpired = (document: any) => {
    if (!document.expiry_date) return false;
    return new Date(document.expiry_date) < new Date();
};

const isExpiringSoon = (document: any) => {
    if (!document.expiry_date || isExpired(document)) return false;
    const expiryDate = new Date(document.expiry_date);
    const today = new Date();
    const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
    return expiryDate <= thirtyDaysFromNow;
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>
