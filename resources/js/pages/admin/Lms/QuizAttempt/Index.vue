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

interface LmsQuiz {
    id: number;
    title: string;
    category: CategoryRef | null;
}

interface LmsQuizAttempt {
    id: number;
    user: UserRef;
    submitted_at: string | null;
    score: number | null;
    max_score: number | null;
    is_passed: boolean | null;
    created_at: string;
}

interface Props {
    quiz: LmsQuiz;
    attempts: {
        data: LmsQuizAttempt[];
        links: any[];
        meta?: any;
    };
    filters: {
        search?: string | null;
    };
}

const { quiz, attempts, filters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-materials' },
    { title: 'Kuis', href: '/admin/lms-quizzes' },
    { title: 'Attempt', href: `/admin/lms-quizzes/${quiz.id}/attempts` },
];

const categoryLabel = computed(() => {
    if (!quiz.category) return '-';
    if (quiz.category.parent?.name) return `${quiz.category.parent.name} / ${quiz.category.name}`;
    return quiz.category.name;
});

const search = ref(filters.search ?? '');

watch(
    () => search.value,
    (value) => {
        router.get(
            `/admin/lms-quizzes/${quiz.id}/attempts`,
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

const scoreLabel = (a: LmsQuizAttempt) => {
    if (a.score === null || a.score === undefined) return '-';
    if (a.max_score) return `${a.score} / ${a.max_score}`;
    return String(a.score);
};
</script>

<template>
    <Head :title="`Attempt Kuis: ${quiz.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <a
                            :href="`/admin/lms-quizzes/${quiz.id}`"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Attempt Kuis</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">{{ quiz.title }} • {{ categoryLabel }}</p>
                        </div>
                    </div>
                    <Link
                        :href="`/admin/lms-quizzes/${quiz.id}/edit`"
                        class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-amber-700"
                    >
                        Kelola Kuis
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
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Nilai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Lulus</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Waktu Submit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr v-for="a in attempts.data" :key="a.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ a.user?.name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ scoreLabel(a) }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="
                                            a.is_passed === true
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : a.is_passed === false
                                                  ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                                  : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                        "
                                    >
                                        {{ a.is_passed === true ? 'Ya' : a.is_passed === false ? 'Tidak' : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span v-if="a.submitted_at">{{ formatDate(a.submitted_at) }}</span>
                                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                                </td>
                            </tr>
                            <tr v-if="attempts.data.length === 0">
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada attempt untuk kuis ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

