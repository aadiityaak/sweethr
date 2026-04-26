<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

type QuestionType = 'multiple_choice' | 'true_false' | 'short_answer';

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
    time_limit_minutes: number | null;
    passing_score: number;
    is_active: boolean;
    category: CategoryRef | null;
    questions: LmsQuizQuestion[];
    created_at: string;
}

interface Props {
    quiz: LmsQuiz;
}

const { quiz } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Kuis', href: '/admin/lms-quizzes' },
    { title: 'Detail', href: `/admin/lms-quizzes/${quiz.id}` },
];

const categoryLabel = () => {
    if (!quiz.category) return '-';
    if (quiz.category.parent?.name) return `${quiz.category.parent.name} / ${quiz.category.name}`;
    return quiz.category.name;
};

const typeLabel = (type: QuestionType) => {
    if (type === 'multiple_choice') return 'Pilihan ganda';
    if (type === 'true_false') return 'Benar/Salah';
    return 'Jawaban singkat';
};
</script>

<template>
    <Head :title="`Kuis: ${quiz.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
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
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ quiz.title }}</h1>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ categoryLabel() }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ quiz.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                <span class="mx-2">•</span>
                                <span>Lulus: {{ quiz.passing_score }}%</span>
                                <span v-if="quiz.time_limit_minutes" class="mx-2">•</span>
                                <span v-if="quiz.time_limit_minutes">Durasi: {{ quiz.time_limit_minutes }} menit</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="`/admin/lms-quizzes/${quiz.id}/attempts`"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                        >
                            Attempt
                        </Link>
                        <Link
                            :href="`/admin/lms-quizzes/${quiz.id}/edit`"
                            class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-amber-700"
                        >
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Deskripsi</h2>
                    <p v-if="quiz.description" class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">
                        {{ quiz.description }}
                    </p>
                    <p v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada deskripsi.</p>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pertanyaan</h2>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ quiz.questions.length }} item</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Pertanyaan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Poin</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                                <tr v-for="(q, idx) in quiz.questions" :key="q.id">
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ idx + 1 }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ typeLabel(q.type) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ q.question }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ q.points }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="
                                                q.is_active
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                            "
                                        >
                                            {{ q.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="quiz.questions.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada pertanyaan. Tambahkan dari halaman Edit.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
