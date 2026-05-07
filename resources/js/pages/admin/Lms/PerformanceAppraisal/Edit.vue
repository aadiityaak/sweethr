<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { ArrowLeft, Bold, Link2, List, ListOrdered, Save, Underline as UnderlineIcon, Unlink } from 'lucide-vue-next';
import { computed } from 'vue';

interface Employee {
    id: number;
    name: string;
    employee_id?: string | null;
    position_id?: number | null;
    position?: { id: number; title: string } | null;
}

interface Appraisal {
    id: number;
    user_id: number;
    evaluated_at: string;
    quality_work: number;
    quantity_work: number;
    task_knowledge: number;
    discipline: number;
    teamwork: number;
    communication: number;
    initiative: number;
    target_realization: number;
    time_management: number;
    attitude: number;
    adaptability: number;
    leadership_delegation: number | null;
    leadership_development: number | null;
    feedback: string | null;
}

interface Props {
    employees: Employee[];
    appraisal: Appraisal;
    parameters: Array<{
        key: string;
        group: string;
        label: string;
        is_active: boolean;
        visible_position_ids?: number[] | null;
    }>;
}

const { employees, appraisal, parameters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Performance Appraisal', href: '/admin/lms-performance-appraisals' },
    { title: 'Edit', href: `/admin/lms-performance-appraisals/${appraisal.id}/edit` },
];

const form = useForm({
    user_id: appraisal.user_id as number | null,
    evaluated_at: appraisal.evaluated_at,
    quality_work: appraisal.quality_work,
    quantity_work: appraisal.quantity_work,
    task_knowledge: appraisal.task_knowledge,
    discipline: appraisal.discipline,
    teamwork: appraisal.teamwork,
    communication: appraisal.communication,
    initiative: appraisal.initiative,
    target_realization: appraisal.target_realization,
    time_management: appraisal.time_management,
    attitude: appraisal.attitude,
    adaptability: appraisal.adaptability,
    leadership_delegation: appraisal.leadership_delegation as number | null,
    leadership_development: appraisal.leadership_development as number | null,
    feedback: appraisal.feedback ?? '',
});

const selectedEmployee = computed(() => {
    if (!form.user_id) return null;
    return employees.find((e) => e.id === form.user_id) ?? null;
});

const selectedPositionId = computed(() => {
    return selectedEmployee.value?.position_id ?? null;
});

const scoreOptions = [1, 2, 3, 4, 5];

const employeeLabel = (e: Employee) => {
    const empId = e.employee_id ? ` (${e.employee_id})` : '';
    const pos = e.position?.title ? ` - ${e.position.title}` : '';
    return `${e.name}${empId}${pos}`;
};

const editor = useEditor({
    content: form.feedback || '',
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
        Placeholder.configure({
            placeholder: 'Tulis evaluasi / feedback di sini...',
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none min-h-[160px] max-h-[40vh] overflow-y-auto p-3 text-sm text-gray-700 focus:outline-none dark:prose-invert dark:text-gray-200',
        },
    },
    onUpdate: ({ editor }) => {
        form.feedback = editor.getHTML();
    },
});

const groupedParameters = computed(() => {
    const groups: Array<{ group: string; params: typeof parameters }> = [];
    const idxByName = new Map<string, number>();

    for (const p of parameters) {
        const visibleIds = p.visible_position_ids ?? [];
        if (form.user_id) {
            if (Array.isArray(visibleIds) && visibleIds.length > 0) {
                if (selectedPositionId.value === null) continue;
                if (!visibleIds.includes(selectedPositionId.value)) continue;
            }
        }
        const name = p.group || 'Lainnya';
        const existingIdx = idxByName.get(name);
        if (existingIdx === undefined) {
            idxByName.set(name, groups.length);
            groups.push({ group: name, params: [p] });
        } else {
            groups[existingIdx].params.push(p);
        }
    }

    return groups;
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

const submit = () => {
    form.put(`/admin/lms-performance-appraisals/${appraisal.id}`);
};
</script>

<template>
    <Head title="Edit Performance Appraisal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6 pt-6">
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/lms-performance-appraisals"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Performance Appraisal</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Perbarui penilaian kinerja</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Karyawan</label>
                            <select
                                v-model="form.user_id"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.user_id }"
                            >
                                <option :value="null">Pilih karyawan</option>
                                <option v-for="e in employees" :key="e.id" :value="e.id">{{ employeeLabel(e) }}</option>
                            </select>
                            <p v-if="form.errors.user_id" class="mt-1 text-xs text-red-600">{{ form.errors.user_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Penilaian</label>
                            <input
                                v-model="form.evaluated_at"
                                type="date"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.evaluated_at }"
                            />
                            <p v-if="form.errors.evaluated_at" class="mt-1 text-xs text-red-600">{{ form.errors.evaluated_at }}</p>
                        </div>
                    </div>

                    <div v-for="(g, idx) in groupedParameters" :key="g.group" class="rounded-xl border border-gray-200/60 p-5 dark:border-gray-800/60">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ idx + 1 }}. {{ g.group }}</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-4">
                            <div v-for="p in g.params" :key="p.key">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ p.label }}</label>
                                <select
                                    v-model="(form as any)[p.key]"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': (form.errors as any)[p.key] }"
                                >
                                    <option v-for="n in scoreOptions" :key="`${p.key}-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="(form.errors as any)[p.key]" class="mt-1 text-xs text-red-600">{{ (form.errors as any)[p.key] }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Evaluasi / Feedback</label>
                        <div class="mt-1 overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800">
                            <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 p-2 dark:border-gray-700">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :class="editor?.isActive('bold') ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                    :disabled="!editor?.can().chain().focus().toggleBold().run()"
                                    @click="editor?.chain().focus().toggleBold().run()"
                                >
                                    <Bold class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :class="editor?.isActive('underline') ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                    :disabled="!editor?.can().chain().focus().toggleUnderline().run()"
                                    @click="editor?.chain().focus().toggleUnderline().run()"
                                >
                                    <UnderlineIcon class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :class="editor?.isActive('bulletList') ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                    @click="editor?.chain().focus().toggleBulletList().run()"
                                >
                                    <List class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :class="editor?.isActive('orderedList') ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                    @click="editor?.chain().focus().toggleOrderedList().run()"
                                >
                                    <ListOrdered class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :class="editor?.isActive('link') ? 'bg-gray-100 dark:bg-gray-700' : ''"
                                    @click="setLink"
                                >
                                    <Link2 class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                    :disabled="!editor?.isActive('link')"
                                    @click="editor?.chain().focus().unsetLink().run()"
                                >
                                    <Unlink class="h-4 w-4" />
                                </button>
                            </div>
                            <EditorContent :editor="editor" />
                        </div>
                        <p v-if="form.errors.feedback" class="mt-1 text-xs text-red-600">{{ form.errors.feedback }}</p>
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
