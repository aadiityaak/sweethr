<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type QuestionType = 'multiple_choice' | 'true_false' | 'short_answer';

interface LmsCategory {
    id: number;
    parent_id: number | null;
    name: string;
    is_active: boolean;
    children?: LmsCategory[];
}

interface CategoryRef {
    id: number;
    name: string;
    parent?: CategoryRef | null;
}

interface LmsQuizQuestion {
    id: number;
    type: QuestionType;
    question: string;
    options: string[] | null;
    correct_answer: any;
    points: number;
    order: number;
    is_active: boolean;
}

interface LmsQuiz {
    id: number;
    title: string;
    description: string | null;
    lms_category_id: number;
    time_limit_minutes: number | null;
    passing_score: number;
    is_active: boolean;
    category: CategoryRef | null;
    questions: LmsQuizQuestion[];
}

interface Props {
    quiz: LmsQuiz;
    categories: LmsCategory[];
}

const { quiz, categories } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Kuis', href: '/admin/lms-quizzes' },
    { title: 'Edit', href: `/admin/lms-quizzes/${quiz.id}/edit` },
];

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

const quizForm = useForm({
    title: quiz.title,
    description: quiz.description ?? '',
    lms_category_id: quiz.lms_category_id,
    time_limit_minutes: quiz.time_limit_minutes,
    passing_score: quiz.passing_score,
    is_active: quiz.is_active,
});

const submitQuiz = () => {
    quizForm.put(`/admin/lms-quizzes/${quiz.id}`);
};

const typeLabel = (type: QuestionType) => {
    if (type === 'multiple_choice') return 'Pilihan ganda';
    if (type === 'true_false') return 'Benar/Salah';
    return 'Jawaban singkat';
};

const editingQuestionId = ref<number | null>(null);

const questionForm = useForm({
    type: 'multiple_choice' as QuestionType,
    question: '',
    points: 1,
    order: 0,
    is_active: true,
    options: [''] as string[],
    correct_option_index: 0,
    correct_true_false: true,
    correct_short_answer: '',
});

const resetQuestionForm = () => {
    editingQuestionId.value = null;
    questionForm.reset();
    questionForm.clearErrors();
    questionForm.type = 'multiple_choice';
    questionForm.options = ['', '', '', ''];
    questionForm.correct_option_index = 0;
    questionForm.correct_true_false = true;
    questionForm.correct_short_answer = '';
    questionForm.points = 1;
    questionForm.order = 0;
    questionForm.is_active = true;
};

resetQuestionForm();

const addOption = () => {
    questionForm.options.push('');
};

const removeOption = (idx: number) => {
    questionForm.options.splice(idx, 1);
    if (questionForm.correct_option_index >= questionForm.options.length) {
        questionForm.correct_option_index = Math.max(0, questionForm.options.length - 1);
    }
};

const startEditQuestion = (q: LmsQuizQuestion) => {
    editingQuestionId.value = q.id;
    questionForm.type = q.type;
    questionForm.question = q.question;
    questionForm.points = q.points;
    questionForm.order = q.order ?? 0;
    questionForm.is_active = q.is_active;

    if (q.type === 'multiple_choice') {
        const opts = Array.isArray(q.options) ? q.options : [];
        questionForm.options = opts.length ? [...opts] : ['', '', '', ''];
        questionForm.correct_option_index = typeof q.correct_answer?.value === 'number' ? q.correct_answer.value : 0;
    } else if (q.type === 'true_false') {
        questionForm.options = ['', '', '', ''];
        questionForm.correct_true_false = Boolean(q.correct_answer?.value);
    } else {
        questionForm.options = ['', '', '', ''];
        questionForm.correct_short_answer = typeof q.correct_answer?.value === 'string' ? q.correct_answer.value : '';
    }
};

const submitQuestion = () => {
    if (!editingQuestionId.value) {
        questionForm.post(`/admin/lms-quizzes/${quiz.id}/questions`, {
            preserveScroll: true,
            onSuccess: () => resetQuestionForm(),
        });
        return;
    }

    questionForm.put(`/admin/lms-quizzes/${quiz.id}/questions/${editingQuestionId.value}`, {
        preserveScroll: true,
        onSuccess: () => resetQuestionForm(),
    });
};

const deleteQuestion = (q: LmsQuizQuestion) => {
    if (!confirm('Yakin ingin menghapus pertanyaan ini?')) return;
    const form = useForm({});
    form.delete(`/admin/lms-quizzes/${quiz.id}/questions/${q.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            if (editingQuestionId.value === q.id) resetQuestionForm();
        },
    });
};
</script>

<template>
    <Head :title="`Edit Kuis: ${quiz.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-6">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <a
                            href="/admin/lms-quizzes"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Kuis</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">{{ quiz.title }}</p>
                        </div>
                    </div>
                    <Link
                        :href="`/admin/lms-quizzes/${quiz.id}`"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        Preview
                    </Link>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <form @submit.prevent="submitQuiz" class="space-y-6 p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                            <input
                                v-model="quizForm.title"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': quizForm.errors.title }"
                            />
                            <p v-if="quizForm.errors.title" class="mt-1 text-xs text-red-600">{{ quizForm.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                            <select
                                v-model="quizForm.lms_category_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': quizForm.errors.lms_category_id }"
                            >
                                <option v-for="opt in categoryOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
                            </select>
                            <p v-if="quizForm.errors.lms_category_id" class="mt-1 text-xs text-red-600">{{ quizForm.errors.lms_category_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi (Opsional)</label>
                            <textarea
                                v-model="quizForm.description"
                                rows="4"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': quizForm.errors.description }"
                            />
                            <p v-if="quizForm.errors.description" class="mt-1 text-xs text-red-600">{{ quizForm.errors.description }}</p>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Durasi (menit)</label>
                                <input
                                    v-model.number="quizForm.time_limit_minutes"
                                    type="number"
                                    min="1"
                                    max="1440"
                                    placeholder="Opsional"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': quizForm.errors.time_limit_minutes }"
                                />
                                <p v-if="quizForm.errors.time_limit_minutes" class="mt-1 text-xs text-red-600">{{ quizForm.errors.time_limit_minutes }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nilai Lulus (%)</label>
                                <input
                                    v-model.number="quizForm.passing_score"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': quizForm.errors.passing_score }"
                                />
                                <p v-if="quizForm.errors.passing_score" class="mt-1 text-xs text-red-600">{{ quizForm.errors.passing_score }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input v-model="quizForm.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label class="text-sm text-gray-700 dark:text-gray-300">Aktif</label>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button
                                type="submit"
                                :disabled="quizForm.processing"
                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <Save class="mr-2 h-4 w-4" />
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pertanyaan</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ quiz.questions.length }} item</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">#</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Pertanyaan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Poin</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                                    <tr v-for="(q, idx) in quiz.questions" :key="q.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ idx + 1 }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ typeLabel(q.type) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ q.question }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ q.points }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                                    @click="startEditQuestion(q)"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-400/30 dark:hover:bg-red-950"
                                                    @click="deleteQuestion(q)"
                                                >
                                                    <Trash2 class="mr-1 h-3 w-3" />
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="quiz.questions.length === 0">
                                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada pertanyaan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 rounded-xl border border-gray-200/50 bg-white p-6 dark:border-gray-800/50 dark:bg-gray-950">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ editingQuestionId ? 'Edit Pertanyaan' : 'Tambah Pertanyaan' }}
                                </h3>
                                <button
                                    v-if="editingQuestionId"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                    @click="resetQuestionForm()"
                                >
                                    <X class="mr-1 h-3 w-3" />
                                    Batal
                                </button>
                            </div>

                            <form @submit.prevent="submitQuestion" class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                                        <select
                                            v-model="questionForm.type"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                            :class="{ 'border-red-500': questionForm.errors.type }"
                                        >
                                            <option value="multiple_choice">Pilihan ganda</option>
                                            <option value="true_false">Benar/Salah</option>
                                            <option value="short_answer">Jawaban singkat</option>
                                        </select>
                                        <p v-if="questionForm.errors.type" class="mt-1 text-xs text-red-600">{{ questionForm.errors.type }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Poin</label>
                                        <input
                                            v-model.number="questionForm.points"
                                            type="number"
                                            min="1"
                                            max="1000"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                            :class="{ 'border-red-500': questionForm.errors.points }"
                                        />
                                        <p v-if="questionForm.errors.points" class="mt-1 text-xs text-red-600">{{ questionForm.errors.points }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan</label>
                                        <input
                                            v-model.number="questionForm.order"
                                            type="number"
                                            min="0"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                            :class="{ 'border-red-500': questionForm.errors.order }"
                                        />
                                        <p v-if="questionForm.errors.order" class="mt-1 text-xs text-red-600">{{ questionForm.errors.order }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pertanyaan</label>
                                    <textarea
                                        v-model="questionForm.question"
                                        rows="3"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                        :class="{ 'border-red-500': questionForm.errors.question }"
                                    />
                                    <p v-if="questionForm.errors.question" class="mt-1 text-xs text-red-600">{{ questionForm.errors.question }}</p>
                                </div>

                                <div v-if="questionForm.type === 'multiple_choice'">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Opsi</label>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 ring-1 ring-blue-600/20 hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-400/30 dark:hover:bg-blue-950"
                                            @click="addOption"
                                        >
                                            <Plus class="mr-1 h-3 w-3" />
                                            Tambah opsi
                                        </button>
                                    </div>
                                    <div class="mt-2 space-y-2">
                                        <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex items-center gap-2">
                                            <input
                                                v-model="questionForm.options[idx]"
                                                type="text"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                            />
                                            <button
                                                v-if="questionForm.options.length > 2"
                                                type="button"
                                                class="inline-flex items-center rounded-md bg-gray-100 px-2 py-2 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                                @click="removeOption(idx)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="questionForm.errors.options" class="mt-1 text-xs text-red-600">{{ questionForm.errors.options }}</p>

                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jawaban Benar</label>
                                        <select
                                            v-model.number="questionForm.correct_option_index"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                            :class="{ 'border-red-500': questionForm.errors.correct_option_index }"
                                        >
                                            <option v-for="(opt, idx) in questionForm.options" :key="idx" :value="idx">
                                                {{ opt ? opt : `Opsi ${idx + 1}` }}
                                            </option>
                                        </select>
                                        <p v-if="questionForm.errors.correct_option_index" class="mt-1 text-xs text-red-600">{{ questionForm.errors.correct_option_index }}</p>
                                    </div>
                                </div>

                                <div v-else-if="questionForm.type === 'true_false'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jawaban Benar</label>
                                    <select
                                        v-model="questionForm.correct_true_false"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                        :class="{ 'border-red-500': questionForm.errors.correct_true_false }"
                                    >
                                        <option :value="true">Benar</option>
                                        <option :value="false">Salah</option>
                                    </select>
                                    <p v-if="questionForm.errors.correct_true_false" class="mt-1 text-xs text-red-600">{{ questionForm.errors.correct_true_false }}</p>
                                </div>

                                <div v-else>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jawaban Benar (Opsional)</label>
                                    <input
                                        v-model="questionForm.correct_short_answer"
                                        type="text"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                        :class="{ 'border-red-500': questionForm.errors.correct_short_answer }"
                                    />
                                    <p v-if="questionForm.errors.correct_short_answer" class="mt-1 text-xs text-red-600">{{ questionForm.errors.correct_short_answer }}</p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input v-model="questionForm.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                    <label class="text-sm text-gray-700 dark:text-gray-300">Aktif</label>
                                </div>

                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        type="submit"
                                        :disabled="questionForm.processing"
                                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <Save class="mr-2 h-4 w-4" />
                                        {{ editingQuestionId ? 'Simpan Pertanyaan' : 'Tambah Pertanyaan' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
