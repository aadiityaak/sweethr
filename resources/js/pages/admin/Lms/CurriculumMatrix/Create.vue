<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, Save } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Props {
    positions: { id: number; title: string; level: number }[];
    categories: { id: number; name: string }[];
    materials: { id: number; title: string }[];
    quizzes: { id: number; title: string }[];
    assignments: { id: number; title: string }[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Matriks Kurikulum', href: '/admin/curriculum-matrix' },
    { title: 'Tambah', href: '' },
];

const form = useForm({
    position_id: '',
    item_type: 'material',
    lms_category_id: '',
    lms_material_id: '',
    lms_quiz_id: '',
    lms_assignment_id: '',
    is_mandatory: true,
    deadline_days: 30,
});

const itemOptions = computed(() => {
    if (form.item_type === 'material') return props.materials;
    if (form.item_type === 'quiz') return props.quizzes;
    return props.assignments;
});

const selectedItem = computed({
    get: () => form.item_type === 'material' ? form.lms_material_id : form.item_type === 'quiz' ? form.lms_quiz_id : form.lms_assignment_id,
    set: (val: string) => {
        form.lms_material_id = form.item_type === 'material' ? val : '';
        form.lms_quiz_id = form.item_type === 'quiz' ? val : '';
        form.lms_assignment_id = form.item_type === 'assignment' ? val : '';
    },
});

const submit = () => form.post('/admin/curriculum-matrix');
</script>

<template>
    <Head title="Tambah Item Kurikulum" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl space-y-6 p-6">
            <div class="flex items-center gap-3">
                <a href="/admin/curriculum-matrix" class="rounded-md border p-2 hover:bg-gray-50"><ArrowLeft class="h-4 w-4" /></a>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Item Kurikulum</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-lg border bg-white p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jabatan <span class="text-red-500">*</span></label>
                        <select v-model="form.position_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="">-- Pilih Jabatan --</option>
                            <option v-for="pos in positions" :key="pos.id" :value="pos.id">{{ pos.title }}</option>
                        </select>
                        <p v-if="form.errors.position_id" class="mt-1 text-xs text-red-600">{{ form.errors.position_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipe Item <span class="text-red-500">*</span></label>
                        <select v-model="form.item_type" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="material">Materi</option>
                            <option value="quiz">Kuis / Ujian</option>
                            <option value="assignment">Tugas Praktik</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ form.item_type === 'material' ? 'Materi' : form.item_type === 'quiz' ? 'Kuis/Ujian' : 'Tugas' }} <span class="text-red-500">*</span>
                    </label>
                    <select v-model="selectedItem" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">-- Pilih --</option>
                        <option v-for="opt in itemOptions" :key="opt.id" :value="opt.id">{{ opt.title }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori (opsional)</label>
                    <select v-model="form.lms_category_id" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">-- Tidak ada --</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Batas Waktu (hari sejak assignment)</label>
                        <input v-model="form.deadline_days" type="number" min="1" max="365"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input v-model="form.is_mandatory" type="checkbox" class="text-blue-600" /> Modul wajib
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t pt-4">
                    <a href="/admin/curriculum-matrix" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        <Save class="h-4 w-4" /> Simpan
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
