<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, HelpCircle, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

type QuestionType = 'multiple_choice' | 'true_false' | 'short_answer';

interface CategoryRef {
    id: number;
    name: string;
    parent?: CategoryRef | null;
}

interface LmsQuiz {
    id: number;
    title: string;
    description: string | null;
    time_limit_minutes: number | null;
    passing_score: number;
    category: CategoryRef | null;
}

interface Attempt {
    id: number;
    started_at: string | null;
    submitted_at: string | null;
    score: number | null;
    max_score: number | null;
    is_passed: boolean | null;
}

interface Item {
    id: number;
    type: QuestionType;
    question: string;
    options: string[] | null;
    correct_answer: any;
    points: number;
    user_answer: any;
    is_correct: boolean;
}

interface Props {
    quiz: LmsQuiz;
    attempt: Attempt;
    items: Item[];
}

const { quiz, attempt, items } = defineProps<Props>();

const categoryLabel = computed(() => {
    if (!quiz.category) return '-';
    if (quiz.category.parent?.name) return `${quiz.category.parent.name} / ${quiz.category.name}`;
    return quiz.category.name;
});

const percentage = computed(() => {
    const max = attempt.max_score ?? 0;
    const score = attempt.score ?? 0;
    if (max <= 0) return 0;
    return Math.round((score / max) * 100);
});

const formatDateTime = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const correctLabel = (item: Item) => {
    const v = item.correct_answer?.value;
    if (item.type === 'multiple_choice') {
        if (!Array.isArray(item.options) || typeof v !== 'number') return '-';
        return item.options[v] ?? '-';
    }
    if (item.type === 'true_false') {
        if (typeof v !== 'boolean') return '-';
        return v ? 'Benar' : 'Salah';
    }
    if (item.type === 'short_answer') {
        return typeof v === 'string' ? v : '-';
    }
    return '-';
};

const userLabel = (item: Item) => {
    const v = item.user_answer;
    if (item.type === 'multiple_choice') {
        if (!Array.isArray(item.options) || typeof v !== 'number') return '-';
        return item.options[v] ?? '-';
    }
    if (item.type === 'true_false') {
        if (typeof v !== 'boolean') return '-';
        return v ? 'Benar' : 'Salah';
    }
    if (item.type === 'short_answer') {
        return typeof v === 'string' ? v : '-';
    }
    return '-';
};
</script>

<template>
    <Head :title="`LMS - Hasil Kuis: ${quiz.title}`" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link :href="`/lms/quizzes/${quiz.id}`" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div class="min-w-0">
                            <h1 class="truncate text-lg font-semibold">{{ quiz.title }}</h1>
                            <p class="text-sm text-muted-foreground">{{ categoryLabel }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <HelpCircle class="h-4 w-4" />
                            Hasil
                        </h2>
                    </div>

                    <div class="p-4">
                        <div
                            class="mb-4 flex items-center gap-3 rounded-lg border p-4"
                            :class="attempt.is_passed ? 'border-green-200 bg-green-50 dark:border-green-900/30 dark:bg-green-900/10' : 'border-red-200 bg-red-50 dark:border-red-900/30 dark:bg-red-900/10'"
                        >
                            <component :is="attempt.is_passed ? CheckCircle : XCircle" class="h-6 w-6" :class="attempt.is_passed ? 'text-green-600' : 'text-destructive'" />
                            <div>
                                <p class="text-sm font-semibold">
                                    {{ attempt.is_passed ? 'Lulus' : 'Tidak lulus' }} • {{ attempt.score ?? 0 }}/{{ attempt.max_score ?? 0 }} ({{ percentage }}%)
                                </p>
                                <p class="mt-0.5 text-xs text-muted-foreground">Submit: {{ formatDateTime(attempt.submitted_at) }}</p>
                            </div>
                        </div>

                        <div class="text-sm text-muted-foreground">
                            Target lulus: {{ quiz.passing_score }}%
                            <span v-if="quiz.time_limit_minutes"> • Durasi: {{ quiz.time_limit_minutes }} menit</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="text-lg font-semibold">Pembahasan</h2>
                    </div>

                    <div class="divide-y">
                        <div v-for="(item, idx) in items" :key="item.id" class="p-4">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <p class="text-xs font-semibold text-muted-foreground">Soal {{ idx + 1 }} • {{ item.points }} poin</p>
                                <span
                                    class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-semibold"
                                    :class="item.is_correct ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-900/30 dark:bg-green-900/10 dark:text-green-300' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/30 dark:bg-red-900/10 dark:text-red-300'"
                                >
                                    {{ item.is_correct ? 'Benar' : 'Salah' }}
                                </span>
                            </div>

                            <div class="prose prose-sm max-w-none dark:prose-invert" v-html="item.question"></div>

                            <div class="mt-3 grid gap-2 text-sm">
                                <div class="rounded-md border bg-background px-3 py-2">
                                    <p class="text-xs font-semibold text-muted-foreground">Jawaban kamu</p>
                                    <p class="mt-1">{{ userLabel(item) }}</p>
                                </div>
                                <div class="rounded-md border bg-background px-3 py-2">
                                    <p class="text-xs font-semibold text-muted-foreground">Jawaban benar</p>
                                    <p class="mt-1">{{ correctLabel(item) }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="!items.length" class="p-8 text-center">
                            <HelpCircle class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p class="text-muted-foreground">Tidak ada pertanyaan</p>
                        </div>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>

