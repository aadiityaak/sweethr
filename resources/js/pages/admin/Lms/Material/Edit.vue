<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import {
    AlignCenter,
    AlignLeft,
    AlignRight,
    ArrowLeft,
    Bold,
    Code,
    Heading1,
    Heading2,
    Link2,
    List,
    ListOrdered,
    Minus,
    Quote,
    Redo2,
    Save,
    Strikethrough,
    Underline as UnderlineIcon,
    Undo2,
    Unlink,
    Upload,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';

interface LmsCategory {
    id: number;
    parent_id: number | null;
    name: string;
    is_active: boolean;
    children?: LmsCategory[];
}

interface LmsMaterial {
    id: number;
    title: string;
    description: string | null;
    lms_category_id: number | null;
    category: { id: number; name: string; parent?: { id: number; name: string } | null } | null;
    file_path: string;
    thumbnail_path: string | null;
    is_active: boolean;
    created_at: string;
}

interface Props {
    material: LmsMaterial;
    categories: LmsCategory[];
}

const { material, categories } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Pengelolaan Konten', href: '/admin/lms-materials' },
    { title: 'Edit', href: `/admin/lms-materials/${material.id}/edit` },
];

const form = useForm({
    title: material.title,
    description: material.description ?? '',
    lms_category_id: material.lms_category_id,
    file: null as File | null,
    thumbnail: null as File | null,
    is_active: material.is_active,
});

const editor = useEditor({
    content: form.description || '',
    extensions: [
        StarterKit,
        Underline,
        Link.configure({
            openOnClick: false,
            autolink: true,
            linkOnPaste: true,
            HTMLAttributes: {
                target: '_blank',
                rel: 'noopener noreferrer',
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Placeholder.configure({
            placeholder: 'Tulis deskripsi materi di sini...',
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none min-h-[320px] max-h-[60vh] overflow-y-auto p-3 text-sm text-gray-700 focus:outline-none dark:prose-invert dark:text-gray-200',
        },
    },
    onUpdate: ({ editor }) => {
        form.description = editor.getHTML();
    },
});

const setLink = () => {
    if (!editor.value) return;
    const previousUrl = editor.value.getAttributes('link').href as string | undefined;
    const url = window.prompt('Masukkan URL', previousUrl || '');
    if (url === null) return;
    if (url === '') {
        editor.value.chain().focus().unsetLink().run();
        return;
    }
    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const currentFileUrl = computed(() => `/storage/${material.file_path}`);
const currentThumbnailUrl = computed(() => (material.thumbnail_path ? `/storage/${material.thumbnail_path}` : null));

const thumbnailPreviewUrl = ref<string | null>(null);
const displayedThumbnailUrl = computed(() => thumbnailPreviewUrl.value || currentThumbnailUrl.value);

const revokeThumbnailPreviewUrl = () => {
    if (!thumbnailPreviewUrl.value) return;
    URL.revokeObjectURL(thumbnailPreviewUrl.value);
    thumbnailPreviewUrl.value = null;
};

onBeforeUnmount(() => {
    revokeThumbnailPreviewUrl();
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.file = file;
};

const fileInputRef = ref<HTMLInputElement | null>(null);

const openFilePicker = () => {
    fileInputRef.value?.click();
};

const handleThumbnailChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.thumbnail = file;
    revokeThumbnailPreviewUrl();
    if (file) {
        thumbnailPreviewUrl.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(`/admin/lms-materials/${material.id}/update`, {
        forceFormData: true,
    });
};

const flattenCategories = (items: LmsCategory[], depth = 0): Array<{ id: number; label: string }> => {
    const result: Array<{ id: number; label: string }> = [];
    for (const item of items) {
        const prefix = depth > 0 ? `${'—'.repeat(depth)} ` : '';
        result.push({ id: item.id, label: `${prefix}${item.name}` });
        if (item.children?.length) {
            result.push(...flattenCategories(item.children, depth + 1));
        }
    }
    return result;
};

const categoryOptions = computed(() => flattenCategories(categories));
</script>

<template>
    <Head :title="`Edit Materi: ${material.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6 pt-6">
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/lms-materials"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="h-4 w-4" />
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
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                                >
                                    <Heading1 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                                >
                                    <Heading2 class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleBold().run()"
                                >
                                    <Bold class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleItalic().run()"
                                >
                                    <span class="font-serif italic">I</span>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleUnderline().run()"
                                >
                                    <UnderlineIcon class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleStrike().run()"
                                >
                                    <Strikethrough class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleBulletList().run()"
                                >
                                    <List class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleOrderedList().run()"
                                >
                                    <ListOrdered class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleBlockquote().run()"
                                >
                                    <Quote class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().toggleCode().run()"
                                >
                                    <Code class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="setLink"
                                >
                                    <Link2 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().unsetLink().run()"
                                >
                                    <Unlink class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().setTextAlign('left').run()"
                                >
                                    <AlignLeft class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().setTextAlign('center').run()"
                                >
                                    <AlignCenter class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().setTextAlign('right').run()"
                                >
                                    <AlignRight class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().setHorizontalRule().run()"
                                >
                                    <Minus class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-5 w-px bg-gray-200 dark:bg-gray-700"></div>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().undo().run()"
                                >
                                    <Undo2 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor"
                                    @click="editor?.chain().focus().redo().run()"
                                >
                                    <Redo2 class="h-4 w-4" />
                                </button>
                            </div>
                            <EditorContent v-if="editor" :editor="editor" />
                        </div>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                            <select
                                v-model="form.lms_category_id"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.lms_category_id }"
                            >
                                <option :value="null" disabled>Pilih kategori...</option>
                                <option v-for="opt in categoryOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
                            </select>
                            <p v-if="form.errors.lms_category_id" class="mt-1 text-xs text-red-600">{{ form.errors.lms_category_id }}</p>
                        </div>

                        <div class="flex items-center gap-2 pt-7">
                            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label class="text-sm text-gray-700 dark:text-gray-300">Aktif</label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail (Opsional)</label>
                        <div class="mt-2 flex items-start gap-4">
                            <div class="h-20 w-32 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                                <img v-if="displayedThumbnailUrl" :src="displayedThumbnailUrl" class="h-full w-full object-cover" alt="Thumbnail" />
                                <div v-else class="flex h-full w-full items-center justify-center text-xs text-gray-500 dark:text-gray-400">Tidak ada</div>
                            </div>
                            <div class="flex-1">
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:text-gray-200 dark:file:bg-gray-800 dark:file:text-gray-200 dark:hover:file:bg-gray-700"
                                    @change="handleThumbnailChange"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG/JPG/WebP, maks 5MB</p>
                                <p v-if="form.errors.thumbnail" class="mt-1 text-xs text-red-600">{{ form.errors.thumbnail }}</p>
                            </div>
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
                        <div
                            class="mt-1 flex cursor-pointer justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-10 dark:border-gray-600"
                            role="button"
                            tabindex="0"
                            @click="openFilePicker"
                            @keydown.enter.prevent="openFilePicker"
                            @keydown.space.prevent="openFilePicker"
                        >
                            <div class="text-center">
                                <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                <div class="mt-4">
                                    <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">Pilih file</span>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maks 50MB</p>
                                </div>
                                <p v-if="form.file" class="mt-3 text-xs text-gray-700 dark:text-gray-300">{{ form.file.name }}</p>
                            </div>
                            <input ref="fileInputRef" type="file" class="sr-only" @change="handleFileChange" />
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
