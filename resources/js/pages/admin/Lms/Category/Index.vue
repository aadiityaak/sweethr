<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

interface LmsCategory {
    id: number;
    parent_id: number | null;
    name: string;
    is_active: boolean;
    children?: LmsCategory[];
}

interface Props {
    categories: LmsCategory[];
}

const { categories } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Kategori', href: '/admin/lms-categories' },
];

const flattenCategories = (items: LmsCategory[], depth = 0): Array<{ category: LmsCategory; depth: number }> => {
    const result: Array<{ category: LmsCategory; depth: number }> = [];
    for (const item of items) {
        result.push({ category: item, depth });
        if (item.children?.length) {
            result.push(...flattenCategories(item.children, depth + 1));
        }
    }
    return result;
};

const rows = flattenCategories(categories);

const deleteCategory = (category: LmsCategory) => {
    if (!confirm(`Yakin ingin menghapus kategori "${category.name}"?`)) return;
    const form = useForm({});
    form.delete(`/admin/lms-categories/${category.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Kategori LMS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kategori LMS</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola kategori dan sub kategori materi</p>
                    </div>
                    <Link
                        href="/admin/lms-categories/create"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Kategori
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Nama
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="row in rows" :key="row.category.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            <span class="text-gray-400 dark:text-gray-600" v-if="row.depth > 0">
                                                {{ '—'.repeat(row.depth) }}
                                            </span>
                                            {{ row.category.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            row.category.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        "
                                    >
                                        {{ row.category.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/lms-categories/${row.category.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                        >
                                            <Pencil class="mr-1 h-3 w-3" />
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-400/30 dark:hover:bg-red-950"
                                            @click="deleteCategory(row.category)"
                                        >
                                            <Trash2 class="mr-1 h-3 w-3" />
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="rows.length === 0">
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada kategori. Tambahkan kategori pertama.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
