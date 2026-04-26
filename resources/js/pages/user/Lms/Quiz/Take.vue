<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Clock, Send } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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

interface LmsQuizAttempt {
    id: number;
    started_at: string | null;
    submitted_at: string | null;
    answers: Record<string, any> | null;
}

interface LmsQuizQuestion {
    id: number;
    type: QuestionType;
    question: string;
    options: string[] | null;
    points: number;
    order: number;
}

interface Props {
    quiz: LmsQuiz;
    attempt: LmsQuizAttempt;
    questions: LmsQuizQuestion[];
}

const { quiz, attempt, questions } = defineProps<Props>();
const { toast } = useToast();

const initialAnswers: Record<string, any> = { ...(attempt.answers ?? {}) };
const form = useForm<{ answers: Record<string, any> }>({
    answers: initialAnswers,
});

const categoryLabel = computed(() => {
    if (!quiz.category) return '-';
    if (quiz.category.parent?.name) return `${quiz.category.parent.name} / ${quiz.category.name}`;
    return quiz.category.name;
});

const startedAtMs = computed(() => (attempt.started_at ? new Date(attempt.started_at).getTime() : Date.now()));
const timeLimitSeconds = computed(() => (quiz.time_limit_minutes ? quiz.time_limit_minutes * 60 : null));
const nowMs = ref(Date.now());
let timer: number | null = null;

onMounted(() => {
    timer = window.setInterval(() => {
        nowMs.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (timer) window.clearInterval(timer);
});

const elapsedSeconds = computed(() => Math.max(0, Math.floor((nowMs.value - startedAtMs.value) / 1000)));
const remainingSeconds = computed(() => (timeLimitSeconds.value === null ? null : Math.max(0, timeLimitSeconds.value - elapsedSeconds.value)));
const isExpired = computed(() => remainingSeconds.value !== null && remainingSeconds.value <= 0);

const formatSeconds = (total: number) => {
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
};

const submit = () => {
    if (questions.length === 0) {
        toast({ title: 'Tidak ada pertanyaan', description: 'Kuis belum memiliki pertanyaan aktif.' });
        return;
    }

    form.post(`/lms/quizzes/${quiz.id}/attempts/${attempt.id}/submit`, {
        preserveScroll: true,
        onError: () => {
            toast({ title: 'Gagal submit', description: 'Coba lagi ya.' });
        },
    });
};
</script>

<template>
    <Head :title="`LMS - Kerjakan Kuis: ${quiz.title}`" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <Link
                                :href="`/lms/quizzes/${quiz.id}`"
                                class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80"
                            >
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                            <div class="min-w-0">
                                <h1 class="truncate text-lg font-semibold">{{ quiz.title }}</h1>
                                <p class="text-sm text-muted-foreground">{{ categoryLabel }}</p>
                            </div>
                        </div>

                        <div v-if="remainingSeconds !== null" class="flex items-center gap-2 rounded-md border bg-card px-3 py-2 text-xs font-semibold">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                            <span :class="isExpired ? 'text-destructive' : 'text-foreground'">
                                {{ formatSeconds(remainingSeconds) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div v-if="isExpired" class="mb-4 rounded-lg border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
                    Waktu habis. Kamu tetap bisa submit untuk menyimpan jawaban yang sudah terisi.
                </div>

                <div class="grid gap-4">
                    <div v-for="(q, idx) in questions" :key="q.id" class="rounded-lg border bg-card p-4">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-muted-foreground">Soal {{ idx + 1 }} • {{ q.points }} poin</p>
                                <div class="prose prose-sm mt-2 max-w-none dark:prose-invert" v-html="q.question"></div>
                            </div>
                        </div>

                        <div v-if="q.type === 'multiple_choice'" class="mt-3 grid gap-2">
                            <label
                                v-for="(opt, optIdx) in q.options ?? []"
                                :key="`${q.id}-${optIdx}`"
                                class="flex cursor-pointer items-start gap-3 rounded-md border bg-background px-3 py-2 text-sm transition-colors hover:bg-muted/50"
                            >
                                <input
                                    type="radio"
                                    class="mt-1"
                                    :name="`q-${q.id}`"
                                    :value="optIdx"
                                    v-model="form.answers[String(q.id)]"
                                />
                                <span class="min-w-0">{{ opt }}</span>
                            </label>
                        </div>

                        <div v-else-if="q.type === 'true_false'" class="mt-3 grid gap-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-md border bg-background px-3 py-2 text-sm transition-colors hover:bg-muted/50">
                                <input type="radio" class="mt-0.5" :name="`q-${q.id}`" :value="true" v-model="form.answers[String(q.id)]" />
                                <span>Benar</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-md border bg-background px-3 py-2 text-sm transition-colors hover:bg-muted/50">
                                <input type="radio" class="mt-0.5" :name="`q-${q.id}`" :value="false" v-model="form.answers[String(q.id)]" />
                                <span>Salah</span>
                            </label>
                        </div>

                        <div v-else class="mt-3">
                            <input
                                type="text"
                                class="w-full rounded-md border bg-background px-3 py-2 text-sm"
                                placeholder="Ketik jawaban..."
                                v-model="form.answers[String(q.id)]"
                            />
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-60"
                >
                    <Send class="h-4 w-4" />
                    Submit Kuis
                </button>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>

