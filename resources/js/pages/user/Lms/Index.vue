<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BarChart3, BookOpen, ChevronDown, ClipboardList, FileText, HelpCircle } from 'lucide-vue-next';
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
    is_read?: boolean;
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
    is_done?: boolean;
    submitted_attempts?: number;
    best_is_passed?: boolean;
    best_percent?: number | null;
    last_submitted_at?: string | null;
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
    is_done?: boolean;
    submitted_at?: string | null;
    graded_at?: string | null;
    score?: number | null;
    score_percent?: number | null;
}

interface PerformanceAppraisal {
    id: number;
    evaluated_at: string;
    evaluator?: { id: number; name: string } | null;
    feedback?: string | null;
    score_avg?: number | null;
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
    progress: {
        quizzes_taken: number;
        quiz_submitted_attempts: number;
        quiz_passed_attempts: number;
        quiz_avg_percent: number | null;
    };
    performanceAppraisals: PerformanceAppraisal[];
}

const { materials, quizzes, assignments, totals, progress, performanceAppraisals } = defineProps<Props>();

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

const formatDateLongLower = (isoDate: string) => {
    const parts = String(isoDate ?? '').split('T')[0].split('-');
    if (parts.length !== 3) return isoDate;
    const [y, m, d] = parts;
    const month = (() => {
        if (m === '01') return 'januari';
        if (m === '02') return 'februari';
        if (m === '03') return 'maret';
        if (m === '04') return 'april';
        if (m === '05') return 'mei';
        if (m === '06') return 'juni';
        if (m === '07') return 'juli';
        if (m === '08') return 'agustus';
        if (m === '09') return 'september';
        if (m === '10') return 'oktober';
        if (m === '11') return 'november';
        if (m === '12') return 'desember';
        return m;
    })();
    return `${d} ${month} ${y}`;
};

const scoreBadgeClass = (avg: number | null | undefined) => {
    if (avg === null || avg === undefined) return 'border-black/10 bg-white text-[#7e7e7e]';
    if (avg >= 4.25) return 'border-green-600/30 bg-green-50 text-green-700';
    if (avg >= 3.5) return 'border-lime-600/30 bg-lime-50 text-lime-700';
    if (avg >= 2.75) return 'border-amber-600/30 bg-amber-50 text-amber-700';
    return 'border-red-600/30 bg-red-50 text-red-700';
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

    <div class="min-h-screen bg-white text-[#25282b]">
        <div class="mx-auto min-h-screen max-w-[480px] bg-white">
            <div class="sticky top-0 z-40 border-b border-black/10 bg-white">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-[2px] border border-[#333333] bg-white p-2 text-[#25282b] transition-opacity hover:opacity-90">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-sm font-extrabold tracking-[0.2em] text-[#25282b] uppercase">LMS</h1>
                            <p class="text-xs text-[#7e7e7e]">Materi pembelajaran</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="mb-6 grid grid-cols-3 gap-3">
                    <button
                        type="button"
                        @click="openFromShortcut('materi')"
                        class="rounded-[6px] border border-black/10 bg-white p-3 text-left transition-colors hover:bg-black/5"
                    >
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white">
                            <BookOpen class="h-5 w-5 text-[#25282b]" />
                        </div>
                        <p class="text-[11px] font-semibold tracking-wide text-[#7e7e7e] uppercase">Materi</p>
                        <p class="text-lg font-extrabold tracking-[-0.03em] text-[#25282b]">{{ totals.materials }}</p>
                    </button>

                    <button
                        type="button"
                        @click="openFromShortcut('kuis')"
                        class="rounded-[6px] border border-black/10 bg-white p-3 text-left transition-colors hover:bg-black/5"
                    >
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white">
                            <HelpCircle class="h-5 w-5 text-[#25282b]" />
                        </div>
                        <p class="text-[11px] font-semibold tracking-wide text-[#7e7e7e] uppercase">Kuis</p>
                        <p class="text-lg font-extrabold tracking-[-0.03em] text-[#25282b]">{{ totals.quizzes }}</p>
                        <p class="mt-0.5 text-[10px] font-semibold text-[#7e7e7e]">
                            Ikut {{ progress.quizzes_taken }} • Rata2 {{ progress.quiz_avg_percent ?? '-' }}<span v-if="progress.quiz_avg_percent !== null">%</span>
                        </p>
                    </button>

                    <button
                        type="button"
                        @click="openFromShortcut('tugas')"
                        class="rounded-[6px] border border-black/10 bg-white p-3 text-left transition-colors hover:bg-black/5"
                    >
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white">
                            <ClipboardList class="h-5 w-5 text-[#25282b]" />
                        </div>
                        <p class="text-[11px] font-semibold tracking-wide text-[#7e7e7e] uppercase">Tugas</p>
                        <p class="text-lg font-extrabold tracking-[-0.03em] text-[#25282b]">{{ totals.assignments }}</p>
                    </button>
                </div>

                <div class="mb-6 overflow-hidden rounded-[6px] border border-black/10 bg-white">
                    <div class="flex items-center justify-between gap-3 border-b border-black/10 p-4">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-4 w-4 text-[#25282b]" />
                            <span class="text-base font-bold text-[#25282b]">Performance</span>
                        </div>
                        <span class="text-xs font-semibold text-[#7e7e7e]">{{ performanceAppraisals.length }} item</span>
                    </div>

                    <div v-if="performanceAppraisals.length === 0" class="p-4 text-xs text-[#7e7e7e]">Belum ada performance appraisal.</div>

                    <div v-else class="divide-y">
                        <div v-for="p in performanceAppraisals" :key="p.id" class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-extrabold tracking-[-0.02em] text-[#25282b]">{{ formatDateLongLower(p.evaluated_at) }}</p>
                                    <p class="mt-0.5 text-[11px] font-semibold text-[#7e7e7e]">
                                        Evaluator: {{ p.evaluator?.name ?? '-' }}
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-full border px-2.5 py-1 text-[11px] font-extrabold" :class="scoreBadgeClass(p.score_avg)">
                                    Avg {{ p.score_avg ?? '-' }}
                                </span>
                            </div>
                            <p v-if="stripHtml(p.feedback ?? '')" class="mt-2 text-xs leading-relaxed text-[#25282b]">
                                {{ truncate(stripHtml(p.feedback ?? ''), 140) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div id="lms-accordion" class="overflow-hidden rounded-[6px] border border-black/10 bg-white">
                    <div class="divide-y">
                        <div id="materi">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 p-4 text-left"
                                @click="togglePanel('materi')"
                            >
                                <div class="flex items-center gap-2">
                                    <BookOpen class="h-4 w-4 text-[#25282b]" />
                                    <span class="text-base font-bold text-[#25282b]">Materi</span>
                                </div>
                                <ChevronDown class="h-5 w-5 text-[#25282b] transition-transform" :class="openPanel === 'materi' ? 'rotate-180' : ''" />
                            </button>

                            <div v-show="openPanel === 'materi'" class="border-t border-black/10">
                                <div class="divide-y">
                                    <div v-for="material in materials.data" :key="material.id" class="p-4 transition-colors hover:bg-black/5">
                                        <div class="flex gap-3">
                                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-[6px] border border-black/10 bg-[#f2f2f2]">
                                                <img
                                                    v-if="thumbnailUrl(material)"
                                                    :src="thumbnailUrl(material)!"
                                                    :alt="material.title"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div v-else class="flex h-full w-full items-center justify-center">
                                                    <FileText class="h-5 w-5 text-[#7e7e7e]" />
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-[#25282b]">{{ material.title }}</p>
                                                        <p class="mt-0.5 text-xs text-[#7e7e7e]">
                                                            {{ categoryLabel(material) }} • {{ formatDate(material.created_at) }}
                                                        </p>
                                                    </div>
                                                    <div class="shrink-0 flex items-center gap-2">
                                                        <span
                                                            class="px-2 py-1 text-[10px] font-semibold"
                                                            :class="
                                                                material.is_read
                                                                    ? 'rounded-[2px] border border-[#e60000] bg-white/80 text-black/80 uppercase tracking-wide'
                                                                    : 'rounded-[32px] bg-[#f2f2f2] text-[#25282b]'
                                                            "
                                                        >
                                                            {{ material.is_read ? 'Sudah dibaca' : 'Belum dibaca' }}
                                                        </span>
                                                        <Link
                                                            :href="`/lms/${material.id}`"
                                                            class="rounded-[2px] bg-[#e60000] px-3 py-2 text-xs font-bold tracking-[0.12px] text-white transition-opacity hover:opacity-90"
                                                        >
                                                            Buka
                                                        </Link>
                                                    </div>
                                                </div>

                                                <p v-if="material.description" class="mt-2 text-sm text-[#7e7e7e]">
                                                    {{ truncate(stripHtml(material.description)) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="!materials.data.length" class="p-8 text-center">
                                        <BookOpen class="mx-auto mb-4 h-12 w-12 text-[#7e7e7e]" />
                                        <p class="text-sm text-[#7e7e7e]">Belum ada materi</p>
                                    </div>
                                </div>

                                <div v-if="pageInfo(materials).last > 1" class="flex items-center justify-between gap-3 border-t border-black/10 p-4">
                                    <Link
                                        v-if="prevUrl(materials)"
                                        :href="prevUrl(materials)!"
                                        class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] transition-colors hover:bg-[#f2f2f2]"
                                    >
                                        Prev
                                    </Link>
                                    <span v-else class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] opacity-50">Prev</span>

                                    <p class="text-xs font-semibold text-[#7e7e7e]">Halaman {{ pageInfo(materials).current }} / {{ pageInfo(materials).last }}</p>

                                    <Link
                                        v-if="nextUrl(materials)"
                                        :href="nextUrl(materials)!"
                                        class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] transition-colors hover:bg-[#f2f2f2]"
                                    >
                                        Next
                                    </Link>
                                    <span v-else class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] opacity-50">Next</span>
                                </div>
                            </div>
                        </div>

                        <div id="kuis">
                            <button type="button" class="flex w-full items-center justify-between gap-3 p-4 text-left" @click="togglePanel('kuis')">
                                <div class="flex items-center gap-2">
                                    <HelpCircle class="h-4 w-4 text-[#25282b]" />
                                    <span class="text-base font-bold text-[#25282b]">Kuis</span>
                                </div>
                                <ChevronDown class="h-5 w-5 text-[#25282b] transition-transform" :class="openPanel === 'kuis' ? 'rotate-180' : ''" />
                            </button>

                            <div v-show="openPanel === 'kuis'" class="border-t border-black/10">
                                <div class="divide-y">
                                    <div v-for="quiz in quizzes.data" :key="quiz.id" class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-[#25282b]">{{ quiz.title }}</p>
                                                <p class="mt-0.5 text-xs text-[#7e7e7e]">
                                                    {{ categoryLabel(quiz) }} • {{ formatDate(quiz.created_at) }} • {{ quiz.questions_count }} soal
                                                </p>
                                                <p v-if="quiz.is_done" class="mt-1 text-xs text-[#7e7e7e]">
                                                    Skor terbaik {{ quiz.best_percent ?? '-' }}<span v-if="quiz.best_percent !== null">%</span>
                                                    <span v-if="quiz.best_is_passed"> • Lulus</span>
                                                    <span v-else-if="quiz.best_percent !== null"> • Belum lulus</span>
                                                </p>
                                            </div>
                                            <div class="shrink-0 flex items-center gap-2">
                                                <span
                                                    class="px-2 py-1 text-[10px] font-semibold"
                                                    :class="
                                                        quiz.is_done
                                                            ? 'rounded-[2px] border border-[#e60000] bg-white/80 text-black/80 uppercase tracking-wide'
                                                            : 'rounded-[32px] bg-[#f2f2f2] text-[#25282b]'
                                                    "
                                                >
                                                    {{ quiz.is_done ? 'Sudah dikerjakan' : 'Belum dikerjakan' }}
                                                </span>
                                                <Link
                                                    :href="`/lms/quizzes/${quiz.id}`"
                                                    class="rounded-[2px] bg-[#e60000] px-3 py-2 text-xs font-bold tracking-[0.12px] text-white transition-opacity hover:opacity-90"
                                                >
                                                    Buka
                                                </Link>
                                            </div>
                                        </div>
                                        <p v-if="quiz.description" class="mt-2 text-sm text-[#7e7e7e]">
                                            {{ truncate(stripHtml(quiz.description)) }}
                                        </p>
                                    </div>

                                    <div v-if="!quizzes.data.length" class="p-8 text-center">
                                        <HelpCircle class="mx-auto mb-4 h-12 w-12 text-[#7e7e7e]" />
                                        <p class="text-sm text-[#7e7e7e]">Belum ada kuis</p>
                                    </div>
                                </div>

                                <div v-if="pageInfo(quizzes).last > 1" class="flex items-center justify-between gap-3 border-t border-black/10 p-4">
                                    <Link
                                        v-if="prevUrl(quizzes)"
                                        :href="prevUrl(quizzes)!"
                                        class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] transition-colors hover:bg-[#f2f2f2]"
                                    >
                                        Prev
                                    </Link>
                                    <span v-else class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] opacity-50">Prev</span>

                                    <p class="text-xs font-semibold text-[#7e7e7e]">Halaman {{ pageInfo(quizzes).current }} / {{ pageInfo(quizzes).last }}</p>

                                    <Link
                                        v-if="nextUrl(quizzes)"
                                        :href="nextUrl(quizzes)!"
                                        class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] transition-colors hover:bg-[#f2f2f2]"
                                    >
                                        Next
                                    </Link>
                                    <span v-else class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] opacity-50">Next</span>
                                </div>
                            </div>
                        </div>

                        <div id="tugas">
                            <button type="button" class="flex w-full items-center justify-between gap-3 p-4 text-left" @click="togglePanel('tugas')">
                                <div class="flex items-center gap-2">
                                    <ClipboardList class="h-4 w-4 text-[#25282b]" />
                                    <span class="text-base font-bold text-[#25282b]">Tugas</span>
                                </div>
                                <ChevronDown class="h-5 w-5 text-[#25282b] transition-transform" :class="openPanel === 'tugas' ? 'rotate-180' : ''" />
                            </button>

                            <div v-show="openPanel === 'tugas'" class="border-t border-black/10">
                                <div class="divide-y">
                                    <div v-for="assignment in assignments.data" :key="assignment.id" class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-[#25282b]">{{ assignment.title }}</p>
                                                <p class="mt-0.5 text-xs text-[#7e7e7e]">
                                                    {{ categoryLabel(assignment) }} • {{ formatDate(assignment.created_at) }}
                                                    <span v-if="assignment.due_at"> • Due {{ formatDateTime(assignment.due_at) }}</span>
                                                </p>
                                                <p v-if="assignment.is_done" class="mt-1 text-xs text-[#7e7e7e]">
                                                    <span v-if="assignment.graded_at && assignment.score_percent !== null">
                                                        Nilai {{ assignment.score_percent }}%
                                                    </span>
                                                    <span v-else>Sudah dikumpulkan</span>
                                                </p>
                                            </div>
                                            <div class="shrink-0 flex items-center gap-2">
                                                <span
                                                    class="px-2 py-1 text-[10px] font-semibold"
                                                    :class="
                                                        assignment.is_done
                                                            ? 'rounded-[2px] border border-[#e60000] bg-white/80 text-black/80 uppercase tracking-wide'
                                                            : 'rounded-[32px] bg-[#f2f2f2] text-[#25282b]'
                                                    "
                                                >
                                                    {{ assignment.is_done ? 'Sudah dikerjakan' : 'Belum dikerjakan' }}
                                                </span>
                                                <Link
                                                    :href="`/lms/assignments/${assignment.id}`"
                                                    class="rounded-[2px] bg-[#e60000] px-3 py-2 text-xs font-bold tracking-[0.12px] text-white transition-opacity hover:opacity-90"
                                                >
                                                    Buka
                                                </Link>
                                            </div>
                                        </div>
                                        <p v-if="assignment.description" class="mt-2 text-sm text-[#7e7e7e]">
                                            {{ truncate(stripHtml(assignment.description)) }}
                                        </p>
                                    </div>

                                    <div v-if="!assignments.data.length" class="p-8 text-center">
                                        <ClipboardList class="mx-auto mb-4 h-12 w-12 text-[#7e7e7e]" />
                                        <p class="text-sm text-[#7e7e7e]">Belum ada tugas</p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3 border-t border-black/10 p-4">
                                    <Link
                                        href="/lms/assignments"
                                        class="text-xs font-semibold text-[#3860be] underline decoration-[#3860be] underline-offset-4"
                                    >
                                        Semua
                                    </Link>

                                    <div v-if="pageInfo(assignments).last > 1" class="flex flex-1 items-center justify-end gap-3">
                                        <Link
                                            v-if="prevUrl(assignments)"
                                            :href="prevUrl(assignments)!"
                                            class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] transition-colors hover:bg-[#f2f2f2]"
                                        >
                                            Prev
                                        </Link>
                                        <span v-else class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] opacity-50">Prev</span>

                                        <p class="text-xs font-semibold text-[#7e7e7e]">
                                            Halaman {{ pageInfo(assignments).current }} / {{ pageInfo(assignments).last }}
                                        </p>

                                        <Link
                                            v-if="nextUrl(assignments)"
                                            :href="nextUrl(assignments)!"
                                            class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] transition-colors hover:bg-[#f2f2f2]"
                                        >
                                            Next
                                        </Link>
                                        <span v-else class="rounded-[2px] border border-[#333333] bg-white px-3 py-2 text-xs font-semibold text-[#25282b] opacity-50">Next</span>
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
