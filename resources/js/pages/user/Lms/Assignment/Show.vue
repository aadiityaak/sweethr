<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardList, Download, Send } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';

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
}

interface AttemptStats {
    max_attempts: number;
    submitted_attempts: number;
    remaining_attempts: number;
}

interface Submission {
    id: number;
    submitted_at: string | null;
    content: string | null;
    attachment_path: string | null;
    score: number | null;
    feedback: string | null;
    graded_at: string | null;
}

interface Props {
    assignment: LmsAssignment;
    submission: Submission | null;
    attemptStats: AttemptStats;
}

const { assignment, submission, attemptStats } = defineProps<Props>();

const categoryLabel = computed(() => {
    if (!assignment.category) return '-';
    if (assignment.category.parent?.name) return `${assignment.category.parent.name} / ${assignment.category.name}`;
    return assignment.category.name;
});

const dueAtMs = computed(() => (assignment.due_at ? new Date(assignment.due_at).getTime() : null));
const isLate = computed(() => (dueAtMs.value === null ? false : Date.now() > dueAtMs.value));

const formatDateTime = (dateString: string | null) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const attachment = ref<File | null>(null);
const attachmentPreviewName = computed(() => attachment.value?.name ?? null);

const form = useForm({
    content: submission?.content ?? '',
    attachment: null as File | null,
});

const handleAttachmentChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    attachment.value = file;
    form.attachment = file;
};

const clearAttachment = () => {
    attachment.value = null;
    form.attachment = null;
};

onBeforeUnmount(() => {
    clearAttachment();
});

const submit = () => {
    form.post(`/lms/assignments/${assignment.id}/submit`, {
        forceFormData: true,
        preserveScroll: true,
    });
};

const attachmentUrl = computed(() => {
    if (!submission?.attachment_path) return null;
    return `/storage/${submission.attachment_path}`;
});

const canSubmit = computed(() => {
    return attemptStats.remaining_attempts > 0;
});
</script>

<template>
    <Head :title="`LMS - Tugas: ${assignment.title}`" />

    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/lms" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                        <div class="min-w-0">
                            <h1 class="truncate text-lg font-semibold">{{ assignment.title }}</h1>
                            <p class="text-sm text-muted-foreground">{{ categoryLabel }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <ClipboardList class="h-4 w-4" />
                            Detail Tugas
                        </h2>
                        <div class="mt-2 text-sm text-muted-foreground">
                            <span>Skor maks {{ assignment.max_score }}</span>
                            <span class="mx-2">•</span>
                            <span>Attempt {{ attemptStats.submitted_attempts }}/{{ attemptStats.max_attempts }}</span>
                            <span v-if="assignment.due_at" class="mx-2">•</span>
                            <span v-if="assignment.due_at" :class="isLate ? 'text-destructive' : ''">Due {{ formatDateTime(assignment.due_at) }}</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div v-if="assignment.description" class="prose prose-sm max-w-none dark:prose-invert" v-html="assignment.description"></div>
                        <div v-else class="text-sm text-muted-foreground">Tidak ada deskripsi.</div>

                        <div class="mt-4">
                            <h3 class="text-sm font-semibold">Instruksi</h3>
                            <div
                                v-if="assignment.instructions"
                                class="prose prose-sm mt-2 max-w-none dark:prose-invert"
                                v-html="assignment.instructions"
                            ></div>
                            <div v-else class="mt-2 text-sm text-muted-foreground">Tidak ada instruksi.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="text-lg font-semibold">Status Pengumpulan</h2>
                    </div>

                    <div class="p-4">
                        <div v-if="submission?.submitted_at" class="grid gap-2 text-sm">
                            <div class="rounded-lg border bg-background p-3">
                                <p class="text-xs font-semibold text-muted-foreground">Terakhir submit</p>
                                <p class="mt-1 font-semibold">{{ formatDateTime(submission.submitted_at) }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border bg-background p-3">
                                    <p class="text-xs font-semibold text-muted-foreground">Nilai</p>
                                    <p class="mt-1 font-semibold">
                                        <span v-if="submission.score !== null && submission.score !== undefined">{{ submission.score }} / {{ assignment.max_score }}</span>
                                        <span v-else>-</span>
                                    </p>
                                </div>
                                <div class="rounded-lg border bg-background p-3">
                                    <p class="text-xs font-semibold text-muted-foreground">Dinilai</p>
                                    <p class="mt-1 font-semibold">
                                        <span v-if="submission.graded_at">{{ formatDateTime(submission.graded_at) }}</span>
                                        <span v-else>-</span>
                                    </p>
                                </div>
                            </div>
                            <div v-if="submission.feedback" class="rounded-lg border bg-background p-3">
                                <p class="text-xs font-semibold text-muted-foreground">Feedback</p>
                                <p class="mt-1 whitespace-pre-line">{{ submission.feedback }}</p>
                            </div>
                            <a
                                v-if="attachmentUrl"
                                :href="attachmentUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border bg-background px-4 py-3 text-sm font-semibold transition-colors hover:bg-muted/50"
                            >
                                <Download class="h-4 w-4" />
                                Unduh Lampiran
                            </a>
                        </div>

                        <div v-else class="text-sm text-muted-foreground">Belum ada pengumpulan.</div>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="text-lg font-semibold">Kumpulkan Tugas</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Isi jawaban dan/atau unggah lampiran (opsional).</p>
                    </div>

                    <form class="p-4" @submit.prevent="submit">
                        <div v-if="!canSubmit" class="mb-4 rounded-lg border bg-background p-3 text-sm text-muted-foreground">
                            Max attempt sudah tercapai.
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Jawaban</label>
                            <textarea
                                v-model="form.content"
                                rows="5"
                                class="mt-2 w-full rounded-lg border bg-background px-3 py-2 text-sm"
                                placeholder="Tulis jawaban di sini..."
                            ></textarea>
                            <p v-if="form.errors.content" class="mt-1 text-xs text-destructive">{{ form.errors.content }}</p>
                        </div>

                        <div class="mt-4">
                            <label class="text-sm font-semibold">Lampiran (opsional)</label>
                            <input type="file" class="mt-2 block w-full text-sm" @change="handleAttachmentChange" />
                            <div v-if="attachmentPreviewName" class="mt-2 flex items-center justify-between gap-3 rounded-md border bg-background px-3 py-2 text-sm">
                                <span class="truncate">{{ attachmentPreviewName }}</span>
                                <button type="button" class="rounded-md bg-secondary px-3 py-1 text-xs font-semibold" @click="clearAttachment">
                                    Hapus
                                </button>
                            </div>
                            <p v-if="form.errors.attachment" class="mt-1 text-xs text-destructive">{{ form.errors.attachment }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || !canSubmit"
                            class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-60"
                        >
                            <Send class="h-4 w-4" />
                            Submit
                        </button>
                    </form>
                </div>
            </div>

            <BottomNavigation current-route="/lms" />
        </div>
    </div>
</template>
