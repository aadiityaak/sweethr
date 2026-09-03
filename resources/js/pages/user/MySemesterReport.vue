<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Award, BookOpen, CheckCircle2, ClipboardList, GraduationCap, ShieldCheck, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Recommendation {
    tier: string;
    label: string;
    salary_raise: boolean;
    bonus: string | null;
    promotion_recommended: boolean;
    pkwtt_eligible: boolean;
    priority: string;
    pip_required?: boolean;
    exit_review?: boolean;
    warnings: string[];
    previous_grade?: string | null;
    consecutive_a?: boolean;
    blocked_by_freeze?: boolean;
}

interface Report {
    id: number;
    year: number;
    semester: string;
    kpi_score: number;
    lms_score: number;
    discipline_score: number;
    final_score: number;
    grade: string;
    total_violation_points: number;
    attendance_rate: number;
    recommendation: Recommendation | null;
    published_at: string | null;
}

interface Props {
    reports: Report[];
}

const { reports } = defineProps<Props>();

const selectedId = ref(reports[0]?.id ?? 0);
const selected = computed(() => reports.find(r => r.id === selectedId.value) ?? reports[0] ?? null);

const gradeBadge = (g: string) => ({
    A: 'bg-green-50 text-green-700 border-green-200', B: 'bg-blue-50 text-blue-700 border-blue-200',
    C: 'bg-yellow-50 text-yellow-700 border-yellow-200', D: 'bg-orange-50 text-orange-700 border-orange-200',
    E: 'bg-red-50 text-red-700 border-red-200',
}[g] ?? 'bg-muted text-muted-foreground border-border');

const gradeLabel = (g: string) => ({
    A: 'Outstanding', B: 'Good / Exceeds', C: 'Meets Standard', D: 'Needs Improvement', E: 'Unsatisfactory',
}[g] ?? '');

const periodLabel = (r: Report) =>
    `${r.year} · Semester ${r.semester === '1' ? 'I (Jan–Jun)' : 'II (Jul–Des)'}`;

const scoreColor = (s: number) => (s >= 80 ? 'text-green-600' : s >= 60 ? 'text-amber-600' : 'text-red-600');

const components = computed(() => {
    if (!selected.value) return [];
    const r = selected.value;
    return [
        { label: 'KPI / Kinerja', weight: '50%', score: r.kpi_score, weighted: (r.kpi_score * 0.5).toFixed(2), icon: ClipboardList, color: 'bg-blue-500' },
        { label: 'LMS (Kuis & Tugas)', weight: '30%', score: r.lms_score, weighted: (r.lms_score * 0.3).toFixed(2), icon: BookOpen, color: 'bg-purple-500' },
        { label: 'Disiplin', weight: '20%', score: r.discipline_score, weighted: (r.discipline_score * 0.2).toFixed(2), icon: ShieldCheck, color: 'bg-amber-500' },
    ];
});

const formatDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
</script>

<template>
    <Head title="Raport Saya" />
    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <!-- Header -->
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <Award class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">Raport Kinerja Saya</h1>
                            <p class="text-sm text-muted-foreground">KPI 50% · LMS 30% · Disiplin 20%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div v-if="selected">
                    <!-- Period selector -->
                    <div v-if="reports.length > 1" class="mb-4 flex gap-2 overflow-x-auto">
                        <button v-for="report in reports" :key="report.id" @click="selectedId = report.id"
                            class="whitespace-nowrap rounded-full border px-3 py-1.5 text-xs font-medium"
                            :class="report.id === selected?.id ? 'border-primary bg-primary text-primary-foreground' : 'bg-card text-muted-foreground'">
                            {{ periodLabel(report) }}
                        </button>
                    </div>

                    <!-- Final score -->
                    <div class="rounded-lg border bg-card p-5 text-center">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">{{ periodLabel(selected) }}</p>
                        <p class="mt-1 text-5xl font-extrabold" :class="scoreColor(selected.final_score)">
                            {{ selected.final_score.toFixed(1) }}
                        </p>
                        <span :class="gradeBadge(selected.grade)" class="mt-2 inline-block rounded-full border px-4 py-1 text-base font-bold">
                            {{ selected.grade }} — {{ gradeLabel(selected.grade) }}
                        </span>
                        <p class="mt-2 text-xs text-muted-foreground">Diterbitkan {{ formatDate(selected.published_at) }}</p>
                    </div>

                    <!-- Breakdown -->
                    <div class="mt-4 space-y-3">
                        <div v-for="comp in components" :key="comp.label" class="rounded-lg border bg-card p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <component :is="comp.icon" class="h-4 w-4 text-muted-foreground" />
                                    <p class="text-sm font-medium">{{ comp.label }}</p>
                                    <span class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">{{ comp.weight }}</span>
                                </div>
                                <p class="text-sm font-bold" :class="scoreColor(comp.score)">{{ comp.score.toFixed(1) }}</p>
                            </div>
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                <div :class="comp.color" class="h-full rounded-full" :style="{ width: Math.min(100, comp.score) + '%' }" />
                            </div>
                            <p class="mt-1 text-right text-xs text-muted-foreground">terbobot: {{ comp.weighted }}</p>
                        </div>
                    </div>

                    <!-- Attendance + points -->
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg border bg-card p-4 text-center">
                            <ShieldCheck class="mx-auto h-5 w-5 text-green-600" />
                            <p class="mt-1 text-2xl font-bold">{{ selected.attendance_rate }}%</p>
                            <p class="text-xs text-muted-foreground">Kehadiran</p>
                        </div>
                        <div class="rounded-lg border bg-card p-4 text-center">
                            <ShieldCheck class="mx-auto h-5 w-5" :class="selected.total_violation_points >= 35 ? 'text-red-600' : 'text-muted-foreground'" />
                            <p class="mt-1 text-2xl font-bold">{{ selected.total_violation_points }}</p>
                            <p class="text-xs text-muted-foreground">Poin Pelanggaran</p>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div v-if="selected.recommendation" class="mt-4 rounded-lg border bg-card p-4">
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-4 w-4 text-amber-600" />
                            <h2 class="text-sm font-semibold">Rekomendasi Reward</h2>
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">Tier {{ selected.recommendation.tier }}</span>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">{{ selected.recommendation.label }}</p>

                        <div class="mt-3 space-y-2 text-xs">
                            <div class="flex items-center justify-between rounded-md bg-muted p-2.5">
                                <span class="flex items-center gap-1.5"><TrendingUp class="h-3.5 w-3.5" :class="selected.recommendation.salary_raise ? 'text-green-600' : 'text-muted-foreground'" /> Kenaikan gaji</span>
                                <span class="font-semibold" :class="selected.recommendation.salary_raise ? 'text-green-700' : 'text-muted-foreground'">
                                    {{ selected.recommendation.salary_raise ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between rounded-md bg-muted p-2.5">
                                <span class="flex items-center gap-1.5"><Award class="h-3.5 w-3.5" :class="selected.recommendation.bonus ? 'text-amber-600' : 'text-muted-foreground'" /> Bonus</span>
                                <span class="text-right font-semibold" :class="selected.recommendation.bonus ? 'text-amber-700' : 'text-muted-foreground'">
                                    {{ selected.recommendation.bonus ?? 'Tidak ada' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between rounded-md bg-muted p-2.5">
                                <span class="flex items-center gap-1.5"><GraduationCap class="h-3.5 w-3.5" :class="selected.recommendation.promotion_recommended ? 'text-blue-600' : 'text-muted-foreground'" /> Promosi</span>
                                <span class="font-semibold" :class="selected.recommendation.promotion_recommended ? 'text-blue-700' : 'text-muted-foreground'">
                                    {{ selected.recommendation.promotion_recommended ? 'Direkomendasikan' : 'Tidak' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between rounded-md bg-muted p-2.5">
                                <span class="flex items-center gap-1.5"><ShieldCheck class="h-3.5 w-3.5" :class="selected.recommendation.pkwtt_eligible ? 'text-purple-600' : 'text-muted-foreground'" /> Layak PKWTT</span>
                                <span class="font-semibold" :class="selected.recommendation.pkwtt_eligible ? 'text-purple-700' : 'text-muted-foreground'">
                                    {{ selected.recommendation.pkwtt_eligible ? 'Ya' : 'Belum' }}
                                </span>
                            </div>
                        </div>

                        <div v-if="selected.recommendation.priority" class="mt-3 rounded-md bg-blue-50 p-2.5 dark:bg-blue-900/20">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-400">Prioritas</p>
                            <p class="mt-0.5 text-xs text-blue-900 dark:text-blue-300">{{ selected.recommendation.priority }}</p>
                        </div>

                        <p v-if="selected.recommendation.blocked_by_freeze"
                            class="mt-2 rounded-md bg-red-50 p-2.5 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">
                            {{ selected.recommendation.warnings[0] ?? 'Promosi sedang dibekukan (SP aktif).' }}
                        </p>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="rounded-lg border bg-card p-8 text-center">
                    <CheckCircle2 class="mx-auto h-10 w-10 text-muted-foreground" />
                    <p class="mt-3 text-sm text-muted-foreground">
                        Belum ada raport yang diterbitkan. Raport akan muncul setelah HR menerbitkannya.
                    </p>
                </div>
            </div>

            <BottomNavigation current-route="/my/semester-report" />
        </div>
    </div>
</template>
