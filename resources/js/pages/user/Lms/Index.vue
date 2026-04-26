<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, ChevronDown, ClipboardList, FileText, HelpCircle } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

interface CategoryRef {
    id: number;
    name: string;
    parent?: CategoryRef | null;
}

interface LmsMaterial {
    id: number;
    title: string;
    description: string | null;
    category: CategoryRef | null;
    file_path: string | null;
    thumbnail_path: string | null;
    created_at: string;
}

interface LmsQuiz {
    id: number;
    title: string;
    description: string | null;
    time_limit_minutes: number | null;
    passing_score: number;
    questions_count: number;
    category: CategoryRef | null;
    created_at: string;
}

interface LmsAssignment {
    id: number;
    title: string;
    description: string | null;
    instructions: string | null;
    due_at: string | null;
    max_score: number;
    category: CategoryRef | null;
    created_at: string;
}

interface Props {
    materials: {
        data: LmsMaterial[];
        links: any[];
        meta?: any;
    };
    quizzes: {
        data: LmsQuiz[];
        links: any[];
        meta?: any;
    };
    assignments: {
        data: LmsAssignment[];
        links: any[];
        meta?: any;
    };
    totals: {
        materials: number;
        quizzes: number;
        assignments: number;
    };
}

const { materials, quizzes, assignments, totals } = defineProps<Props>();

const openPanel = ref<'materi' | 'kuis' | 'tugas'>('materi');

const togglePanel = (panel: 'materi' | 'kuis' | 'tugas') => {
    openPanel.value = panel;
};

const openFromShortcut = (panel: 'materi' | 'kuis' | 'tugas') => {
    openPanel.value = panel;
    requestAnimationFrame(() => {
        document.getElementById('lms-accordion')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.has('quizzes_page')) openPanel.value = 'kuis';
    if (params.has('assignments_page')) openPanel.value = 'tugas';
    if (params.has('materials_page')) openPanel.value = 'materi';

    const hash = window.location.hash;
    if (hash === '#kuis') openPanel.value = 'kuis';
    if (hash === '#tugas') openPanel.value = 'tugas';
});

const categoryLabel = (item: { category: CategoryRef | null }) => {
    if (!item.category) return '-';
    if (item.category.parent?.name) return `${item.category.parent.name} / ${item.category.name}`;
    return item.category.name;
};

const stripHtml = (html: string) => {
    return html
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
};

const truncate = (text: string, maxLength = 140) => {
    if (text.length <= maxLength) return text;
    return `${text.slice(0, maxLength).trimEnd()}…`;
};

const thumbnailUrl = (material: LmsMaterial) => (material.thumbnail_path ? `/storage/${material.thumbnail_path}` : null);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const pageInfo = (paginator: any) => {
    const current = Number(paginator?.meta?.current_page ?? paginator?.current_page ?? 1);
    const last = Number(paginator?.meta?.last_page ?? paginator?.last_page ?? 1);
    return { current, last };
};

const prevUrl = (paginator: any): string | null => {
    const links = paginator?.links;
    if (Array.isArray(links)) return links[0]?.url ?? null;
    if (links && typeof links === 'object') return links.prev ?? null;
    return paginator?.prev_page_url ?? null;
};

const nextUrl = (paginator: any): string | null => {
    const links = paginator?.links;
    if (Array.isArray(links)) return links[links.length - 1]?.url ?? null;
    if (links && typeof links === 'object') return links.next ?? null;
    return paginator?.next_page_url ?? null;
};
</script>

<template>
    <Head title="LMS" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">LMS</h1>
                            <p class="text-sm text-muted-foreground">Materi pembelajaran</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="mb-6 grid grid-cols-3 gap-3">
                    <button
                        type="button"
                        @click="openFromShortcut('materi')"
                        class="rounded-lg border bg-card p-3 text-left transition-colors hover:bg-muted/50"
                    >
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-md bg-blue-100 dark:bg-blue-900/20">
                            <BookOpen class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p class="text-xs font-medium text-muted-foreground">Materi</p>
                        <p class="text-lg font-bold">{{ totals.materials }}</p>
                    </button>

                    <button
                        type="button"
                        @click="openFromShortcut('kuis')"
                        class="rounded-lg border bg-card p-3 text-left transition-colors hover:bg-muted/50"
                    >
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-md bg-purple-100 dark:bg-purple-900/20">
                            <HelpCircle class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <p class="text-xs font-medium text-muted-foreground">Kuis</p>
                        <p class="text-lg font-bold">{{ totals.quizzes }}</p>
                    </button>

                    <button
                        type="button"
                        @click="openFromShortcut('tugas')"
                        class="rounded-lg border bg-card p-3 text-left transition-colors hover:bg-muted/50"
                    >
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-md bg-amber-100 dark:bg-amber-900/20">
                            <ClipboardList class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <p class="text-xs font-medium text-muted-foreground">Tugas</p>
                        <p class="text-lg font-bold">{{ totals.assignments }}</p>
                    </button>
                </div>

                <div id="lms-accordion" class="rounded-lg border bg-card">
                    <div class="divide-y">
                        <div id="materi">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 p-4 text-left"
                                @click="togglePanel('materi')"
                            >
                                <div class="flex items-center gap-2">
                                    <BookOpen class="h-4 w-4" />
                                    <span class="text-lg font-semibold">Materi</span>
                                </div>
                                <ChevronDown class="h-5 w-5 transition-transform" :class="openPanel === 'materi' ? 'rotate-180' : ''" />
                            </button>

                            <div v-show="openPanel === 'materi'" class="border-t">
                                <div class="divide-y">
                                    <div v-for="material in materials.data" :key="material.id" class="p-4 transition-colors hover:bg-muted/50">
                                        <div class="flex gap-3">
                                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-md border bg-muted">
                                                <img
                                                    v-if="thumbnailUrl(material)"
                                                    :src="thumbnailUrl(material)!"
                                                    :alt="material.title"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div v-else class="flex h-full w-full items-center justify-center">
                                                    <FileText class="h-5 w-5 text-muted-foreground" />
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-foreground">{{ material.title }}</p>
                                                        <p class="mt-0.5 text-xs text-muted-foreground">
                                                            {{ categoryLabel(material) }} • {{ formatDate(material.created_at) }}
                                                        </p>
                                                    </div>
                                                    <Link
                                                        :href="`/lms/${material.id}`"
                                                        class="shrink-0 rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                                    >
                                                        Buka
                                                    </Link>
                                                </div>

                                                <p v-if="material.description" class="mt-2 text-sm text-muted-foreground">
                                                    {{ truncate(stripHtml(material.description)) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="!materials.data.length" class="p-8 text-center">
                                        <BookOpen class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                                        <p class="text-muted-foreground">Belum ada materi</p>
                                    </div>
                                </div>

                                <div v-if="pageInfo(materials).last > 1" class="flex items-center justify-between gap-3 border-t p-4">
                                    <Link
                                        v-if="prevUrl(materials)"
                                        :href="prevUrl(materials)!"
                                        class="rounded-md border bg-background px-3 py-2 text-xs font-semibold transition-colors hover:bg-muted/50"
                                    >
                                        Prev
                                    </Link>
                                    <span v-else class="rounded-md border bg-background px-3 py-2 text-xs font-semibold opacity-50">Prev</span>

                                    <p class="text-xs font-semibold text-muted-foreground">Halaman {{ pageInfo(materials).current }} / {{ pageInfo(materials).last }}</p>

                                    <Link
                                        v-if="nextUrl(materials)"
                                        :href="nextUrl(materials)!"
                                        class="rounded-md border bg-background px-3 py-2 text-xs font-semibold transition-colors hover:bg-muted/50"
                                    >
                                        Next
                                    </Link>
                                    <span v-else class="rounded-md border bg-background px-3 py-2 text-xs font-semibold opacity-50">Next</span>
                                </div>
                            </div>
                        </div>

                        <div id="kuis">
                            <button type="button" class="flex w-full items-center justify-between gap-3 p-4 text-left" @click="togglePanel('kuis')">
                                <div class="flex items-center gap-2">
                                    <HelpCircle class="h-4 w-4" />
                                    <span class="text-lg font-semibold">Kuis</span>
                                </div>
                                <ChevronDown class="h-5 w-5 transition-transform" :class="openPanel === 'kuis' ? 'rotate-180' : ''" />
                            </button>

                            <div v-show="openPanel === 'kuis'" class="border-t">
                                <div class="divide-y">
                                    <div v-for="quiz in quizzes.data" :key="quiz.id" class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-foreground">{{ quiz.title }}</p>
                                                <p class="mt-0.5 text-xs text-muted-foreground">
                                                    {{ categoryLabel(quiz) }} • {{ formatDate(quiz.created_at) }} • {{ quiz.questions_count }} soal
                                                </p>
                                            </div>
                                            <Link
                                                :href="`/lms/quizzes/${quiz.id}`"
                                                class="shrink-0 rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                            >
                                                Buka
                                            </Link>
                                        </div>
                                        <p v-if="quiz.description" class="mt-2 text-sm text-muted-foreground">
                                            {{ truncate(stripHtml(quiz.description)) }}
                                        </p>
                                    </div>

                                    <div v-if="!quizzes.data.length" class="p-8 text-center">
                                        <HelpCircle class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                                        <p class="text-muted-foreground">Belum ada kuis</p>
                                    </div>
                                </div>

                                <div v-if="pageInfo(quizzes).last > 1" class="flex items-center justify-between gap-3 border-t p-4">
                                    <Link
                                        v-if="prevUrl(quizzes)"
                                        :href="prevUrl(quizzes)!"
                                        class="rounded-md border bg-background px-3 py-2 text-xs font-semibold transition-colors hover:bg-muted/50"
                                    >
                                        Prev
                                    </Link>
                                    <span v-else class="rounded-md border bg-background px-3 py-2 text-xs font-semibold opacity-50">Prev</span>

                                    <p class="text-xs font-semibold text-muted-foreground">Halaman {{ pageInfo(quizzes).current }} / {{ pageInfo(quizzes).last }}</p>

                                    <Link
                                        v-if="nextUrl(quizzes)"
                                        :href="nextUrl(quizzes)!"
                                        class="rounded-md border bg-background px-3 py-2 text-xs font-semibold transition-colors hover:bg-muted/50"
                                    >
                                        Next
                                    </Link>
                                    <span v-else class="rounded-md border bg-background px-3 py-2 text-xs font-semibold opacity-50">Next</span>
                                </div>
                            </div>
                        </div>

                        <div id="tugas">
                            <button type="button" class="flex w-full items-center justify-between gap-3 p-4 text-left" @click="togglePanel('tugas')">
                                <div class="flex items-center gap-2">
                                    <ClipboardList class="h-4 w-4" />
                                    <span class="text-lg font-semibold">Tugas</span>
                                </div>
                                <ChevronDown class="h-5 w-5 transition-transform" :class="openPanel === 'tugas' ? 'rotate-180' : ''" />
                            </button>

                            <div v-show="openPanel === 'tugas'" class="border-t">
                                <div class="divide-y">
                                    <div v-for="assignment in assignments.data" :key="assignment.id" class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-foreground">{{ assignment.title }}</p>
                                                <p class="mt-0.5 text-xs text-muted-foreground">
                                                    {{ categoryLabel(assignment) }} • {{ formatDate(assignment.created_at) }}
                                                    <span v-if="assignment.due_at"> • Due {{ formatDateTime(assignment.due_at) }}</span>
                                                </p>
                                            </div>
                                            <Link
                                                :href="`/lms/assignments/${assignment.id}`"
                                                class="shrink-0 rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                            >
                                                Buka
                                            </Link>
                                        </div>
                                        <p v-if="assignment.description" class="mt-2 text-sm text-muted-foreground">
                                            {{ truncate(stripHtml(assignment.description)) }}
                                        </p>
                                    </div>

                                    <div v-if="!assignments.data.length" class="p-8 text-center">
                                        <ClipboardList class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                                        <p class="text-muted-foreground">Belum ada tugas</p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3 border-t p-4">
                                    <Link href="/lms/assignments" class="text-xs font-semibold text-primary hover:underline">Semua</Link>

                                    <div v-if="pageInfo(assignments).last > 1" class="flex flex-1 items-center justify-end gap-3">
                                        <Link
                                            v-if="prevUrl(assignments)"
                                            :href="prevUrl(assignments)!"
                                            class="rounded-md border bg-background px-3 py-2 text-xs font-semibold transition-colors hover:bg-muted/50"
                                        >
                                            Prev
                                        </Link>
                                        <span v-else class="rounded-md border bg-background px-3 py-2 text-xs font-semibold opacity-50">Prev</span>

                                        <p class="text-xs font-semibold text-muted-foreground">
                                            Halaman {{ pageInfo(assignments).current }} / {{ pageInfo(assignments).last }}
                                        </p>

                                        <Link
                                            v-if="nextUrl(assignments)"
                                            :href="nextUrl(assignments)!"
                                            class="rounded-md border bg-background px-3 py-2 text-xs font-semibold transition-colors hover:bg-muted/50"
                                        >
                                            Next
                                        </Link>
                                        <span v-else class="rounded-md border bg-background px-3 py-2 text-xs font-semibold opacity-50">Next</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>
