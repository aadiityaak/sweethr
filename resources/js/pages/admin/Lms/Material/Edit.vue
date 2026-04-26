<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Bold, Italic, Link2, List, ListOrdered, Save, Underline, Upload } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

type MaterialType = 'video' | 'pdf' | 'module';

interface LmsMaterial {
    id: number;
    title: string;
    description: string | null;
    type: MaterialType;
    file_path: string;
    is_active: boolean;
    created_at: string;
}

interface Props {
    material: LmsMaterial;
}

const { material } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Pengelolaan Konten', href: '/admin/lms-materials' },
    { title: 'Edit', href: `/admin/lms-materials/${material.id}/edit` },
];

const form = useForm({
    title: material.title,
    description: material.description ?? '',
    type: material.type,
    file: null as File | null,
    is_active: material.is_active,
});

const descriptionEditor = ref<HTMLDivElement | null>(null);

const syncDescriptionFromEditor = () => {
    if (!descriptionEditor.value) return;
    form.description = descriptionEditor.value.innerHTML;
};

const applyEditorCommand = (command: string, value?: string) => {
    descriptionEditor.value?.focus();
    document.execCommand(command, false, value);
    syncDescriptionFromEditor();
};

const addLink = () => {
    const url = window.prompt('Masukkan URL');
    if (!url) return;
    applyEditorCommand('createLink', url);
};

const accept = computed(() => {
    if (form.type === 'pdf') return 'application/pdf';
    if (form.type === 'video') return 'video/*';
    return '';
});

const currentFileUrl = computed(() => `/storage/${material.file_path}`);

onMounted(() => {
    if (!descriptionEditor.value) return;
    descriptionEditor.value.innerHTML = form.description || '';
});

watch(
    () => form.description,
    (value) => {
        if (!descriptionEditor.value) return;
        const nextValue = value || '';
        if (descriptionEditor.value.innerHTML !== nextValue) {
            descriptionEditor.value.innerHTML = nextValue;
        }
    },
);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.file = file;
};

const submit = () => {
    form.post(`/admin/lms-materials/${material.id}/update`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Edit Materi: ${material.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/lms-materials"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Materi</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">{{ material.title }}</p>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi (Opsional)</label>
                        <div
                            class="mt-1 overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800"
                            :class="{ 'border-red-500': form.errors.description }"
                        >
                            <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900/40">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="applyEditorCommand('bold')"
                                >
                                    <Bold class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="applyEditorCommand('italic')"
                                >
                                    <Italic class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="applyEditorCommand('underline')"
                                >
                                    <Underline class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="applyEditorCommand('insertUnorderedList')"
                                >
                                    <List class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="applyEditorCommand('insertOrderedList')"
                                >
                                    <ListOrdered class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="addLink"
                                >
                                    <Link2 class="h-4 w-4" />
                                </button>
                            </div>
                            <div
                                ref="descriptionEditor"
                                contenteditable="true"
                                class="prose prose-sm max-w-none p-3 text-sm text-gray-700 focus:outline-none dark:prose-invert dark:text-gray-200"
                                @input="syncDescriptionFromEditor"
                            ></div>
                        </div>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                            <select
                                v-model="form.type"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.type }"
                            >
                                <option value="video">Video</option>
                                <option value="pdf">PDF</option>
                                <option value="module">Modul</option>
                            </select>
                            <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                        </div>

                        <div class="flex items-center gap-2 pt-7">
                            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label class="text-sm text-gray-700 dark:text-gray-300">Aktif</label>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            File saat ini:
                            <a :href="currentFileUrl" target="_blank" class="font-medium text-blue-600 hover:underline dark:text-blue-400">
                                Buka
                            </a>
                        </div>
                        <label class="mt-4 block text-sm font-medium text-gray-700 dark:text-gray-300">Ganti File (Opsional)</label>
                        <div class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-10 dark:border-gray-600">
                            <div class="text-center">
                                <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                <div class="mt-4">
                                    <label class="cursor-pointer">
                                        <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">Pilih file</span>
                                        <input type="file" class="sr-only" :accept="accept" @change="handleFileChange" />
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maks 50MB</p>
                                </div>
                                <p v-if="form.file" class="mt-3 text-xs text-gray-700 dark:text-gray-300">{{ form.file.name }}</p>
                            </div>
                        </div>
                        <p v-if="form.errors.file" class="mt-1 text-xs text-red-600">{{ form.errors.file }}</p>
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
