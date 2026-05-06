<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

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

const feedbackPreview = (text: string | null | undefined) => {
    const raw = String(text ?? '').trim();
    if (!raw) return '-';
    if (raw.length <= 90) return raw;
    return `${raw.slice(0, 90)}…`;
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="a in appraisals.data" :key="a.id">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ a.evaluated_at }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ employeeLabel(a.user) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ a.evaluator?.name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    <span v-if="a.score_avg !== null && a.score_avg !== undefined">{{ a.score_avg }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ feedbackPreview(a.feedback) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

