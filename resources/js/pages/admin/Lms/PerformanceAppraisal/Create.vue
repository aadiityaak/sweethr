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
    subordinates_count: number;
}

interface Props {
    employees: Employee[];
}

const { employees } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Performance Appraisal', href: '/admin/lms-performance-appraisals' },
    { title: 'Tambah', href: '/admin/lms-performance-appraisals/create' },
];

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    user_id: null as number | null,
    evaluated_at: today,
    quality_work: 3,
    quantity_work: 3,
    task_knowledge: 3,
    discipline: 3,
    teamwork: 3,
    communication: 3,
    initiative: 3,
    target_realization: 3,
    time_management: 3,
    attitude: 3,
    adaptability: 3,
    leadership_delegation: null as number | null,
    leadership_development: null as number | null,
    feedback: '',
});

const selectedEmployee = computed(() => {
    if (!form.user_id) return null;
    return employees.find((e) => e.id === form.user_id) ?? null;
});

const isManager = computed(() => {
    return (selectedEmployee.value?.subordinates_count ?? 0) > 0;
});

const scoreOptions = [1, 2, 3, 4, 5];

const employeeLabel = (e: Employee) => {
    const empId = e.employee_id ? ` (${e.employee_id})` : '';
    const marker = e.subordinates_count > 0 ? ' - Manajerial' : '';
    return `${e.name}${empId}${marker}`;
};

const submit = () => {
    form.post('/admin/lms-performance-appraisals');
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
</script>

<template>
    <Head title="Tambah Performance Appraisal" />

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
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Performance Appraisal</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Form penilaian kinerja</p>
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

                    <div class="rounded-xl border border-gray-200/60 p-5 dark:border-gray-800/60">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">1. Kompetensi Teknis (Hard Skills)</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kualitas Kerja</label>
                                <select v-model="form.quality_work" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`quality-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.quality_work" class="mt-1 text-xs text-red-600">{{ form.errors.quality_work }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kuantitas Kerja</label>
                                <select v-model="form.quantity_work" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`quantity-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.quantity_work" class="mt-1 text-xs text-red-600">{{ form.errors.quantity_work }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pengetahuan Tugas</label>
                                <select v-model="form.task_knowledge" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`knowledge-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.task_knowledge" class="mt-1 text-xs text-red-600">{{ form.errors.task_knowledge }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200/60 p-5 dark:border-gray-800/60">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">2. Perilaku Kerja (Soft Skills)</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kedisiplinan</label>
                                <select v-model="form.discipline" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`discipline-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.discipline" class="mt-1 text-xs text-red-600">{{ form.errors.discipline }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kerja Sama Tim</label>
                                <select v-model="form.teamwork" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`teamwork-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.teamwork" class="mt-1 text-xs text-red-600">{{ form.errors.teamwork }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Komunikasi</label>
                                <select v-model="form.communication" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`communication-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.communication" class="mt-1 text-xs text-red-600">{{ form.errors.communication }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Inisiatif</label>
                                <select v-model="form.initiative" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`initiative-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.initiative" class="mt-1 text-xs text-red-600">{{ form.errors.initiative }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200/60 p-5 dark:border-gray-800/60">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">3. Pencapaian Target (KPI)</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Realisasi Target</label>
                                <select
                                    v-model="form.target_realization"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    <option v-for="n in scoreOptions" :key="`target-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.target_realization" class="mt-1 text-xs text-red-600">{{ form.errors.target_realization }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Manajemen Waktu</label>
                                <select
                                    v-model="form.time_management"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    <option v-for="n in scoreOptions" :key="`time-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.time_management" class="mt-1 text-xs text-red-600">{{ form.errors.time_management }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200/60 p-5 dark:border-gray-800/60">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">4. Sikap dan Adaptabilitas</h2>
                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sikap (Attitude)</label>
                                <select v-model="form.attitude" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`attitude-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.attitude" class="mt-1 text-xs text-red-600">{{ form.errors.attitude }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adaptabilitas</label>
                                <select v-model="form.adaptability" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    <option v-for="n in scoreOptions" :key="`adaptability-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.adaptability" class="mt-1 text-xs text-red-600">{{ form.errors.adaptability }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200/60 p-5 dark:border-gray-800/60">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">5. Kepemimpinan (Khusus Level Manajerial)</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Muncul otomatis jika karyawan bertipe manajerial.</p>

                        <div v-if="isManager" class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delegasi</label>
                                <select
                                    v-model="form.leadership_delegation"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': form.errors.leadership_delegation }"
                                >
                                    <option :value="null">Pilih</option>
                                    <option v-for="n in scoreOptions" :key="`delegation-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.leadership_delegation" class="mt-1 text-xs text-red-600">{{ form.errors.leadership_delegation }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pengembangan Anggota</label>
                                <select
                                    v-model="form.leadership_development"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': form.errors.leadership_development }"
                                >
                                    <option :value="null">Pilih</option>
                                    <option v-for="n in scoreOptions" :key="`development-${n}`" :value="n">{{ n }}</option>
                                </select>
                                <p v-if="form.errors.leadership_development" class="mt-1 text-xs text-red-600">{{ form.errors.leadership_development }}</p>
                            </div>
                        </div>

                        <div v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">Pilih karyawan manajerial untuk mengisi bagian ini.</div>
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
