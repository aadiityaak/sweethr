<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

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
    assignments: {
        data: LmsAssignment[];
        links: any[];
        meta?: any;
    };
    categories: Array<{ id: number; name: string; parent_id: number | null; children?: Array<{ id: number; name: string; parent_id: number | null }> }>;
    filters?: {
        category?: number | null;
        search?: string | null;
    };
}

const { assignments, categories, filters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Tugas', href: '/admin/lms-assignments' },
];

const categoryLabel = (a: LmsAssignment) => {
    if (!a.category) return '-';
    if (a.category.parent?.name) return `${a.category.parent.name} / ${a.category.name}`;
    return a.category.name;
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

const flattenCategoryOptions = (
    items: Array<{ id: number; name: string; parent_id: number | null; children?: any[] }>,
    depth = 0,
): Array<{ id: number; label: string }> => {
    const result: Array<{ id: number; label: string }> = [];
    for (const item of items) {
        const prefix = depth > 0 ? `${'—'.repeat(depth)} ` : '';
        result.push({ id: item.id, label: `${prefix}${item.name}` });
        if (item.children?.length) {
            result.push(...flattenCategoryOptions(item.children, depth + 1));
        }
    }
    return result;
};

const categoryOptions = flattenCategoryOptions(categories);

const searchQuery = ref(filters?.search ?? '');
const selectedCategoryFilter = ref(filters?.category ? String(filters.category) : '');
let searchTimer: number | undefined;

const applyFilters = () => {
    const category = selectedCategoryFilter.value ? Number(selectedCategoryFilter.value) : undefined;
    const search = searchQuery.value.trim() || undefined;

    router.get(
        '/admin/lms-assignments',
        {
            category,
            search,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategoryFilter.value = '';
    router.get('/admin/lms-assignments');
};

watch(
    searchQuery,
    () => {
        if (searchTimer) window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            applyFilters();
        }, 400);
    },
    { flush: 'post' },
);

watch(selectedCategoryFilter, () => {
    applyFilters();
});

const deleteAssignment = (a: LmsAssignment) => {
    if (!confirm(`Yakin ingin menghapus tugas "${a.title}"?`)) return;
    const form = useForm({});
    form.delete(`/admin/lms-assignments/${a.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tugas LMS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tugas LMS</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola tugas dan deadline</p>
                    </div>
                    <Link
                        href="/admin/lms-assignments/create"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Tugas
                    </Link>
                </div>
            </div>

            <div class="mb-6 rounded-xl border border-gray-200/50 bg-white p-5 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Cari</label>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari judul / deskripsi / instruksi..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400/20"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Kategori</label>
                        <select
                            v-model="selectedCategoryFilter"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-400/20"
                        >
                            <option value="">Semua kategori</option>
                            <option v-for="opt in categoryOptions" :key="opt.id" :value="String(opt.id)">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-end">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        @click="clearFilters"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="a in assignments.data" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ a.title }}</div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Skor maks: {{ a.max_score }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ categoryLabel(a) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span v-if="a.due_at">{{ formatDate(a.due_at) }}</span>
                                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            a.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        "
                                    >
                                        {{ a.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/lms-assignments/${a.id}`"
                                            class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 ring-1 ring-blue-600/20 transition-colors hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-400/30 dark:hover:bg-blue-950"
                                        >
                                            <Eye class="mr-1 h-3 w-3" />
                                            Lihat
                                        </Link>
                                        <Link
                                            :href="`/admin/lms-assignments/${a.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                        >
                                            <Pencil class="mr-1 h-3 w-3" />
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-400/30 dark:hover:bg-red-950"
                                            @click="deleteAssignment(a)"
                                        >
                                            <Trash2 class="mr-1 h-3 w-3" />
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="assignments.data.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada tugas. Tambahkan tugas pertama.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
