<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface UserRef {
    id: number;
    name: string;
}

interface CategoryRef {
    id: number;
    name: string;
    parent?: CategoryRef | null;
}

interface LmsAssignment {
    id: number;
    title: string;
    category: CategoryRef | null;
    max_score: number;
    due_at: string | null;
}

interface LmsAssignmentSubmission {
    id: number;
    user: UserRef;
    submitted_at: string | null;
    score: number | null;
    graded_at: string | null;
    grader: UserRef | null;
    created_at: string;
}

interface Props {
    assignment: LmsAssignment;
    submissions: {
        data: LmsAssignmentSubmission[];
        links: any[];
        meta?: any;
    };
    filters: {
        search?: string | null;
    };
}

const { assignment, submissions, filters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Tugas', href: '/admin/lms-assignments' },
    { title: 'Pengumpulan', href: `/admin/lms-assignments/${assignment.id}/submissions` },
];

const categoryLabel = computed(() => {
    if (!assignment.category) return '-';
    if (assignment.category.parent?.name) return `${assignment.category.parent.name} / ${assignment.category.name}`;
    return assignment.category.name;
});

const search = ref(filters.search ?? '');

watch(
    () => search.value,
    (value) => {
        router.get(
            `/admin/lms-assignments/${assignment.id}/submissions`,
            { search: value || undefined },
            { preserveState: true, replace: true, preserveScroll: true }
        );
    }
);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Pengumpulan Tugas: ${assignment.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <a
                            :href="`/admin/lms-assignments/${assignment.id}`"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pengumpulan Tugas</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">
                                {{ assignment.title }} • {{ categoryLabel }} • Skor maks: {{ assignment.max_score }}
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="`/admin/lms-assignments/${assignment.id}/edit`"
                        class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-amber-700"
                    >
                        Kelola Tugas
                    </Link>
                </div>
            </div>

            <div class="mb-6">
                <div class="relative max-w-md">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama karyawan..."
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-3 pl-10 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    />
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Waktu Submit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Skor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Dinilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="s in submissions.data" :key="s.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ s.user?.name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span v-if="s.submitted_at">{{ formatDate(s.submitted_at) }}</span>
                                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span v-if="s.score !== null && s.score !== undefined">{{ s.score }} / {{ assignment.max_score }}</span>
                                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span v-if="s.graded_at">{{ formatDate(s.graded_at) }}</span>
                                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                                    <div v-if="s.grader?.name" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        oleh {{ s.grader.name }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="submissions.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada pengumpulan untuk tugas ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

