<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Dokumen Karyawan</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola dan pantau dokumen karyawan perusahaan</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
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
            <div class="mb-10 grid gap-6 md:grid-cols-2">
                <!-- Total Documents -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Dokumen</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Semua dokumen terdaftar</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ stats.total }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Aktif</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ stats.active }}</span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Active Documents -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Dokumen Aktif</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Dokumen yang masih berlaku</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Aktif</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ stats.active }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total ? Math.round((stats.active / stats.total) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="group relative mb-10 overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30"
            >
                <div class="border-b border-gray-200/50 p-6 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-500/10 ring-1 ring-gray-500/20">
                            <AlertTriangle class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter & Pencarian</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Filter dokumen berdasarkan kriteria</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Employee Filter -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Karyawan </label>
                            <select
                                id="user_id"
                                v-model="filters.user_id"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset focus:ring-2 focus:ring-blue-600 focus:ring-inset sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-600"
                                @change="applyFilters"
                            >
                                <option value="">Semua Karyawan</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.employee_id }})</option>
                            </select>
                        </div>

                        <!-- Document Type Filter -->
                        <div>
                            <label for="document_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Jenis Dokumen </label>
                            <select
                                id="document_type_id"
                                v-model="filters.document_type_id"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset focus:ring-2 focus:ring-blue-600 focus:ring-inset sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-600"
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
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                            @click="clearFilters"
                        >
                            Bersihkan Filter
                        </button>
                    </div>
                </div>
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-gray-500/5 to-slate-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
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
            <div v-if="documents.data.length === 0" class="py-12 text-center">
                <FileText class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada dokumen</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada dokumen yang diupload atau sesuai dengan filter.</p>
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
import DocumentCard from '@/components/Documents/DocumentCard.vue';
import Pagination from '@/components/Pagination.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { create, destroy, index as documentsIndex, download, edit, show } from '@/routes/admin/documents';
import { Link, router } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle, FileText, Plus, RefreshCw } from 'lucide-vue-next';
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
    { name: 'Dashboard', href: dashboard.url() },
    { name: 'Manajemen Karyawan', href: '#' },
    { name: 'Dokumen Karyawan', href: documentsIndex.url(), current: true },
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
</script>
