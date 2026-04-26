<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import LinkExtension from '@tiptap/extension-link';
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
    Quote,
    Redo2,
    Save,
    Strikethrough,
    Underline as UnderlineIcon,
    Undo2,
    Unlink,
} from 'lucide-vue-next';
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

const instructionsEditor = useEditor({
    content: form.instructions || '',
    extensions: [
        StarterKit,
        Underline,
        LinkExtension.configure({
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
            placeholder: 'Tulis instruksi tugas di sini...',
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none min-h-[240px] max-h-[60vh] overflow-y-auto p-3 text-sm text-gray-700 focus:outline-none dark:prose-invert dark:text-gray-200',
        },
    },
    onUpdate: ({ editor }) => {
        form.instructions = editor.getHTML();
    },
});

const setInstructionsLink = () => {
    if (!instructionsEditor.value) return;
    const previousUrl = instructionsEditor.value.getAttributes('link').href as string | undefined;
    const url = window.prompt('Masukkan URL', previousUrl || '');
    if (url === null) return;
    if (url === '') {
        instructionsEditor.value.chain().focus().unsetLink().run();
        return;
    }
    instructionsEditor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

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
        <div class="px-6 pt-6">
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/lms-assignments"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="h-4 w-4" />
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
                        <div
                            class="mt-1 overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800"
                            :class="{ 'border-red-500': form.errors.instructions }"
                        >
                            <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 bg-gray-50 px-2 py-2 dark:border-gray-700 dark:bg-gray-900">
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('bold') }"
                                    @click="instructionsEditor?.chain().focus().toggleBold().run()"
                                >
                                    <Bold class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('underline') }"
                                    @click="instructionsEditor?.chain().focus().toggleUnderline().run()"
                                >
                                    <UnderlineIcon class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('strike') }"
                                    @click="instructionsEditor?.chain().focus().toggleStrike().run()"
                                >
                                    <Strikethrough class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('code') }"
                                    @click="instructionsEditor?.chain().focus().toggleCode().run()"
                                >
                                    <Code class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-6 w-px bg-gray-200 dark:bg-gray-700" />
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('heading', { level: 1 }) }"
                                    @click="instructionsEditor?.chain().focus().toggleHeading({ level: 1 }).run()"
                                >
                                    <Heading1 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('heading', { level: 2 }) }"
                                    @click="instructionsEditor?.chain().focus().toggleHeading({ level: 2 }).run()"
                                >
                                    <Heading2 class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-6 w-px bg-gray-200 dark:bg-gray-700" />
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('bulletList') }"
                                    @click="instructionsEditor?.chain().focus().toggleBulletList().run()"
                                >
                                    <List class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('orderedList') }"
                                    @click="instructionsEditor?.chain().focus().toggleOrderedList().run()"
                                >
                                    <ListOrdered class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('blockquote') }"
                                    @click="instructionsEditor?.chain().focus().toggleBlockquote().run()"
                                >
                                    <Quote class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-6 w-px bg-gray-200 dark:bg-gray-700" />
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    @click="instructionsEditor?.chain().focus().setTextAlign('left').run()"
                                >
                                    <AlignLeft class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    @click="instructionsEditor?.chain().focus().setTextAlign('center').run()"
                                >
                                    <AlignCenter class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    @click="instructionsEditor?.chain().focus().setTextAlign('right').run()"
                                >
                                    <AlignRight class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-6 w-px bg-gray-200 dark:bg-gray-700" />
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :class="{ 'bg-gray-200 dark:bg-gray-800': instructionsEditor?.isActive('link') }"
                                    @click="setInstructionsLink"
                                >
                                    <Link2 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :disabled="!instructionsEditor?.isActive('link')"
                                    @click="instructionsEditor?.chain().focus().unsetLink().run()"
                                >
                                    <Unlink class="h-4 w-4" />
                                </button>
                                <div class="mx-1 h-6 w-px bg-gray-200 dark:bg-gray-700" />
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :disabled="!instructionsEditor?.can().chain().focus().undo().run()"
                                    @click="instructionsEditor?.chain().focus().undo().run()"
                                >
                                    <Undo2 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-2 text-gray-700 hover:bg-gray-200 disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-800"
                                    :disabled="!instructionsEditor?.can().chain().focus().redo().run()"
                                    @click="instructionsEditor?.chain().focus().redo().run()"
                                >
                                    <Redo2 class="h-4 w-4" />
                                </button>
                            </div>
                            <EditorContent v-if="instructionsEditor" :editor="instructionsEditor" />
                        </div>
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
