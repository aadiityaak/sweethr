<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, FileText, Image as ImageIcon } from 'lucide-vue-next';
import { computed } from 'vue';

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
    youtube_url: string | null;
    file_path: string | null;
    thumbnail_path: string | null;
    created_at: string;
}

interface Props {
    material: LmsMaterial;
}

const { material } = defineProps<Props>();

const categoryLabel = computed(() => {
    if (!material.category) return '-';
    if (material.category.parent?.name) return `${material.category.parent.name} / ${material.category.name}`;
    return material.category.name;
});

const fileUrl = computed(() => (material.file_path ? `/storage/${material.file_path}` : null));

const youtubeEmbedUrl = computed(() => {
    const raw = String(material.youtube_url ?? '').trim();
    if (!raw) return null;

    const normalized = raw.startsWith('http://') || raw.startsWith('https://') ? raw : `https://${raw}`;

    try {
        const url = new URL(normalized);
        const host = url.hostname.toLowerCase();
        const path = url.pathname;

        let id: string | null = null;

        if (host === 'youtu.be') {
            id = path.split('/').filter(Boolean)[0] ?? null;
        } else if (host.endsWith('youtube.com') || host.endsWith('youtube-nocookie.com')) {
            if (path === '/watch') {
                id = url.searchParams.get('v');
            } else {
                const parts = path.split('/').filter(Boolean);
                const embedIndex = parts.indexOf('embed');
                const shortsIndex = parts.indexOf('shorts');
                if (embedIndex >= 0) id = parts[embedIndex + 1] ?? null;
                else if (shortsIndex >= 0) id = parts[shortsIndex + 1] ?? null;
                else if (parts[0] === 'watch') id = url.searchParams.get('v');
            }
        } else if (host === 'drive.google.com') {
            const parts = path.split('/').filter(Boolean);
            if (parts[0] === 'file' && parts[1] === 'd') {
                id = parts[2] ?? null;
            } else {
                id = url.searchParams.get('id');
            }

            if (!id) return null;
            return `https://drive.google.com/file/d/${encodeURIComponent(id)}/preview`;
        }

        if (!id) return null;

        return `https://www.youtube-nocookie.com/embed/${encodeURIComponent(id)}?rel=0&controls=0&disablekb=1&fs=0&iv_load_policy=3&modestbranding=1&playsinline=1`;
    } catch {
        return null;
    }
});

const fileExt = computed(() => {
    const path = material.file_path ?? '';
    const name = path.split('/').pop() ?? '';
    const idx = name.lastIndexOf('.');
    return idx >= 0 ? name.slice(idx + 1).toLowerCase() : '';
});

const isPdf = computed(() => fileExt.value === 'pdf');
const isImage = computed(() => ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(fileExt.value));

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`LMS - ${material.title}`" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/lms" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div class="min-w-0">
                            <h1 class="truncate text-lg font-semibold">{{ material.title }}</h1>
                            <p class="text-sm text-muted-foreground">{{ categoryLabel }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <p class="text-xs text-muted-foreground">Diposting {{ formatDate(material.created_at) }}</p>
                    </div>

                    <div class="p-4">
                        <div v-if="material.description" class="prose prose-sm max-w-none dark:prose-invert" v-html="material.description"></div>
                        <div v-else class="text-sm text-muted-foreground">Tidak ada deskripsi.</div>
                    </div>

                    <div class="border-t p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <component :is="isImage ? ImageIcon : FileText" class="h-4 w-4" />
                                <span v-if="fileExt">{{ fileExt.toUpperCase() }}</span>
                                <span v-else>FILE</span>
                            </div>
                            <a
                                v-if="fileUrl"
                                :href="fileUrl"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                <Download class="h-4 w-4" />
                                Unduh
                            </a>
                        </div>
                    </div>
                </div>

                <div v-if="fileUrl" class="mt-6 rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="text-sm font-semibold">Pratinjau</h2>
                        <p class="mt-1 text-xs text-muted-foreground" v-if="!isPdf && !isImage">Pratinjau tidak tersedia untuk tipe file ini.</p>
                    </div>

                    <div class="p-4">
                        <div v-if="youtubeEmbedUrl" class="mb-4">
                            <iframe
                                :src="youtubeEmbedUrl"
                                class="aspect-video w-full rounded-md border bg-background"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            />
                        </div>
                        <iframe v-if="isPdf" :src="fileUrl" class="h-[70vh] w-full rounded-md border bg-background" />
                        <img v-else-if="isImage" :src="fileUrl" :alt="material.title" class="h-auto w-full rounded-md border" />
                    </div>
                </div>

                <div v-else-if="youtubeEmbedUrl" class="mt-6 rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="text-sm font-semibold">Video</h2>
                    </div>
                    <div class="p-4">
                        <iframe
                            :src="youtubeEmbedUrl"
                            class="aspect-video w-full rounded-md border bg-background"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        />
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>
