<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { BarChart3, Search, Users } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Stats {
    employees: number;
    materials_active: number;
    quizzes_active: number;
    assignments_active: number;
    quiz_submitted: number;
    quiz_pass_rate: number;
    assignment_submitted: number;
    assignment_pending_grading: number;
}

interface UserRow {
    id: number;
    name: string;
    employee_id: string | null;
    quiz: {
        attempts: number;
        submitted: number;
        passed: number;
        avg_percent: number | null;
        last_submitted_at: string | null;
    };
    assignment: {
        submissions: number;
        graded: number;
        avg_percent: number | null;
        last_submitted_at: string | null;
    };
    last_activity_at: string | null;
}

interface Paginator<T> {
    data: T[];
    links?: any;
    meta?: any;
    current_page?: number;
    last_page?: number;
    prev_page_url?: string | null;
    next_page_url?: string | null;
}

interface Props {
    stats: Stats;
    users: Paginator<UserRow>;
    filters: {
        search?: string | null;
    };
}

const { stats, users, filters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Pelacakan', href: '/admin/lms-tracking' },
];

const search = ref(filters.search ?? '');
let searchTimer: number | undefined;

watch(
    search,
    (value) => {
        if (searchTimer) window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            router.get(
                '/admin/lms-tracking',
                { search: value || undefined },
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                }
            );
        }, 300);
    },
    { flush: 'post' }
);

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

const formatDateTime = (value: string | null) => {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const quizPassLabel = computed(() => {
    if (!stats.quiz_submitted) return '0%';
    return `${stats.quiz_pass_rate}%`;
});
</script>

<template>
    <Head title="Pelacakan LMS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pelacakan LMS</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Memantau kemajuan peserta didik/karyawan dan laporan kinerja</p>
                    </div>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200/50 bg-white p-4 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Karyawan</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.employees }}</div>
                        </div>
                        <Users class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200/50 bg-white p-4 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Konten Aktif</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ stats.materials_active + stats.quizzes_active + stats.assignments_active }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Materi: {{ stats.materials_active }} • Kuis: {{ stats.quizzes_active }} • Tugas: {{ stats.assignments_active }}
                            </div>
                        </div>
                        <BarChart3 class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200/50 bg-white p-4 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Kuis (Submitted)</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.quiz_submitted }}</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ quizPassLabel }} lulus</div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200/50 bg-white p-4 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Tugas</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.assignment_submitted }}</div>
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ stats.assignment_pending_grading }} belum dinilai</div>
                    </div>
                </div>
            </div>

            <div class="mb-4 flex items-center justify-between gap-3">
                <div class="relative w-full max-w-md">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama / NIK..."
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Kuis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Tugas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Aktivitas Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50/60 dark:hover:bg-gray-900/40">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ u.name }}</div>
                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ u.employee_id ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                                        <div>Attempt: <span class="font-semibold">{{ u.quiz.attempts }}</span></div>
                                        <div>Submitted: <span class="font-semibold">{{ u.quiz.submitted }}</span></div>
                                        <div>Lulus: <span class="font-semibold">{{ u.quiz.passed }}</span></div>
                                        <div>Rata2: <span class="font-semibold">{{ u.quiz.avg_percent ?? '-' }}</span><span v-if="u.quiz.avg_percent !== null">%</span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                                        <div>Submit: <span class="font-semibold">{{ u.assignment.submissions }}</span></div>
                                        <div>Dinilai: <span class="font-semibold">{{ u.assignment.graded }}</span></div>
                                        <div>Rata2: <span class="font-semibold">{{ u.assignment.avg_percent ?? '-' }}</span><span v-if="u.assignment.avg_percent !== null">%</span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ formatDateTime(u.last_activity_at) }}
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="pageInfo(users).last > 1" class="flex items-center justify-between gap-3 border-t border-gray-200/60 p-4 dark:border-gray-800/60">
                    <Link
                        v-if="prevUrl(users)"
                        :href="prevUrl(users)!"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        preserve-scroll
                        preserve-state
                    >
                        Prev
                    </Link>
                    <span v-else class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        Prev
                    </span>

                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-300">Halaman {{ pageInfo(users).current }} / {{ pageInfo(users).last }}</div>

                    <Link
                        v-if="nextUrl(users)"
                        :href="nextUrl(users)!"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                        preserve-scroll
                        preserve-state
                    >
                        Next
                    </Link>
                    <span v-else class="rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        Next
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
