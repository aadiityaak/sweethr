<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

interface Props {
    availableKeys: string[];
    groups: string[];
    positions: Array<{ id: number; title: string }>;
}

const { availableKeys, groups, positions } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Performance Appraisal', href: '/admin/lms-performance-appraisals' },
    { title: 'Parameter Penilaian', href: '/admin/lms-performance-appraisal-parameters' },
    { title: 'Tambah', href: '/admin/lms-performance-appraisal-parameters/create' },
];

const form = useForm({
    key: availableKeys[0] ?? '',
    group: groups[0] ?? '',
    label: '',
    is_active: true,
    visible_position_ids: [] as number[],
});

const submit = () => {
    form.post('/admin/lms-performance-appraisal-parameters');
};
</script>

<template>
    <Head title="Tambah Parameter Penilaian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6 pt-6">
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/lms-performance-appraisal-parameters"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Parameter</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Buat item penilaian baru</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <div v-if="availableKeys.length === 0" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/30 dark:bg-amber-950/30 dark:text-amber-200">
                        Semua key parameter sudah digunakan.
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Key</label>
                            <select
                                v-model="form.key"
                                :disabled="availableKeys.length === 0"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.key }"
                            >
                                <option v-for="k in availableKeys" :key="k" :value="k">{{ k }}</option>
                            </select>
                            <p v-if="form.errors.key" class="mt-1 text-xs text-red-600">{{ form.errors.key }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group</label>
                            <input
                                v-model="form.group"
                                list="groups"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.group }"
                            />
                            <datalist id="groups">
                                <option v-for="g in groups" :key="g" :value="g"></option>
                            </datalist>
                            <p v-if="form.errors.group" class="mt-1 text-xs text-red-600">{{ form.errors.group }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label</label>
                            <input
                                v-model="form.label"
                                type="text"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.label }"
                            />
                            <p v-if="form.errors.label" class="mt-1 text-xs text-red-600">{{ form.errors.label }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            Aktif
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan yang Ditampilkan</label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kosongkan jika ingin tampil untuk semua jabatan. Bisa pilih lebih dari 1.</p>
                        <div class="mt-2 max-h-56 overflow-y-auto rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                            <div v-if="positions.length === 0" class="text-sm text-gray-500 dark:text-gray-400">Belum ada data jabatan.</div>
                            <label
                                v-for="pos in positions"
                                :key="pos.id"
                                class="flex items-center gap-2 py-1 text-sm text-gray-700 dark:text-gray-200"
                            >
                                <input v-model="form.visible_position_ids" type="checkbox" :value="pos.id" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="truncate">{{ pos.title }}</span>
                            </label>
                        </div>
                        <p v-if="form.errors.visible_position_ids" class="mt-1 text-xs text-red-600">{{ form.errors.visible_position_ids }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing || availableKeys.length === 0"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
