<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { Pencil, Plus, Search, ShieldAlert, Trash2, X } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Violation {
    id: number;
    code: string;
    name: string;
    category: 'ringan' | 'sedang' | 'berat';
    points: number;
    description: string | null;
    is_active: boolean;
}

interface Props {
    violations: { data: Violation[]; links: any[] };
    filters: { category: string; search: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Disiplin', href: '/admin/employee-violations' },
    { title: 'Master Pelanggaran', href: '' },
];

const search = ref(props.filters.search);
const category = ref(props.filters.category);

watch([search, category], debounce(() => {
    router.get('/admin/disciplinary-violations', { search: search.value, category: category.value },
        { preserveState: true, preserveScroll: true, replace: true });
}, 400));

const showForm = ref(false);
const editing = ref<Violation | null>(null);

const form = useForm({
    code: '', name: '', category: 'ringan', points: 5, description: '', is_active: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.is_active = true;
    showForm.value = true;
};

const openEdit = (violation: Violation) => {
    editing.value = violation;
    form.code = violation.code;
    form.name = violation.name;
    form.category = violation.category;
    form.points = violation.points;
    form.description = violation.description ?? '';
    form.is_active = violation.is_active;
    showForm.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(`/admin/disciplinary-violations/${editing.value.id}`, {
            onSuccess: () => { showForm.value = false; },
        });
    } else {
        form.post('/admin/disciplinary-violations', {
            onSuccess: () => { showForm.value = false; },
        });
    }
};

const remove = (violation: Violation) => {
    if (confirm(`Hapus kategori "${violation.name}"?`)) {
        router.delete(`/admin/disciplinary-violations/${violation.id}`, { preserveScroll: true });
    }
};

const catBadge = (c: string) => ({
    ringan: 'bg-yellow-100 text-yellow-800',
    sedang: 'bg-orange-100 text-orange-800',
    berat: 'bg-red-100 text-red-800',
}[c] ?? 'bg-gray-100 text-gray-700');
const catLabel = (c: string) => ({ ringan: 'Ringan', sedang: 'Sedang', berat: 'Berat' }[c] ?? c);
</script>

<template>
    <Head title="Master Pelanggaran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Master Kategori Pelanggaran</h1>
                    <p class="mt-1 text-sm text-gray-500">Bobot poin: Ringan 5–10 · Sedang 15–25 · Berat 35+.</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <Plus class="h-4 w-4" /> Tambah Kategori
                </button>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari kode / nama pelanggaran..."
                        class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm" />
                </div>
                <select v-model="category" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Kategori</option>
                    <option value="ringan">Ringan</option>
                    <option value="sedang">Sedang</option>
                    <option value="berat">Berat</option>
                </select>
            </div>

            <div class="overflow-x-auto rounded-lg border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nama Pelanggaran</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Poin</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="violation in violations.data" :key="violation.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ violation.code }}</td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ violation.name }}</p>
                                <p class="text-xs text-gray-500">{{ violation.description }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="catBadge(violation.category)" class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ catLabel(violation.category) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ violation.points }}</td>
                            <td class="px-4 py-3">
                                <span :class="violation.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                    class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ violation.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button @click="openEdit(violation)" class="rounded p-1.5 text-blue-600 hover:bg-blue-50">
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button @click="remove(violation)" class="rounded p-1.5 text-red-600 hover:bg-red-50">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="violations.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                <ShieldAlert class="mx-auto mb-2 h-8 w-8 text-gray-300" />
                                Belum ada kategori pelanggaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="violations.links.length > 3" class="flex justify-center gap-1">
                <button v-for="(link, i) in violations.links" :key="i" :disabled="!link.url"
                    @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    :class="link.active ? 'border-blue-600 bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    v-html="link.label" />
            </div>

            <!-- Modal Form -->
            <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showForm = false">
                <div class="w-full max-w-lg rounded-lg bg-white p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ editing ? 'Edit' : 'Tambah' }} Kategori Pelanggaran</h2>
                        <button @click="showForm = false" class="rounded p-1 hover:bg-gray-100"><X class="h-4 w-4" /></button>
                    </div>
                    <form @submit.prevent="submit" class="mt-4 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kode <span class="text-red-500">*</span></label>
                                <input v-model="form.code" type="text" required placeholder="mis. LT-03"
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                                <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Poin <span class="text-red-500">*</span></label>
                                <input v-model.number="form.points" type="number" min="1" max="100" required
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" required
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                <select v-model="form.category" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                    <option value="ringan">Ringan (5–10)</option>
                                    <option value="sedang">Sedang (15–25)</option>
                                    <option value="berat">Berat (35+)</option>
                                </select>
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input v-model="form.is_active" type="checkbox" class="text-blue-600" /> Aktif
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea v-model="form.description" rows="2" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 border-t pt-4">
                            <button type="button" @click="showForm = false" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                            <button type="submit" :disabled="form.processing"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
