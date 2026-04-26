<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, HelpCircle, Play, RotateCcw, Trophy } from 'lucide-vue-next';
import { computed } from 'vue';

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
    is_active: boolean;
    category: CategoryRef | null;
    questions_count: number;
}

interface AttemptRef {
    id: number;
    started_at: string | null;
    submitted_at: string | null;
    score?: number | null;
    max_score?: number | null;
    is_passed?: boolean | null;
}

interface Props {
    quiz: LmsQuiz;
    inProgressAttempt: AttemptRef | null;
    attempts: AttemptRef[];
}

const { quiz, inProgressAttempt, attempts } = defineProps<Props>();

const categoryLabel = computed(() => {
    if (!quiz.category) return '-';
    if (quiz.category.parent?.name) return `${quiz.category.parent.name} / ${quiz.category.name}`;
    return quiz.category.name;
});

const startForm = useForm({});

const startOrResume = () => {
    startForm.post(`/lms/quizzes/${quiz.id}/start`, { preserveScroll: true });
};

const percentage = (attempt: AttemptRef) => {
    if (!attempt.max_score || !attempt.score) return 0;
    return Math.round((attempt.score / attempt.max_score) * 100);
};

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
</script>

<template>
    <Head :title="`LMS - Kuis: ${quiz.title}`" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/lms" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
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
                            Detail Kuis
                        </h2>
                        <div class="mt-2 text-sm text-muted-foreground">
                            <span>{{ quiz.questions_count }} soal</span>
                            <span class="mx-2">•</span>
                            <span>Lulus {{ quiz.passing_score }}%</span>
                            <span v-if="quiz.time_limit_minutes" class="mx-2">•</span>
                            <span v-if="quiz.time_limit_minutes">Durasi {{ quiz.time_limit_minutes }} menit</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div v-if="quiz.description" class="prose prose-sm max-w-none dark:prose-invert" v-html="quiz.description"></div>
                        <div v-else class="text-sm text-muted-foreground">Tidak ada deskripsi.</div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                @click="startOrResume"
                                :disabled="startForm.processing"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-60"
                            >
                                <component :is="inProgressAttempt ? RotateCcw : Play" class="h-4 w-4" />
                                {{ inProgressAttempt ? 'Lanjutkan' : 'Mulai' }}
                            </button>
                            <Link
                                href="/lms/quizzes"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border bg-background px-4 py-3 text-sm font-semibold transition-colors hover:bg-muted/50"
                            >
                                <HelpCircle class="h-4 w-4" />
                                Semua Kuis
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <Trophy class="h-4 w-4" />
                            Riwayat Attempt
                        </h2>
                    </div>

                    <div class="divide-y">
                        <div v-for="attempt in attempts" :key="attempt.id" class="p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-foreground">
                                        Skor: {{ attempt.score ?? 0 }}/{{ attempt.max_score ?? 0 }} ({{ percentage(attempt) }}%)
                                    </p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">Submit: {{ formatDateTime(attempt.submitted_at) }}</p>
                                </div>
                                <Link
                                    :href="`/lms/quizzes/${quiz.id}/attempts/${attempt.id}`"
                                    class="shrink-0 rounded-md bg-secondary px-3 py-2 text-xs font-semibold text-secondary-foreground transition-colors hover:bg-secondary/80"
                                >
                                    Lihat
                                </Link>
                            </div>
                        </div>

                        <div v-if="!attempts.length" class="p-8 text-center">
                            <Trophy class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p class="text-muted-foreground">Belum ada attempt</p>
                        </div>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>

