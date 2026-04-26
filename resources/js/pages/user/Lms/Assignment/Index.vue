<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardList } from 'lucide-vue-next';

interface CategoryRef {
    id: number;
    name: string;
    parent?: CategoryRef | null;
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
    assignments: {
        data: LmsAssignment[];
        links: any[];
        meta?: any;
    };
}

const { assignments } = defineProps<Props>();

const categoryLabel = (item: LmsAssignment) => {
    if (!item.category) return '-';
    if (item.category.parent?.name) return `${item.category.parent.name} / ${item.category.name}`;
    return item.category.name;
};

const stripHtml = (html: string) => {
    return String(html ?? '')
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
};

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
</script>

<template>
    <Head title="LMS - Tugas" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/lms" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">Tugas</h1>
                            <p class="text-sm text-muted-foreground">Daftar tugas</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <ClipboardList class="h-4 w-4" />
                            Tugas
                        </h2>
                    </div>

                    <div class="divide-y">
                        <div v-for="a in assignments.data" :key="a.id" class="p-4 transition-colors hover:bg-muted/50">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-foreground">{{ a.title }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">
                                        {{ categoryLabel(a) }} • {{ formatDate(a.created_at) }}
                                        <span v-if="a.due_at"> • Due {{ formatDateTime(a.due_at) }}</span>
                                    </p>
                                    <p v-if="a.description" class="mt-2 text-sm text-muted-foreground">
                                        {{ stripHtml(a.description) }}
                                    </p>
                                </div>
                                <Link
                                    :href="`/lms/assignments/${a.id}`"
                                    class="shrink-0 rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    Buka
                                </Link>
                            </div>
                        </div>

                        <div v-if="!assignments.data.length" class="p-8 text-center">
                            <ClipboardList class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p class="text-muted-foreground">Belum ada tugas</p>
                        </div>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>

