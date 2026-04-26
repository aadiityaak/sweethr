<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, HelpCircle } from 'lucide-vue-next';

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
    created_at: string;
}

interface Props {
    quizzes: {
        data: LmsQuiz[];
        links: any[];
        meta?: any;
    };
}

const { quizzes } = defineProps<Props>();

const categoryLabel = (quiz: LmsQuiz) => {
    if (!quiz.category) return '-';
    if (quiz.category.parent?.name) return `${quiz.category.parent.name} / ${quiz.category.name}`;
    return quiz.category.name;
};

const stripHtml = (html: string) => {
    return String(html ?? '')
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="LMS - Kuis" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/lms" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">Kuis</h1>
                            <p class="text-sm text-muted-foreground">Daftar kuis</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <HelpCircle class="h-4 w-4" />
                            Kuis
                        </h2>
                    </div>

                    <div class="divide-y">
                        <div v-for="quiz in quizzes.data" :key="quiz.id" class="p-4 transition-colors hover:bg-muted/50">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-foreground">{{ quiz.title }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">
                                        {{ categoryLabel(quiz) }} • {{ formatDate(quiz.created_at) }} • {{ quiz.questions_count }} soal
                                        <span v-if="quiz.time_limit_minutes"> • {{ quiz.time_limit_minutes }} menit</span>
                                    </p>
                                    <p v-if="quiz.description" class="mt-2 text-sm text-muted-foreground">
                                        {{ stripHtml(quiz.description) }}
                                    </p>
                                </div>
                                <Link
                                    :href="`/lms/quizzes/${quiz.id}`"
                                    class="shrink-0 rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    Buka
                                </Link>
                            </div>
                        </div>

                        <div v-if="!quizzes.data.length" class="p-8 text-center">
                            <HelpCircle class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p class="text-muted-foreground">Belum ada kuis</p>
                        </div>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>
