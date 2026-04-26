<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, FileText } from 'lucide-vue-next';

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
}

interface Props {
    materials: {
        data: LmsMaterial[];
        links: any[];
        meta?: {
            from?: number;
            to?: number;
            total?: number;
            per_page?: number;
        };
    };
}

const { materials } = defineProps<Props>();

const categoryLabel = (material: LmsMaterial) => {
    if (!material.category) return '-';
    if (material.category.parent?.name) return `${material.category.parent.name} / ${material.category.name}`;
    return material.category.name;
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

const fileUrl = (material: LmsMaterial) => (material.file_path ? `/storage/${material.file_path}` : null);
const thumbnailUrl = (material: LmsMaterial) => (material.thumbnail_path ? `/storage/${material.thumbnail_path}` : null);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="LMS" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">LMS</h1>
                            <p class="text-sm text-muted-foreground">Materi pembelajaran</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <BookOpen class="h-4 w-4" />
                            Materi Terbaru
                        </h2>
                    </div>

                    <div class="divide-y">
                        <div v-for="material in materials.data" :key="material.id" class="p-4 transition-colors hover:bg-muted/50">
                            <div class="flex gap-3">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-md border bg-muted">
                                    <img
                                        v-if="thumbnailUrl(material)"
                                        :src="thumbnailUrl(material)!"
                                        :alt="material.title"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full w-full items-center justify-center">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-foreground">{{ material.title }}</p>
                                            <p class="mt-0.5 text-xs text-muted-foreground">{{ categoryLabel(material) }} • {{ formatDate(material.created_at) }}</p>
                                        </div>
                                        <a
                                            v-if="fileUrl(material)"
                                            :href="fileUrl(material)!"
                                            target="_blank"
                                            class="shrink-0 rounded-md bg-primary px-3 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                        >
                                            Buka
                                        </a>
                                    </div>

                                    <p v-if="material.description" class="mt-2 text-sm text-muted-foreground">
                                        {{ truncate(stripHtml(material.description)) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="!materials.data.length" class="p-8 text-center">
                            <BookOpen class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p class="text-muted-foreground">Belum ada materi LMS</p>
                        </div>
                    </div>

                    <div v-if="(materials.meta?.total ?? 0) > (materials.meta?.per_page ?? 0)" class="border-t p-4">
                        <p class="text-center text-sm text-muted-foreground">
                            Menampilkan {{ materials.meta?.from }} - {{ materials.meta?.to }} dari {{ materials.meta?.total }} data
                        </p>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>
