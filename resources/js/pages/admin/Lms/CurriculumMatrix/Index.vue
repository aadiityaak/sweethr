<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { BookOpenCheck, ClipboardList, FileText, HelpCircle, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface MatrixItem {
    id: number;
    position_id: number;
    item_type: 'material' | 'quiz' | 'assignment';
    is_mandatory: boolean;
    deadline_days: number | null;
    item_title: string;
    position?: { id: number; title: string; level: number };
    category?: { id: number; name: string } | null;
}

interface Props {
    items: MatrixItem[];
    positions: { id: number; title: string; level: number }[];
    filters: { position: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-dashboard' },
    { title: 'Matriks Kurikulum', href: '' },
];

const position = ref(props.filters.position);

watch(position, debounce(() => {
    router.get('/admin/curriculum-matrix', { position: position.value },
        { preserveState: true, preserveScroll: true, replace: true });
}, 300));

const deleteItem = (item: MatrixItem) => {
    if (confirm(`Hapus item "${item.item_title}" dari matriks?`)) {
        router.delete(`/admin/curriculum-matrix/${item.id}`, { preserveScroll: true });
    }
};

const typeIcon = (t: string) => ({ material: BookOpenCheck, quiz: HelpCircle, assignment: ClipboardList }[t] ?? FileText);
const typeLabel = (t: string) => ({ material: 'Materi', quiz: 'Kuis/Ujian', assignment: 'Tugas' }[t] ?? t);
const typeBadge = (t: string) => ({
    material: 'bg-blue-100 text-blue-800',
    quiz: 'bg-purple-100 text-purple-800',
    assignment: 'bg-amber-100 text-amber-800',
}[t] ?? 'bg-gray-100 text-gray-700');

// group by position untuk grid
const grouped = () => {
    const map = new Map<number, { title: string; items: MatrixItem[] }>();
    for (const item of props.items) {
        const posId = item.position_id;
        if (!map.has(posId)) {
            map.set(posId, { title: item.position?.title ?? '—', items: [] });
        }
        map.get(posId)!.items.push(item);
    }
    return Array.from(map.values());
};
</script>

<template>
    <Head title="Matriks Kurikulum LMS" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Matriks Kurikulum LMS</h1>
                    <p class="mt-1 text-sm text-gray-500">Modul wajib per jabatan: Cleaner, Pramusaji, Kasir, Koki, Ast SPV, SPV Outlet.</p>
                </div>
                <Link href="/admin/curriculum-matrix/create"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" /> Tambah Item
                </Link>
            </div>

            <div class="max-w-xs">
                <select v-model="position" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Jabatan</option>
                    <option v-for="pos in positions" :key="pos.id" :value="String(pos.id)">{{ pos.title }}</option>
                </select>
            </div>

            <div v-if="items.length === 0" class="rounded-lg border bg-white p-12 text-center text-sm text-gray-500">
                Belum ada item kurikulum. Klik "Tambah Item" untuk menyusun matriks per jabatan.
            </div>

            <div v-for="group in grouped()" :key="group.title" class="overflow-hidden rounded-lg border bg-white">
                <div class="border-b bg-gray-50 px-4 py-3">
                    <h2 class="font-semibold text-gray-900">{{ group.title }}</h2>
                    <p class="text-xs text-gray-500">{{ group.items.length }} item kurikulum</p>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Tipe</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Item</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Kategori</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Wajib</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500">Deadline</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="item in group.items" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-4 py-2.5">
                                <span :class="typeBadge(item.item_type)" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
                                    <component :is="typeIcon(item.item_type)" class="h-3 w-3" />
                                    {{ typeLabel(item.item_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-sm font-medium text-gray-900">{{ item.item_title }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-500">{{ item.category?.name ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span :class="item.is_mandatory ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                                    class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ item.is_mandatory ? 'Wajib' : 'Opsional' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-sm text-gray-600">
                                {{ item.deadline_days ? `${item.deadline_days} hari` : '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex justify-end gap-1">
                                    <Link :href="`/admin/curriculum-matrix/${item.id}/edit`" class="rounded p-1.5 text-blue-600 hover:bg-blue-50">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button @click="deleteItem(item)" class="rounded p-1.5 text-red-600 hover:bg-red-50">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
