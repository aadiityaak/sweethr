<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { computed } from 'vue';

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
    { title: 'Tugas', href: '/admin/lms-assignments' },
    { title: 'Tambah', href: '/admin/lms-assignments/create' },
];

const form = useForm({
    title: '',
    description: '',
    instructions: '',
    lms_category_id: null as number | null,
    due_at: '' as string | null,
    max_score: 100,
    max_attempts: 1,
    is_active: true,
});

const flattenCategories = (items: LmsCategory[], depth = 0): Array<{ id: number; label: string }> => {
    const result: Array<{ id: number; label: string }> = [];
    for (const item of items) {
        const prefix = depth > 0 ? `${'—'.repeat(depth)} ` : '';
        result.push({ id: item.id, label: `${prefix}${item.name}` });
        if (item.children?.length) result.push(...flattenCategories(item.children, depth + 1));
    }
    return result;
};

const categoryOptions = computed(() => flattenCategories(categories));

const submit = () => {
    form.post('/admin/lms-assignments', {
        onBefore: () => {
            if (!form.due_at) form.due_at = null;
        },
    });
};
</script>

<template>
    <Head title="Tambah Tugas LMS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/lms-assignments"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Tugas</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Buat tugas beserta instruksi dan deadline</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.title }"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                        <select
                            v-model="form.lms_category_id"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.lms_category_id }"
                        >
                            <option :value="null" disabled>Pilih kategori...</option>
                            <option v-for="opt in categoryOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
                        </select>
                        <p v-if="form.errors.lms_category_id" class="mt-1 text-xs text-red-600">{{ form.errors.lms_category_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi (Opsional)</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.description }"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Instruksi (Opsional)</label>
                        <textarea
                            v-model="form.instructions"
                            rows="8"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.instructions }"
                        />
                        <p v-if="form.errors.instructions" class="mt-1 text-xs text-red-600">{{ form.errors.instructions }}</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline (Opsional)</label>
                            <input
                                v-model="form.due_at"
                                type="datetime-local"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.due_at }"
                            />
                            <p v-if="form.errors.due_at" class="mt-1 text-xs text-red-600">{{ form.errors.due_at }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Skor Maks</label>
                            <input
                                v-model.number="form.max_score"
                                type="number"
                                min="1"
                                max="100000"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.max_score }"
                            />
                            <p v-if="form.errors.max_score" class="mt-1 text-xs text-red-600">{{ form.errors.max_score }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Attempt</label>
                        <input
                            v-model.number="form.max_attempts"
                            type="number"
                            min="1"
                            max="1000"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.max_attempts }"
                        />
                        <p v-if="form.errors.max_attempts" class="mt-1 text-xs text-red-600">{{ form.errors.max_attempts }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label class="text-sm text-gray-700 dark:text-gray-300">Aktif</label>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
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
