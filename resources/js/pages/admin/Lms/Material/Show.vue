<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';

interface LmsMaterial {
    id: number;
    title: string;
    description: string | null;
    lms_category_id: number | null;
    category: { id: number; name: string; parent?: { id: number; name: string } | null } | null;
    file_path: string;
    thumbnail_path: string | null;
    is_active: boolean;
    created_at: string;
}

interface Props {
    material: LmsMaterial;
}

const { material } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Pengelolaan Konten', href: '/admin/lms-materials' },
    { title: 'Detail', href: `/admin/lms-materials/${material.id}` },
];

const fileUrl = computed(() => `/storage/${material.file_path}`);
const thumbnailUrl = computed(() => (material.thumbnail_path ? `/storage/${material.thumbnail_path}` : null));

const fileExtension = computed(() => {
    const parts = material.file_path.split('.');
    if (parts.length < 2) return '';
    return (parts.at(-1) || '').toLowerCase();
});

const isPdf = computed(() => fileExtension.value === 'pdf');
const isImage = computed(() =>
    ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'].includes(fileExtension.value),
);
const isVideo = computed(() =>
    ['mp4', 'webm', 'ogg', 'mov', 'm4v'].includes(fileExtension.value),
);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const categoryLabel = computed(() => {
    if (!material.category) return '-';
    if (material.category.parent?.name) {
        return `${material.category.parent.name} / ${material.category.name}`;
    }
    return material.category.name;
});
</script>

<template>
    <Head :title="`Materi LMS: ${material.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6 pt-6">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <a
                            href="/admin/lms-materials"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ material.title }}</h1>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ categoryLabel }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ formatDate(material.created_at) }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ material.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="`/admin/lms-materials/${material.id}/edit`"
                            class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-amber-700"
                        >
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                        <a
                            :href="fileUrl"
                            target="_blank"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Unduh
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                        <div v-if="thumbnailUrl" class="mb-6 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                            <img :src="thumbnailUrl" class="w-full object-cover" alt="Thumbnail" />
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Deskripsi</h2>
                        <div
                            v-if="material.description"
                            class="prose prose-gray dark:prose-invert mt-3 max-w-none text-sm text-gray-700 dark:text-gray-200"
                            v-html="material.description"
                        ></div>
                        <p v-else class="mt-3 text-sm text-gray-500 dark:text-gray-400">Tidak ada deskripsi.</p>
                        <div class="mt-6 text-sm text-gray-700 dark:text-gray-300">
                            File:
                            <a :href="fileUrl" target="_blank" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Buka</a>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Preview</h2>

                        <div v-if="isVideo" class="mt-4">
                            <video :src="fileUrl" controls class="w-full rounded-lg"></video>
                        </div>

                        <div v-else-if="isPdf" class="mt-4">
                            <iframe :src="fileUrl" class="h-[70vh] w-full rounded-lg border border-gray-200 dark:border-gray-800"></iframe>
                        </div>

                        <div v-else-if="isImage" class="mt-4">
                            <img :src="fileUrl" class="w-full rounded-lg border border-gray-200 dark:border-gray-800" alt="Preview" />
                        </div>

                        <div v-else class="mt-4">
                            <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                Preview tidak tersedia untuk tipe file ini. Gunakan tombol Unduh untuk membuka file.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
