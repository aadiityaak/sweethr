<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { AlertTriangle, Award, Ban, BookOpen, CheckCircle2, ClipboardList, GraduationCap, Send, ShieldCheck, TrendingUp } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

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
    promotion_frozen?: boolean;
    blocked_by_freeze?: boolean;
}

interface Report {
    id: number;
    user_id: number;
    year: number;
    semester: string;
    kpi_score: number;
    lms_score: number;
    discipline_score: number;
    final_score: number;
    grade: string;
    total_violation_points: number;
    attendance_rate: number;
    status: string;
    published_at: string | null;
    recommendation: Recommendation | null;
    notes: string | null;
    user?: { id: number; name: string; position?: { title: string }; department?: { name: string } };
    generator?: { id: number; name: string } | null;
}

const props = defineProps<{ report: Report }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Raport Semester', href: '/admin/semester-reports' },
    { title: props.report.user?.name ?? 'Detail', href: '' },
];

const semesterLabel = computed(() =>
    props.report.semester === '1' ? 'Semester I (Jan–Jun)' : 'Semester II (Jul–Des)'
);

const periodLabel = computed(() => `${props.report.year} · ${semesterLabel.value}`);

// Bobot per komponen sesuai raport: KPI 50% / LMS 30% / Disiplin 20%
const components = computed(() => [
    {
        key: 'kpi', label: 'KPI / Performance Appraisal', weight: '50%', score: props.report.kpi_score,
        weighted: (props.report.kpi_score * 0.5).toFixed(2), color: 'bg-blue-500',
        icon: ClipboardList, desc: 'Rata-rata penilaian kinerja periodik (skala 0–10 × 10)',
    },
    {
        key: 'lms', label: 'LMS (Kuis & Tugas)', weight: '30%', score: props.report.lms_score,
        weighted: (props.report.lms_score * 0.3).toFixed(2), color: 'bg-purple-500',
        icon: BookOpen, desc: 'Kuis terbaik 70% + Tugas 30% pada periode',
    },
    {
        key: 'discipline', label: 'Disiplin (Kehadiran + Poin)', weight: '20%', score: props.report.discipline_score,
        weighted: (props.report.discipline_score * 0.2).toFixed(2), color: 'bg-amber-500',
        icon: ShieldCheck, desc: `Kehadiran 60% + Skor pelanggaran 40% (${props.report.total_violation_points} poin, kehadiran ${props.report.attendance_rate}%)`,
    },
]);

const gradeBadge = (g: string) => ({
    A: 'bg-green-100 text-green-800', B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800', D: 'bg-orange-100 text-orange-800',
    E: 'bg-red-100 text-red-800',
}[g] ?? 'bg-gray-100 text-gray-700');

const gradeLabel = (g: string) => ({
    A: 'Outstanding', B: 'Good / Exceeds', C: 'Meets Standard', D: 'Needs Improvement', E: 'Unsatisfactory',
}[g] ?? '');

const scoreColor = (score: number) =>
    score >= 80 ? 'text-green-600' : score >= 60 ? 'text-amber-600' : 'text-red-600';

const publish = () => {
    if (confirm('Terbitkan raport ini? Karyawan akan melihatnya di halaman My Raport.')) {
        router.patch(`/admin/semester-reports/${props.report.id}/publish`, {}, { preserveScroll: true });
    }
};

const formatDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';
</script>

<template>
    <Head :title="`Raport ${report.user?.name ?? ''}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-5xl space-y-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Raport Kinerja Semester</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ periodLabel }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="report.status === 'published'"
                        class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                        <CheckCircle2 class="h-3.5 w-3.5" /> Terbit
                    </span>
                    <span v-else class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                        Draft
                    </span>
                    <button v-if="report.status === 'draft'" @click="publish"
                        class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        <Send class="h-4 w-4" /> Terbitkan
                    </button>
                    <Link href="/admin/semester-reports"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Employee card + final score -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-white p-5 md:col-span-2">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Karyawan</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ report.user?.name }}</p>
                    <p class="text-sm text-gray-500">{{ report.user?.position?.title }} · {{ report.user?.department?.name }}</p>
                    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-gray-500">Poin pelanggaran (6 bln)</dt>
                            <dd class="font-semibold" :class="report.total_violation_points >= 35 ? 'text-red-600' : 'text-gray-900'">
                                {{ report.total_violation_points }}
                            </dd>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-gray-500">Tingkat kehadiran</dt>
                            <dd class="font-semibold text-gray-900">{{ report.attendance_rate }}%</dd>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-gray-500">Di-generate oleh</dt>
                            <dd class="font-semibold text-gray-900">{{ report.generator?.name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-gray-500">Diterbitkan</dt>
                            <dd class="font-semibold text-gray-900">{{ formatDate(report.published_at) }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="flex flex-col items-center justify-center rounded-lg border bg-white p-5 text-center">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Skor Akhir</p>
                    <p class="mt-1 text-5xl font-extrabold" :class="scoreColor(report.final_score)">
                        {{ report.final_score.toFixed(1) }}
                    </p>
                    <span :class="gradeBadge(report.grade)" class="mt-2 rounded-full px-4 py-1 text-lg font-bold">{{ report.grade }}</span>
                    <p class="mt-1 text-xs text-gray-500">{{ gradeLabel(report.grade) }}</p>
                </div>
            </div>

            <!-- Score breakdown -->
            <div class="rounded-lg border bg-white p-5">
                <h2 class="text-base font-semibold text-gray-900">Rincian Penilaian (KPI 50% · LMS 30% · Disiplin 20%)</h2>
                <div class="mt-4 space-y-4">
                    <div v-for="comp in components" :key="comp.key" class="rounded-md border p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component :is="comp.icon" class="h-4 w-4 text-gray-500" />
                                <p class="text-sm font-medium text-gray-900">{{ comp.label }}</p>
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">bobot {{ comp.weight }}</span>
                            </div>
                            <p class="text-sm font-bold" :class="scoreColor(comp.score)">{{ comp.score.toFixed(1) }}</p>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div :class="comp.color" class="h-full rounded-full" :style="{ width: Math.min(100, comp.score) + '%' }" />
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ comp.desc }}</p>
                        <p class="mt-1 text-xs font-medium text-gray-700">Skor terbobot: <span class="text-gray-900">{{ comp.weighted }}</span></p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-md bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-700">Total (Skor Akhir)</p>
                    <p class="text-lg font-extrabold" :class="scoreColor(report.final_score)">{{ report.final_score.toFixed(2) }}</p>
                </div>
            </div>

            <!-- Reward recommendation -->
            <div class="rounded-lg border bg-white p-5">
                <div class="flex items-center gap-2">
                    <Award class="h-5 w-5 text-amber-500" />
                    <h2 class="text-base font-semibold text-gray-900">Rekomendasi Reward &amp; Karir</h2>
                    <span v-if="report.recommendation?.tier" class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                        Tier {{ report.recommendation.tier }}
                    </span>
                </div>

                <template v-if="report.recommendation">
                    <p class="mt-2 text-sm text-gray-600">{{ report.recommendation.label }}</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="flex items-start gap-2 rounded-md border p-3">
                            <TrendingUp class="mt-0.5 h-4 w-4" :class="report.recommendation.salary_raise ? 'text-green-600' : 'text-gray-300'" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Kenaikan Gaji</p>
                                <p class="text-xs" :class="report.recommendation.salary_raise ? 'text-green-700' : 'text-gray-500'">
                                    {{ report.recommendation.salary_raise ? 'Direkomendasikan' : 'Tidak ada' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-md border p-3">
                            <Award class="mt-0.5 h-4 w-4" :class="report.recommendation.bonus ? 'text-amber-600' : 'text-gray-300'" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Bonus</p>
                                <p class="text-xs" :class="report.recommendation.bonus ? 'text-amber-700' : 'text-gray-500'">
                                    {{ report.recommendation.bonus ?? 'Tidak ada' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-md border p-3">
                            <GraduationCap class="mt-0.5 h-4 w-4" :class="report.recommendation.promotion_recommended ? 'text-blue-600' : 'text-gray-300'" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Rekomendasi Promosi</p>
                                <p class="text-xs" :class="report.recommendation.promotion_recommended ? 'text-blue-700' : 'text-gray-500'">
                                    {{ report.recommendation.promotion_recommended ? 'Ya' : 'Tidak' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-md border p-3">
                            <ShieldCheck class="mt-0.5 h-4 w-4" :class="report.recommendation.pkwtt_eligible ? 'text-purple-600' : 'text-gray-300'" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">Kelayakan PKWTT</p>
                                <p class="text-xs" :class="report.recommendation.pkwtt_eligible ? 'text-purple-700' : 'text-gray-500'">
                                    {{ report.recommendation.pkwtt_eligible ? 'Memenuhi syarat' : 'Belum' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-md bg-blue-50 p-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-blue-700">Prioritas Tindak Lanjut</p>
                        <p class="mt-1 text-sm text-blue-900">{{ report.recommendation.priority }}</p>
                    </div>

                    <div class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
                        <p class="text-gray-600">
                            Predikat semester sebelumnya:
                            <span class="font-semibold text-gray-900">{{ report.recommendation.previous_grade ?? '—' }}</span>
                            <span v-if="report.recommendation.consecutive_a" class="ml-1 rounded bg-green-100 px-1.5 py-0.5 font-medium text-green-700">A beruntun</span>
                        </p>
                        <p v-if="report.recommendation.pip_required" class="flex items-center gap-1 font-medium text-orange-700">
                            <AlertTriangle class="h-3.5 w-3.5" /> Wajib mengikuti PIP (Performance Improvement Plan)
                        </p>
                        <p v-if="report.recommendation.exit_review" class="flex items-center gap-1 font-medium text-red-700">
                            <AlertTriangle class="h-3.5 w-3.5" /> Perlu evaluasi kelanjutan kerja
                        </p>
                        <p v-if="report.recommendation.blocked_by_freeze" class="flex items-center gap-1 font-medium text-red-700 sm:col-span-2">
                            <Ban class="h-3.5 w-3.5" /> {{ report.recommendation.warnings[0] ?? 'Promosi dibekukan (SP aktif).' }}
                        </p>
                    </div>
                </template>
                <p v-else class="mt-3 text-sm text-gray-500">Rekomendasi belum tersedia — generate ulang raport untuk menghitung.</p>
            </div>

            <!-- Notes -->
            <div v-if="report.notes" class="rounded-lg border bg-white p-5">
                <h2 class="text-base font-semibold text-gray-900">Catatan HR</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ report.notes }}</p>
            </div>
        </div>
    </AppLayout>
</template>
