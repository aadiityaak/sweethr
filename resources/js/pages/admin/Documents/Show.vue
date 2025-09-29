<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detail Dokumen</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Informasi lengkap dokumen karyawan</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="documentsIndex.url()"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
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
                <div class="space-y-8 lg:col-span-2">
                    <!-- Document Info -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                    <FileText class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    Informasi Dokumen
                                </h3>
                                <div
                                    v-if="isExpired"
                                    class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800 dark:bg-red-900/20 dark:text-red-400"
                                >
                                    Kadaluarsa
                                </div>
                                <div
                                    v-else-if="isExpiringSoon"
                                    class="inline-flex items-center rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900/20 dark:text-orange-400"
                                >
                                    Akan Kadaluarsa
                                </div>
                                <div
                                    v-else
                                    class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400"
                                >
                                    Aktif
                                </div>
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
                                    <dd
                                        :class="[
                                            'mt-1 text-sm',
                                            isExpired
                                                ? 'text-red-600 dark:text-red-400'
                                                : isExpiringSoon
                                                  ? 'text-orange-600 dark:text-orange-400'
                                                  : 'text-gray-900 dark:text-white',
                                        ]"
                                    >
                                        {{ formatDate(document.expiry_date) }}
                                        <span v-if="isExpired" class="ml-2 text-xs">(Kadaluarsa)</span>
                                        <span v-else-if="isExpiringSoon" class="ml-2 text-xs"
                                            >(Akan kadaluarsa dalam {{ daysUntilExpiry }} hari)</span
                                        >
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
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
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

                    <!-- Upload Info -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                                <Clock class="mr-2 h-5 w-5 text-purple-600 dark:text-purple-400" />
                                Riwayat Dokumen
                            </h3>
                        </div>
                        <div class="p-6 pb-8">
                            <div class="flow-root">
                                <ul role="list" class="space-y-6">
                                    <!-- Upload Event -->
                                    <li>
                                        <div class="relative">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span
                                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 ring-8 ring-white dark:ring-gray-900"
                                                    >
                                                        <Upload class="h-4 w-4 text-white" />
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            Dokumen diupload oleh
                                                            <span class="font-medium text-gray-900 dark:text-white">{{
                                                                document.uploaded_by?.name
                                                            }}</span>
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                        {{ formatDateTime(document.created_at) }}
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
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Aksi</h3>
                        </div>
                        <div class="space-y-4 p-6">
                            <!-- Actions -->
                            <Link
                                :href="edit.url(document.id)"
                                class="inline-flex w-full items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
                            >
                                <SquarePen class="mr-2 h-4 w-4" />
                                Edit Dokumen
                            </Link>

                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                                @click="showDeleteModal = true"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Hapus Dokumen
                            </button>
                        </div>
                    </div>

                    <!-- Document Type Info -->
                    <div
                        v-if="document.document_type"
                        class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10"
                    >
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Jenis Dokumen</h3>
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
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { destroy, index as documentsIndex, download, edit, show } from '@/routes/admin/documents';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Clock, Download, FileText, SquarePen, Trash2, Upload, User } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    document: any;
}

const props = defineProps<Props>();
const { toast } = useToast();

const showDeleteModal = ref(false);

const breadcrumbs = [
    { name: 'Dashboard', href: dashboard.url() },
    { name: 'Manajemen Karyawan', href: '#' },
    { name: 'Dokumen Karyawan', href: documentsIndex.url() },
    { name: 'Detail Dokumen', href: show.url(props.document.id), current: true },
];

const isExpired = computed(() => {
    if (!props.document.expiry_date) return false;
    return new Date(props.document.expiry_date) < new Date();
});

const isExpiringSoon = computed(() => {
    if (!props.document.expiry_date || isExpired.value) return false;
    const expiryDate = new Date(props.document.expiry_date);
    const today = new Date();
    const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
    return expiryDate <= thirtyDaysFromNow;
});

const daysUntilExpiry = computed(() => {
    if (!props.document.expiry_date) return 0;
    const expiryDate = new Date(props.document.expiry_date);
    const today = new Date();
    const diffTime = expiryDate.getTime() - today.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});

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
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const downloadDocument = () => {
    window.open(download.url(props.document.id), '_blank');
};

const handleDeleteDocument = () => {
    router.delete(destroy.url(props.document.id), {
        onSuccess: () => {
            toast({
                title: 'Berhasil',
                description: 'Dokumen berhasil dihapus',
                duration: 3000,
            });
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
