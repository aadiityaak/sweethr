<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArcElement, Chart as ChartJS, Legend, Tooltip } from 'chart.js';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { Doughnut } from 'vue-chartjs';
import { computed, ref, watch } from 'vue';

ChartJS.register(ArcElement, Tooltip, Legend);

interface UserRef {
    id: number;
    name: string;
    employee_id?: string | null;
}

interface Appraisal {
    id: number;
    evaluated_at: string;
    user: UserRef;
    evaluator?: UserRef | null;
    feedback?: string | null;
    score_avg?: number | null;
    score_total?: number;
    score_count?: number;
    quality_work: number;
    quantity_work: number;
    task_knowledge: number;
    discipline: number;
    teamwork: number;
    communication: number;
    initiative: number;
    target_realization: number;
    time_management: number;
    attitude: number;
    adaptability: number;
    leadership_delegation?: number | null;
    leadership_development?: number | null;
}

interface Props {
    appraisals: {
        data: Appraisal[];
        links: any[];
        meta?: any;
    };
}

const { appraisals } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Performance Appraisal', href: '/admin/lms-performance-appraisals' },
];

const employeeLabel = (u: UserRef) => {
    const empId = u.employee_id ? ` (${u.employee_id})` : '';
    return `${u.name}${empId}`;
};

const stripHtml = (html: string | null | undefined) => {
    const raw = String(html ?? '');
    if (!raw) return '';

    try {
        if (typeof window !== 'undefined' && 'DOMParser' in window) {
            const doc = new DOMParser().parseFromString(raw, 'text/html');
            return String(doc.body.textContent ?? '').trim();
        }
    } catch {
        // ignore
    }

    return raw
        .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
        .replace(/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi, '')
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
};

const feedbackPreview = (text: string | null | undefined) => {
    const raw = stripHtml(text);
    if (!raw) return '-';
    if (raw.length <= 90) return raw;
    return `${raw.slice(0, 90)}…`;
};

const formatDate = (isoDate: string) => {
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

const selected = ref<Appraisal | null>(null);
const isDialogOpen = ref(false);

const openView = (a: Appraisal) => {
    selected.value = a;
    isDialogOpen.value = true;
};

watch(isDialogOpen, (open) => {
    if (!open) selected.value = null;
});

const chartData = computed(() => {
    if (!selected.value) {
        return {
            labels: [],
            datasets: [{ data: [], backgroundColor: [], borderWidth: 0 }],
        };
    }

    const a = selected.value;

    const items: Array<{ label: string; value: number }> = [
        { label: 'Hard Skills', value: a.quality_work + a.quantity_work + a.task_knowledge },
        { label: 'Soft Skills', value: a.discipline + a.teamwork + a.communication + a.initiative },
        { label: 'KPI', value: a.target_realization + a.time_management },
        { label: 'Sikap & Adaptabilitas', value: a.attitude + a.adaptability },
    ];

    const leadershipTotal = (a.leadership_delegation ?? 0) + (a.leadership_development ?? 0);
    const leadershipExists = a.leadership_delegation !== null && a.leadership_delegation !== undefined && a.leadership_development !== null && a.leadership_development !== undefined;
    if (leadershipExists) {
        items.push({ label: 'Kepemimpinan', value: leadershipTotal });
    }

    const colors = ['#60a5fa', '#34d399', '#fbbf24', '#a78bfa', '#fb7185'];

    return {
        labels: items.map((x) => x.label),
        datasets: [
            {
                data: items.map((x) => x.value),
                backgroundColor: colors.slice(0, items.length),
                borderWidth: 0,
            },
        ],
    };
});

const chartOptions = computed(() => {
    return {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom' as const,
                labels: {
                    boxWidth: 10,
                    boxHeight: 10,
                },
            },
            tooltip: {
                callbacks: {
                    label: function (ctx: any) {
                        const label = ctx.label ?? '';
                        const value = ctx.parsed ?? 0;
                        return `${label}: ${value}`;
                    },
                },
            },
        },
    };
});

const deleteForm = useForm({});
const deleteAppraisal = (a: Appraisal) => {
    if (!confirm('Yakin ingin menghapus appraisal ini?')) return;
    deleteForm.delete(`/admin/lms-performance-appraisals/${a.id}`, { preserveScroll: true });
};

const scoreBadgeClass = (avg: number | null | undefined) => {
    if (avg === null || avg === undefined) {
        return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200';
    }

    if (avg >= 4.25) return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
    if (avg >= 3.5) return 'bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300';
    if (avg >= 2.75) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
    return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
};
</script>

<template>
    <Head title="Performance Appraisal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Performance Appraisal</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Penilaian kinerja karyawan</p>
                    </div>
                    <Link
                        href="/admin/lms-performance-appraisals/create"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Buat Penilaian
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div v-if="appraisals.data.length === 0" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada appraisal. Buat appraisal pertama.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/30">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Evaluator
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Skor (Avg)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Feedback
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="a in appraisals.data" :key="a.id">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ formatDate(a.evaluated_at) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ employeeLabel(a.user) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ a.evaluator?.name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="scoreBadgeClass(a.score_avg)"
                                    >
                                        <span v-if="a.score_avg !== null && a.score_avg !== undefined">{{ a.score_avg }}</span>
                                        <span v-else>-</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ feedbackPreview(a.feedback) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700 ring-1 ring-sky-600/20 transition-colors hover:bg-sky-100 dark:bg-sky-950/50 dark:text-sky-300 dark:ring-sky-400/30 dark:hover:bg-sky-950"
                                        @click="openView(a)"
                                    >
                                        <Eye class="mr-1 h-3 w-3" />
                                        View
                                    </button>
                                    <Link
                                        :href="`/admin/lms-performance-appraisals/${a.id}/edit`"
                                        class="ml-2 inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-300 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                    >
                                        <Pencil class="mr-1 h-3 w-3" />
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="ml-2 inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-300 dark:ring-red-400/30 dark:hover:bg-red-950"
                                        @click="deleteAppraisal(a)"
                                    >
                                        <Trash2 class="mr-1 h-3 w-3" />
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>

    <Dialog v-model:open="isDialogOpen">
        <DialogContent class="sm:max-w-lg">
            <div class="h-80 w-full">
                <Doughnut :data="chartData" :options="chartOptions" />
            </div>
            <div v-if="selected" class="mt-4">
                <div class="text-sm font-semibold text-gray-900 dark:text-white">Feedback</div>
                <div class="mt-1 max-h-40 overflow-y-auto rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                    {{ stripHtml(selected.feedback) || '-' }}
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
