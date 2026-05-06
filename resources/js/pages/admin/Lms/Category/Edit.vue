<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

interface ParentCategory {
    id: number;
    name: string;
}

interface LmsCategory {
    id: number;
    name: string;
    parent_id: number | null;
    is_active: boolean;
    visible_roles?: string[] | null;
}

interface Props {
    category: LmsCategory;
    parents: ParentCategory[];
}

const { category, parents } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Kategori', href: '/admin/lms-categories' },
    { title: 'Edit', href: `/admin/lms-categories/${category.id}/edit` },
];

const roleOptions = [
    { value: 'employee', label: 'Karyawan' },
    { value: 'manager', label: 'Manager' },
    { value: 'admin', label: 'Admin' },
];

const form = useForm({
    name: category.name,
    parent_id: category.parent_id as number | null,
    is_active: category.is_active,
    visible_roles: (category.visible_roles ?? []) as string[],
});

const submit = () => {
    form.put(`/admin/lms-categories/${category.id}`);
};
</script>

<template>
    <Head :title="`Edit Kategori: ${category.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6 pt-6">
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/lms-categories"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Kategori</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">{{ category.name }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent (Opsional)</label>
                        <select
                            v-model="form.parent_id"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            :class="{ 'border-red-500': form.errors.parent_id }"
                        >
                            <option :value="null">Tanpa parent (kategori utama)</option>
                            <option v-for="p in parents" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <p v-if="form.errors.parent_id" class="mt-1 text-xs text-red-600">{{ form.errors.parent_id }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <label class="text-sm text-gray-700 dark:text-gray-300">Aktif</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Visibility (Role)</label>
                        <div class="mt-2 space-y-2">
                            <label v-for="opt in roleOptions" :key="opt.value" class="flex items-center gap-2">
                                <input v-model="form.visible_roles" type="checkbox" :value="opt.value" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ opt.label }}</span>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Jika tidak memilih role apa pun, kategori akan terlihat untuk semua role.
                        </p>
                        <p v-if="form.errors.visible_roles" class="mt-1 text-xs text-red-600">{{ form.errors.visible_roles }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
