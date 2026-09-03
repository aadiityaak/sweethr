<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Contract {
    id: number;
    contract_number: string;
    type: 'pkwt' | 'pkwtt';
    start_date: string;
    end_date: string | null;
    status: string;
    salary_grade: string | null;
    notes: string | null;
    user?: { id: number; name: string };
}

const props = defineProps<{ contract: Contract; users: { id: number; name: string; employee_id: string | null }[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Kontrak Kerja', href: '/admin/contracts' },
    { title: 'Edit', href: '' },
];

const form = useForm({
    type: props.contract.type,
    start_date: props.contract.start_date,
    end_date: props.contract.end_date ?? '',
    status: props.contract.status,
    salary_grade: props.contract.salary_grade ?? '',
    notes: props.contract.notes ?? '',
});

const submit = () => form.put(`/admin/contracts/${props.contract.id}`);
</script>

<template>
    <Head title="Edit Kontrak" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl space-y-6 p-6">
            <div class="flex items-center gap-3">
                <a href="/admin/contracts" class="rounded-md border p-2 hover:bg-gray-50"><ArrowLeft class="h-4 w-4" /></a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Kontrak</h1>
                    <p class="text-sm text-gray-500">{{ contract.contract_number }} — {{ contract.user?.name }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-lg border bg-white p-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipe Kontrak</label>
                    <div class="mt-1 flex gap-4">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input v-model="form.type" type="radio" value="pkwt" class="text-blue-600" /> PKWT
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input v-model="form.type" type="radio" value="pkwtt" class="text-blue-600" /> PKWTT
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input v-model="form.start_date" type="date" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        <p v-if="form.errors.start_date" class="mt-1 text-xs text-red-600">{{ form.errors.start_date }}</p>
                    </div>
                    <div v-if="form.type === 'pkwt'">
                        <label class="block text-sm font-medium text-gray-700">Tanggal Berakhir</label>
                        <input v-model="form.end_date" type="date" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        <p v-if="form.errors.end_date" class="mt-1 text-xs text-red-600">{{ form.errors.end_date }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select v-model="form.status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                            <option value="active">Aktif</option>
                            <option value="expired">Kedaluwarsa</option>
                            <option value="renewed">Diperpanjang</option>
                            <option value="terminated">Dihentikan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Grade Gaji</label>
                        <input v-model="form.salary_grade" type="text" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan</label>
                    <textarea v-model="form.notes" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-2 border-t pt-4">
                    <a href="/admin/contracts" class="rounded-md border px-4 py-2 text-sm">Batal</a>
                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        <Save class="h-4 w-4" /> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
