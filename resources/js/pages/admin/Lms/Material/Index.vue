<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, FileText, Pencil, Plus, Trash2 } from 'lucide-vue-next';

type MaterialType = 'video' | 'pdf' | 'module';

interface LmsMaterial {
    id: number;
    title: string;
    description: string | null;
    type: MaterialType;
    file_path: string;
    is_active: boolean;
    created_at: string;
}

interface Props {
    materials: {
        data: LmsMaterial[];
        links: any[];
        meta?: {
            from?: number;
            to?: number;
            total?: number;
            current_page?: number;
            last_page?: number;
            per_page?: number;
        };
    };
}

const { materials } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Pengelolaan Konten', href: '/admin/lms-materials' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const typeLabel = (type: MaterialType) => {
    if (type === 'video') return 'Video';
    if (type === 'pdf') return 'PDF';
    return 'Modul';
};

const fileUrl = (material: LmsMaterial) => `/storage/${material.file_path}`;

const deleteMaterial = (material: LmsMaterial) => {
    if (!confirm(`Yakin ingin menghapus materi "${material.title}"?`)) return;
    const form = useForm({});
    form.delete(`/admin/lms-materials/${material.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengelolaan Konten LMS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pengelolaan Konten LMS</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola materi video, PDF, dan modul pelatihan</p>
                    </div>
                    <Link
                        href="/admin/lms-materials/create"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Materi
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Materi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Tipe
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Dibuat
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="material in materials.data" :key="material.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                            <FileText class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ material.title }}
                                            </div>
                                            <div v-if="material.description" class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                                                {{ material.description }}
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                <a :href="fileUrl(material)" target="_blank" class="hover:underline">Lihat file</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ typeLabel(material.type) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            material.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        "
                                    >
                                        {{ material.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(material.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/lms-materials/${material.id}`"
                                            class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 ring-1 ring-blue-600/20 transition-colors hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-400/30 dark:hover:bg-blue-950"
                                        >
                                            <Eye class="mr-1 h-3 w-3" />
                                            Lihat
                                        </Link>
                                        <Link
                                            :href="`/admin/lms-materials/${material.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                        >
                                            <Pencil class="mr-1 h-3 w-3" />
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            @click="deleteMaterial(material)"
                                            class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-400/30 dark:hover:bg-red-950"
                                        >
                                            <Trash2 class="mr-1 h-3 w-3" />
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="materials.data.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada materi. Tambahkan materi pertama.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
