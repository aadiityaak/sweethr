<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronDown, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface LmsCategory {
    id: number;
    parent_id: number | null;
    name: string;
    is_active: boolean;
    materials_count?: number;
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

const materialsHref = (categoryId: number) => `/admin/lms-materials?category=${categoryId}`;

const openMap = ref<Record<number, boolean>>({});
const isOpen = (categoryId: number) => !!openMap.value[categoryId];
const toggleOpen = (categoryId: number) => {
    openMap.value[categoryId] = !openMap.value[categoryId];
};

const totalMaterialsCount = (category: LmsCategory): number => {
    const own = category.materials_count ?? 0;
    const children = category.children ?? [];
    if (!children.length) return own;
    return own + children.reduce((sum, child) => sum + totalMaterialsCount(child), 0);
};

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
                <div v-if="categories.length === 0" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada kategori. Tambahkan kategori pertama.
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-800">
                    <div v-for="category in categories" :key="category.id" class="bg-white dark:bg-gray-950">
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="min-w-0">
                                <Link
                                    :href="materialsHref(category.id)"
                                    class="block truncate text-sm font-medium text-gray-900 hover:underline dark:text-white"
                                >
                                    {{ category.name }}
                                </Link>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ totalMaterialsCount(category) }} materi</div>
                                <div class="mt-2">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            category.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        "
                                    >
                                        {{ category.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/admin/lms-categories/${category.id}/edit`"
                                    class="inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                >
                                    <Pencil class="mr-1 h-3 w-3" />
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-400/30 dark:hover:bg-red-950"
                                    @click="deleteCategory(category)"
                                >
                                    <Trash2 class="mr-1 h-3 w-3" />
                                    Hapus
                                </button>
                                <button
                                    v-if="category.children?.length"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1.5 text-gray-700 ring-1 ring-gray-200 transition-colors hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800"
                                    @click="toggleOpen(category.id)"
                                    :aria-expanded="isOpen(category.id)"
                                >
                                    <ChevronDown class="h-4 w-4 transition-transform" :class="isOpen(category.id) ? 'rotate-180' : ''" />
                                </button>
                            </div>
                        </div>

                        <div v-if="category.children?.length && isOpen(category.id)" class="bg-gray-50/50 dark:bg-gray-900/30">
                            <div class="px-6 pb-5">
                                <div class="space-y-2">
                                    <div
                                        v-for="row in flattenCategories(category.children, 1)"
                                        :key="row.category.id"
                                        class="flex items-center justify-between gap-4 rounded-lg border border-gray-200/50 bg-white px-4 py-3 shadow-sm dark:border-gray-800/50 dark:bg-gray-950"
                                    >
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-400 dark:text-gray-600" v-if="row.depth > 0">
                                                    {{ '—'.repeat(row.depth) }}
                                                </span>
                                                <Link
                                                    :href="materialsHref(row.category.id)"
                                                    class="truncate text-sm font-medium text-gray-900 hover:underline dark:text-white"
                                                >
                                                    {{ row.category.name }}
                                                </Link>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ totalMaterialsCount(row.category) }} materi
                                            </div>
                                            <div class="mt-2">
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
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
