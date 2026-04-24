<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Calendar, Clock, Tag, User, X } from 'lucide-vue-next';

interface AnnouncementCategory {
    id: number;
    name: string;
    color: string;
    icon: string;
}

interface Author {
    id: number;
    name: string;
}

interface Announcement {
    id: number;
    title: string;
    content: string;
    excerpt: string;
    category: AnnouncementCategory;
    author: Author;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    published_at: string;
    expires_at: string | null;
    image_url: string | null;
}

interface Props {
    announcement: Announcement | null;
    open: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    close: [];
}>();

const getCategoryColor = (color: string) => {
    const colors = {
        blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        amber: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        indigo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    };
    return colors[color as keyof typeof colors] || colors.blue;
};

const getPriorityColor = (priority: string) => {
    const colors = {
        low: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        normal: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        high: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        urgent: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    };
    return colors[priority as keyof typeof colors] || colors.normal;
};

const getPriorityLabel = (priority: string) => {
    const labels = {
        low: 'Rendah',
        normal: 'Normal',
        high: 'Tinggi',
        urgent: 'Mendesak',
    };
    return labels[priority as keyof typeof labels] || 'Normal';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const handleClose = () => {
    emit('close');
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-h-[90vh] max-w-4xl overflow-y-auto">
            <div v-if="announcement" class="space-y-6">
                <!-- Header -->
                <DialogHeader>
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <DialogTitle class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ announcement.title }}
                            </DialogTitle>

                            <!-- Meta Information -->
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                <!-- Category -->
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="getCategoryColor(announcement.category.color)"
                                >
                                    <Tag class="mr-1 h-3 w-3" />
                                    {{ announcement.category.name }}
                                </span>

                                <!-- Priority -->
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="getPriorityColor(announcement.priority)"
                                >
                                    {{ getPriorityLabel(announcement.priority) }}
                                </span>

                                <!-- Author -->
                                <span class="flex items-center text-gray-600 dark:text-gray-400">
                                    <User class="mr-1 h-4 w-4" />
                                    {{ announcement.author.name }}
                                </span>

                                <!-- Published Date -->
                                <span class="flex items-center text-gray-600 dark:text-gray-400">
                                    <Calendar class="mr-1 h-4 w-4" />
                                    {{ formatDate(announcement.published_at) }}
                                </span>
                            </div>

                            <!-- Expires Info -->
                            <div v-if="announcement.expires_at" class="mt-2 flex items-center text-xs text-amber-600 dark:text-amber-400">
                                <Clock class="mr-1 h-3 w-3" />
                                Berlaku hingga {{ formatDate(announcement.expires_at) }}
                            </div>
                        </div>

                        <!-- Close Button -->
                        <button
                            @click="handleClose"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </DialogHeader>

                <!-- Image -->
                <div v-if="announcement.image_url" class="mt-6">
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <img :src="announcement.image_url" :alt="announcement.title" class="h-auto max-h-96 w-full object-cover" />
                    </div>
                </div>

                <!-- Content -->
                <div class="prose prose-gray dark:prose-invert max-w-none">
                    <div
                        class="leading-relaxed whitespace-pre-wrap text-gray-900 dark:text-gray-100"
                        v-html="announcement.content.replace(/\n/g, '<br>')"
                    ></div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Dipublikasikan oleh {{ announcement.author.name }}</div>

                        <button
                            @click="handleClose"
                            class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Custom prose styles for content */
.prose {
    font-size: 1rem;
    line-height: 1.7;
}

.prose p {
    margin-bottom: 1rem;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}

.prose ul,
.prose ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.prose li {
    margin: 0.25rem 0;
}

.prose strong {
    font-weight: 600;
}

.prose a {
    color: #3b82f6;
    text-decoration: underline;
}

.prose a:hover {
    color: #1d4ed8;
}

.dark .prose a {
    color: #60a5fa;
}

.dark .prose a:hover {
    color: #93c5fd;
}
</style>
