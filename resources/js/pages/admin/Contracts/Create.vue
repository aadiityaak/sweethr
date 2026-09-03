<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface UserOption { id: number; name: string; employee_id: string | null }

const props = defineProps<{ users: UserOption[]; preselectUserId?: number | null }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Kontrak Kerja', href: '/admin/contracts' },
    { title: 'Tambah', href: '' },
];

const form = useForm({
    user_id: props.preselectUserId ?? '',
    type: 'pkwt',
    start_date: new Date().toISOString().slice(0, 10),
    end_date: '',
    salary_grade: '',
    notes: '',
});

const submit = () => form.post('/admin/contracts');
</script>

<template>
    <Head title="Tambah Kontrak" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl space-y-6 p-6">
            <div class="flex items-center gap-3">
                <a href="/admin/contracts" class="rounded-md border p-2 hover:bg-gray-50"><ArrowLeft class="h-4 w-4" /></a>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Kontrak Kerja</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-lg border bg-white p-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Karyawan <span class="text-red-500">*</span></label>
                    <select v-model="form.user_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                        <option value="">-- Pilih Karyawan --</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                            {{ user.name }}{{ user.employee_id ? ` (${user.employee_id})` : '' }}
                        </option>
                    </select>
                    <p v-if="form.errors.user_id" class="mt-1 text-xs text-red-600">{{ form.errors.user_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipe Kontrak <span class="text-red-500">*</span></label>
                    <div class="mt-1 flex gap-4">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input v-model="form.type" type="radio" value="pkwt" class="text-blue-600" /> PKWT (Kontrak Waktu Tertentu)
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input v-model="form.type" type="radio" value="pkwtt" class="text-blue-600" /> PKWTT (Karyawan Tetap)
                        </label>
                    </div>
                    <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input v-model="form.start_date" type="date" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
                    </div>
                    <div v-if="form.type === 'pkwt'">
                        <label class="block text-sm font-medium text-gray-700">Tanggal Berakhir <span class="text-red-500">*</span></label>
                        <input v-model="form.end_date" type="date" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        <p v-if="form.errors.end_date" class="mt-1 text-xs text-red-600">{{ form.errors.end_date }}</p>
                        <p class="mt-1 text-xs text-gray-500">Alert otomatis H-60 & H-30 dikirim ke HR.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Grade Gaji</label>
                    <input v-model="form.salary_grade" type="text" placeholder="mis. G3 / Supervisor A"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan</label>
                    <textarea v-model="form.notes" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-2 border-t pt-4">
                    <a href="/admin/contracts" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        <Save class="h-4 w-4" /> Simpan Kontrak
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
