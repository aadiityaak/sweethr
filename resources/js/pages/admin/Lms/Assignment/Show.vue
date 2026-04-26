<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

interface CategoryRef {
    id: number;
    name: string;
    parent?: CategoryRef | null;
}

interface LmsAssignment {
    id: number;
    title: string;
    description: string | null;
    instructions: string | null;
    due_at: string | null;
    max_score: number;
    is_active: boolean;
    category: CategoryRef | null;
    created_at: string;
}

interface Props {
    assignment: LmsAssignment;
}

const { assignment } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Tugas', href: '/admin/lms-assignments' },
    { title: 'Detail', href: `/admin/lms-assignments/${assignment.id}` },
];

const categoryLabel = () => {
    if (!assignment.category) return '-';
    if (assignment.category.parent?.name) return `${assignment.category.parent.name} / ${assignment.category.name}`;
    return assignment.category.name;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Tugas: ${assignment.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <a
                            href="/admin/lms-assignments"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ assignment.title }}</h1>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ categoryLabel() }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ assignment.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                <span class="mx-2">•</span>
                                <span>Skor maks: {{ assignment.max_score }}</span>
                                <span v-if="assignment.due_at" class="mx-2">•</span>
                                <span v-if="assignment.due_at">Deadline: {{ formatDate(assignment.due_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="`/admin/lms-assignments/${assignment.id}/submissions`"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                        >
                            Pengumpulan
                        </Link>
                        <Link
                            :href="`/admin/lms-assignments/${assignment.id}/edit`"
                            class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-amber-700"
                        >
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Deskripsi</h2>
                    <p v-if="assignment.description" class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">
                        {{ assignment.description }}
                    </p>
                    <p v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada deskripsi.</p>
                </div>

                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Instruksi</h2>
                    <p v-if="assignment.instructions" class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">
                        {{ assignment.instructions }}
                    </p>
                    <p v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada instruksi.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
