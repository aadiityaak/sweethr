<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';

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
</script>

<template>
    <Head :title="`Materi LMS: ${material.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <a
                            href="/admin/lms-materials"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ material.title }}</h1>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ typeLabel(material.type) }}</span>
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
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Deskripsi</h2>
                        <p v-if="material.description" class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                            {{ material.description }}
                        </p>
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

                        <div v-if="material.type === 'video'" class="mt-4">
                            <video :src="fileUrl" controls class="w-full rounded-lg"></video>
                        </div>

                        <div v-else-if="material.type === 'pdf'" class="mt-4">
                            <iframe :src="fileUrl" class="h-[70vh] w-full rounded-lg border border-gray-200 dark:border-gray-800"></iframe>
                        </div>

                        <div v-else class="mt-4">
                            <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                                Preview modul tidak tersedia. Gunakan tombol Unduh untuk membuka file.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
