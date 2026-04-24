<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Upload Dokumen Karyawan</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Upload dokumen baru untuk karyawan</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="documentsIndex.url()"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Upload class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Upload Dokumen
                    </h3>
                </div>
                <div class="p-6">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <!-- Employee Selection -->
                            <div>
                                <label for="user_id" class="block text-sm leading-6 font-medium text-gray-900 dark:text-white">
                                    Karyawan <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="user_id"
                                    v-model="form.user_id"
                                    :class="[
                                        'mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-blue-600 focus:ring-inset sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white',
                                        form.errors.user_id
                                            ? 'ring-red-300 focus:ring-red-600 dark:ring-red-600'
                                            : 'ring-gray-300 dark:ring-gray-600',
                                    ]"
                                >
                                    <option value="">Pilih Karyawan</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.employee_id }})</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.user_id" />
                            </div>

                            <!-- Document Type Selection -->
                            <div>
                                <label for="document_type_id" class="block text-sm leading-6 font-medium text-gray-900 dark:text-white">
                                    Jenis Dokumen <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="document_type_id"
                                    v-model="form.document_type_id"
                                    :class="[
                                        'mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-blue-600 focus:ring-inset sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white',
                                        form.errors.document_type_id
                                            ? 'ring-red-300 focus:ring-red-600 dark:ring-red-600'
                                            : 'ring-gray-300 dark:ring-gray-600',
                                    ]"
                                    @change="handleDocumentTypeChange"
                                >
                                    <option value="">Pilih Jenis Dokumen</option>
                                    <option v-for="docType in documentTypes" :key="docType.id" :value="docType.id">
                                        {{ docType.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.document_type_id" />

                                <!-- Document Type Info -->
                                <div v-if="selectedDocumentType" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p>
                                        Format: {{ selectedDocumentType.allowed_extensions?.join(', ').toUpperCase() }} | Maksimal:
                                        {{ selectedDocumentType.max_file_size_mb }}MB
                                    </p>
                                    <p v-if="selectedDocumentType.requires_expiry" class="text-orange-600 dark:text-orange-400">
                                        ⚠️ Dokumen ini memerlukan tanggal kadaluarsa
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Document Title -->
                        <div>
                            <label for="title" class="block text-sm leading-6 font-medium text-gray-900 dark:text-white">
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
                            <label for="description" class="block text-sm leading-6 font-medium text-gray-900 dark:text-white"> Deskripsi </label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-2"
                                placeholder="Masukkan deskripsi dokumen (opsional)"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm leading-6 font-medium text-gray-900 dark:text-white">
                                File Dokumen <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-2">
                                <FileUpload
                                    v-if="selectedDocumentType"
                                    v-model="form.file"
                                    :accepted-formats="selectedDocumentType.allowed_extensions || []"
                                    :max-size-m-b="selectedDocumentType.max_file_size_mb || 10"
                                    :uploading="form.processing"
                                    @error="handleUploadError"
                                />
                                <div v-else class="relative rounded-lg border-2 border-dashed border-gray-300 p-6 text-center dark:border-gray-600">
                                    <div class="text-gray-500 dark:text-gray-400">
                                        <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                        <p class="mt-4 text-sm font-medium">Silahkan pilih jenis dokumen terlebih dahulu</p>
                                        <p class="mt-2 text-xs">Format yang diizinkan akan ditampilkan setelah memilih jenis dokumen</p>
                                    </div>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.file" />
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm leading-6 font-medium text-gray-900 dark:text-white"> Catatan </label>
                            <Textarea id="notes" v-model="form.notes" rows="3" class="mt-2" placeholder="Masukkan catatan tambahan (opsional)" />
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 pt-6 dark:border-white/10">
                            <Link
                                :href="documentsIndex.url()"
                                class="text-sm leading-6 font-semibold text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing || !form.file"
                                class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Upload v-else class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Mengupload...' : 'Upload Dokumen' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import FileUpload from '@/components/Documents/FileUpload.vue';
import InputError from '@/components/InputError.vue';
import Input from '@/components/ui/input/Input.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { create, index as documentsIndex, store } from '@/routes/admin/documents';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, LoaderCircle, Upload } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Props {
    users: any[];
    documentTypes: any[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const breadcrumbs = [
    { name: 'Dashboard', href: dashboard.url() },
    { name: 'Manajemen Karyawan', href: '#' },
    { name: 'Dokumen Karyawan', href: documentsIndex.url() },
    { name: 'Upload Dokumen', href: create.url(), current: true },
];

const form = useForm({
    user_id: '',
    document_type_id: '',
    title: '',
    description: '',
    file: null as File | null,
    notes: '',
});

const selectedDocumentType = ref(null);

const handleDocumentTypeChange = () => {
    selectedDocumentType.value = props.documentTypes.find((type) => type.id == form.document_type_id);

    // Clear file if format doesn't match
    if (form.file && selectedDocumentType.value?.allowed_extensions) {
        const fileExtension = form.file.name.split('.').pop()?.toLowerCase();
        if (!selectedDocumentType.value.allowed_extensions.includes(fileExtension)) {
            form.file = null;
        }
    }
};

const handleUploadError = (message: string) => {
    toast({
        title: 'Error Upload',
        description: message,
        variant: 'destructive',
        duration: 4000,
    });
};

// Auto-update expiry date when issued date changes
watch(
    () => form.issued_date,
    (newDate) => {
        if (newDate && selectedDocumentType.value?.requires_expiry && selectedDocumentType.value.default_validity_months) {
            const issuedDate = new Date(newDate);
            issuedDate.setMonth(issuedDate.getMonth() + selectedDocumentType.value.default_validity_months);
            form.expiry_date = issuedDate.toISOString().split('T')[0];
        }
    },
);

const submitForm = () => {
    form.post(store.url(), {
        onSuccess: () => {
            toast({
                title: 'Berhasil',
                description: 'Dokumen berhasil diupload',
                duration: 3000,
            });
        },
        onError: () => {
            toast({
                title: 'Gagal',
                description: 'Terjadi kesalahan saat mengupload dokumen',
                variant: 'destructive',
                duration: 4000,
            });
        },
    });
};
</script>
