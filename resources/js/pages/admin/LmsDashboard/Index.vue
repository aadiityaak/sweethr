<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Chart from '@/components/ui/Chart.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    AlertTriangle, ArrowRight, Award, BookOpen, Building2, CalendarClock, CheckCircle2,
    FileSpreadsheet, GraduationCap, ShieldAlert, Users, XCircle,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface ContractAlert {
    id: number;
    user?: { name: string; position?: { title: string }; department?: { name: string } };
    end_date: string;
    days_remaining: number;
    alert_level: string;
}

interface OutletProgress {
    outlet: string;
    total_employees: number;
    passed_quizzes: number;
    material_reads: number;
    completion_pct: number;
}

interface RecentViolation {
    id: number;
    occurred_at: string;
    points: number;
    user?: { name: string };
    violation?: { name: string; category: string };
}

interface SpCandidate {
    id: number;
    name: string;
    total_points: number;
}

interface TopReport {
    id: number;
    final_score: number;
    grade: string;
    status: string;
    user?: { name: string; position?: { title: string } };
    recommendation?: { tier: string; label: string; promotion_recommended: boolean } | null;
}

interface Props {
    contractStats: {
        pkwt_active: number; pkwtt_active: number;
        expiring_60: number; expiring_30: number; expired: number;
    };
    contractAlerts: ContractAlert[];
    outletProgress: OutletProgress[];
    monthlyFeed: Record<string, Record<string, number>>;
    recentViolations: RecentViolation[];
    spCandidates: SpCandidate[];
    topReports: TopReport[];
    currentPeriod: { year: number; semester: number };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS Executive', href: '' },
];

// ---------- Widget 1: Personnel Status ----------
const statusCards = computed(() => [
    { label: 'PKWT Aktif', value: props.contractStats.pkwt_active, icon: Users, color: 'text-blue-600 bg-blue-50' },
    { label: 'PKWTT Aktif', value: props.contractStats.pkwtt_active, icon: Users, color: 'text-purple-600 bg-purple-50' },
    { label: 'H-60', value: props.contractStats.expiring_60, icon: CalendarClock, color: 'text-amber-600 bg-amber-50' },
    { label: 'H-30', value: props.contractStats.expiring_30, icon: CalendarClock, color: 'text-orange-600 bg-orange-50' },
    { label: 'Expired', value: props.contractStats.expired, icon: XCircle, color: 'text-red-600 bg-red-50' },
]);

// ---------- Widget 2: Outlet Training Progress ----------
const topOutlets = computed(() => props.outletProgress.slice(0, 8));

// ---------- Widget 3: Violation Feed ----------
const categoryColors: Record<string, string> = {
    ringan: 'bg-yellow-400', sedang: 'bg-orange-500', berat: 'bg-red-600',
};

const feedChart = computed(() => {
    const months = Object.keys(props.monthlyFeed).sort();
    return {
        labels: months.map(m => {
            const [y, mo] = m.split('-');
            return new Date(Number(y), Number(mo) - 1, 1).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
        }),
        datasets: [
            {
                label: 'Ringan', backgroundColor: '#facc15',
                data: months.map(m => props.monthlyFeed[m]?.ringan ?? 0),
            },
            {
                label: 'Sedang', backgroundColor: '#f97316',
                data: months.map(m => props.monthlyFeed[m]?.sedang ?? 0),
            },
            {
                label: 'Berat', backgroundColor: '#dc2626',
                data: months.map(m => props.monthlyFeed[m]?.berat ?? 0),
            },
        ],
    };
});

const feedOptions = {
    responsive: true, maintainAspectRatio: false,
    scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
};

const periodLabel = computed(() =>
    `${props.currentPeriod.year} · Semester ${props.currentPeriod.semester === 1 ? 'I' : 'II'}`
);

// ---------- Widget 4: SP candidates ----------
const issueSp = (candidate: SpCandidate) => {
    if (confirm(`Terbitkan SP 1 untuk ${candidate.name} (${candidate.total_points} poin)?`)) {
        router.post('/admin/disciplinary-actions', { user_id: candidate.id, action_type: 'sp1' }, { preserveScroll: true });
    }
};

// ---------- Widget 5: Reports ----------
const gradeBadge = (g: string) => ({
    A: 'bg-green-100 text-green-800', B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800', D: 'bg-orange-100 text-orange-800',
    E: 'bg-red-100 text-red-800',
}[g] ?? 'bg-gray-100 text-gray-700');

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
</script>

<template>
    <Head title="LMS Executive Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Executive Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        SDM &amp; Training · PT Warung Mas Mbull · Periode aktif: {{ periodLabel }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link href="/admin/contracts"
                        class="inline-flex items-center gap-2 rounded-md border bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <Users class="h-4 w-4" /> Kontrak
                    </Link>
                    <Link href="/admin/employee-violations"
                        class="inline-flex items-center gap-2 rounded-md border bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <ShieldAlert class="h-4 w-4" /> Disiplin
                    </Link>
                    <Link href="/admin/semester-reports"
                        class="inline-flex items-center gap-2 rounded-md border bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <FileSpreadsheet class="h-4 w-4" /> Raport
                    </Link>
                </div>
            </div>

            <!-- WIDGET 1: Personnel Status -->
            <section class="rounded-lg border bg-white p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Building2 class="h-5 w-5 text-blue-600" />
                        <h2 class="text-base font-semibold text-gray-900">Status Personel &amp; Kontrak</h2>
                    </div>
                    <Link href="/admin/contracts/alerts" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline">
                        Kelola Alert <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div v-for="card in statusCards" :key="card.label"
                        class="rounded-lg border p-3 text-center">
                        <div :class="card.color" class="mx-auto flex h-9 w-9 items-center justify-center rounded-full">
                            <component :is="card.icon" class="h-4.5 w-4.5" />
                        </div>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ card.value }}</p>
                        <p class="text-xs text-gray-500">{{ card.label }}</p>
                    </div>
                </div>

                <!-- Contract alerts mini list -->
                <div v-if="contractAlerts.length" class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Kontrak Mendekati Berakhir</p>
                    <div class="mt-2 divide-y divide-gray-100 rounded-md border">
                        <div v-for="alert in contractAlerts" :key="alert.id"
                            class="flex items-center justify-between px-3 py-2 text-sm">
                            <div>
                                <p class="font-medium text-gray-900">{{ alert.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ alert.user?.position?.title }} · {{ alert.user?.department?.name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold"
                                    :class="alert.days_remaining <= 0 ? 'bg-red-100 text-red-700' : alert.days_remaining <= 30 ? 'bg-orange-100 text-orange-700' : 'bg-amber-100 text-amber-700'">
                                    {{ alert.days_remaining <= 0 ? 'Expired' : `H-${alert.days_remaining}` }}
                                </span>
                                <p class="mt-0.5 text-xs text-gray-500">s/d {{ formatDate(alert.end_date) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- WIDGET 2: Outlet Training Progress -->
                <section class="rounded-lg border bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5 text-purple-600" />
                            <h2 class="text-base font-semibold text-gray-900">Progres Training per Outlet</h2>
                        </div>
                        <span class="text-xs text-gray-400">kuis lulus ×2 + baca materi, target 10/orang</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div v-for="outlet in topOutlets" :key="outlet.outlet">
                            <div class="flex items-center justify-between text-sm">
                                <p class="font-medium text-gray-900">
                                    {{ outlet.outlet }}
                                    <span class="ml-1 text-xs font-normal text-gray-400">({{ outlet.total_employees }} org)</span>
                                </p>
                                <p class="font-bold" :class="outlet.completion_pct >= 70 ? 'text-green-600' : outlet.completion_pct >= 40 ? 'text-amber-600' : 'text-red-600'">
                                    {{ outlet.completion_pct }}%
                                </p>
                            </div>
                            <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full"
                                    :class="outlet.completion_pct >= 70 ? 'bg-green-500' : outlet.completion_pct >= 40 ? 'bg-amber-500' : 'bg-red-500'"
                                    :style="{ width: outlet.completion_pct + '%' }" />
                            </div>
                        </div>
                        <p v-if="!topOutlets.length" class="py-6 text-center text-sm text-gray-500">Belum ada data departemen.</p>
                    </div>
                </section>

                <!-- WIDGET 3: Violation Feed -->
                <section class="rounded-lg border bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <ShieldAlert class="h-5 w-5 text-red-600" />
                            <h2 class="text-base font-semibold text-gray-900">Feed Pelanggaran 6 Bulan</h2>
                        </div>
                        <Link href="/admin/employee-violations" class="text-sm font-medium text-blue-600 hover:underline">Detail</Link>
                    </div>
                    <div class="mt-4 h-52">
                        <Chart type="bar" :data="feedChart" :options="feedOptions" />
                    </div>
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Pelanggaran Terbaru</p>
                        <ul class="mt-2 divide-y divide-gray-100">
                            <li v-for="v in recentViolations" :key="v.id" class="flex items-center justify-between py-2 text-sm">
                                <div>
                                    <p class="font-medium text-gray-900">{{ v.user?.name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ v.violation?.name }}
                                        <span :class="categoryColors[v.violation?.category ?? '']"
                                            class="ml-1 inline-block h-2 w-2 rounded-full align-middle" />
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold" :class="v.points >= 35 ? 'text-red-600' : 'text-gray-700'">+{{ v.points }}</span>
                                    <p class="text-xs text-gray-400">{{ formatDate(v.occurred_at) }}</p>
                                </div>
                            </li>
                            <li v-if="!recentViolations.length" class="py-6 text-center text-sm text-gray-500">
                                Tidak ada pelanggaran tercatat.
                            </li>
                        </ul>
                    </div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- WIDGET 4: SP Generator -->
                <section class="rounded-lg border bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5 text-orange-600" />
                            <h2 class="text-base font-semibold text-gray-900">Kandidat Surat Peringatan (Otomatis)</h2>
                        </div>
                        <span class="rounded bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700">≥ 35 poin / 6 bln</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Karyawan melewati ambang SP1 yang belum memiliki SP aktif. Tier: Teguran 15 · SP1 35 · SP2 50 · SP3 70 · Evaluasi PHK 85.
                    </p>
                    <div class="mt-3 divide-y divide-gray-100 rounded-md border">
                        <div v-for="c in spCandidates" :key="c.id" class="flex items-center justify-between px-3 py-2.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ c.name }}</p>
                                <p class="text-xs font-bold" :class="c.total_points >= 85 ? 'text-red-600' : c.total_points >= 50 ? 'text-orange-600' : 'text-amber-600'">
                                    {{ c.total_points }} poin
                                </p>
                            </div>
                            <button @click="issueSp(c)"
                                class="inline-flex items-center gap-1 rounded-md bg-orange-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-orange-700">
                                <AlertTriangle class="h-3.5 w-3.5" /> Terbitkan SP
                            </button>
                        </div>
                        <div v-if="!spCandidates.length" class="flex items-center justify-center gap-2 py-8 text-sm text-gray-500">
                            <CheckCircle2 class="h-4 w-4 text-green-500" /> Semua karyawan di bawah ambang SP.
                        </div>
                    </div>
                    <Link href="/admin/disciplinary-actions" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline">
                        Kelola semua SP <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </section>

                <!-- WIDGET 5: Semester Report Card & Reward Engine -->
                <section class="rounded-lg border bg-white p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Award class="h-5 w-5 text-amber-500" />
                            <h2 class="text-base font-semibold text-gray-900">Raport Semester &amp; Reward Engine</h2>
                        </div>
                        <span class="text-xs text-gray-400">{{ periodLabel }}</span>
                    </div>
                    <div class="mt-3 divide-y divide-gray-100 rounded-md border">
                        <div v-for="report in topReports" :key="report.id" class="flex items-center justify-between px-3 py-2.5">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ report.user?.name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ report.user?.position?.title }}
                                    <span v-if="report.recommendation?.promotion_recommended"
                                        class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700">
                                        Promosi
                                    </span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-900">{{ report.final_score.toFixed(1) }}</span>
                                <span :class="gradeBadge(report.grade)" class="rounded-full px-2 py-0.5 text-xs font-bold">{{ report.grade }}</span>
                            </div>
                        </div>
                        <div v-if="!topReports.length" class="py-8 text-center text-sm text-gray-500">
                            Belum ada raport terbit pada periode ini.
                        </div>
                    </div>
                    <Link href="/admin/semester-reports" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline">
                        Kelola raport &amp; generate <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
