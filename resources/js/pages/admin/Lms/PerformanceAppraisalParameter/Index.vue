<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

interface Parameter {
    id: number;
    key: string;
    group: string;
    label: string;
    sort_order: number;
    is_active: boolean;
    managerial_only: boolean;
}

interface Props {
    parameters: Parameter[];
}

const { parameters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Performance Appraisal', href: '/admin/lms-performance-appraisals' },
    { title: 'Parameter Penilaian', href: '/admin/lms-performance-appraisal-parameters' },
];

const deleteForm = useForm({});
const destroy = (p: Parameter) => {
    if (!confirm('Yakin ingin menghapus parameter ini?')) return;
    deleteForm.delete(`/admin/lms-performance-appraisal-parameters/${p.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Parameter Penilaian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Parameter Penilaian</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola item penilaian Performance Appraisal</p>
                    </div>
                    <Link
                        href="/admin/lms-performance-appraisal-parameters/create"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah Parameter
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div v-if="parameters.length === 0" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada parameter. Tambahkan parameter untuk membuat form penilaian dinamis.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/30">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Group</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Label</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Key</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Manajerial</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Sort</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="p in parameters" :key="p.id">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ p.group }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ p.label }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ p.key }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="p.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200'"
                                    >
                                        {{ p.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ p.managerial_only ? 'Ya' : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ p.sort_order }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link
                                        :href="`/admin/lms-performance-appraisal-parameters/${p.id}/edit`"
                                        class="inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-300 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                    >
                                        <Pencil class="mr-1 h-3 w-3" />
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="ml-2 inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-300 dark:ring-red-400/30 dark:hover:bg-red-950"
                                        @click="destroy(p)"
                                    >
                                        <Trash2 class="mr-1 h-3 w-3" />
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

