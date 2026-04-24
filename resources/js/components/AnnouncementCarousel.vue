<script setup lang="ts">
import { Calendar, ChevronLeft, ChevronRight, Tag, User } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

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
    announcements: Announcement[];
    autoPlay?: boolean;
    autoPlayInterval?: number;
}

const props = withDefaults(defineProps<Props>(), {
    autoPlay: true,
    autoPlayInterval: 5000,
});

const emit = defineEmits<{
    announcementClick: [announcement: Announcement];
}>();

const currentIndex = ref(0);
const isPaused = ref(false);
let autoPlayTimer: number | null = null;

const nextSlide = () => {
    if (props.announcements.length === 0) return;
    currentIndex.value = (currentIndex.value + 1) % props.announcements.length;
};

const prevSlide = () => {
    if (props.announcements.length === 0) return;
    currentIndex.value = currentIndex.value === 0 ? props.announcements.length - 1 : currentIndex.value - 1;
};

const goToSlide = (index: number) => {
    currentIndex.value = index;
};

const startAutoPlay = () => {
    if (!props.autoPlay || props.announcements.length <= 1) return;

    autoPlayTimer = window.setInterval(() => {
        if (!isPaused.value) {
            nextSlide();
        }
    }, props.autoPlayInterval);
};

const stopAutoPlay = () => {
    if (autoPlayTimer) {
        clearInterval(autoPlayTimer);
        autoPlayTimer = null;
    }
};

const pauseAutoPlay = () => {
    isPaused.value = true;
};

const resumeAutoPlay = () => {
    isPaused.value = false;
};

const handleAnnouncementClick = (announcement: Announcement) => {
    emit('announcementClick', announcement);
};

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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

onMounted(() => {
    if (props.announcements.length > 0) {
        startAutoPlay();
    }
});

onUnmounted(() => {
    stopAutoPlay();
});
</script>

<template>
    <div class="relative">
        <!-- Empty State -->
        <div v-if="announcements.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
            <Calendar class="mb-4 h-12 w-12 text-gray-400 dark:text-gray-600" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada pengumuman</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pengumuman terbaru akan muncul di sini</p>
        </div>

        <!-- Carousel -->
        <div
            v-else
            class="relative overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950"
            @mouseenter="pauseAutoPlay"
            @mouseleave="resumeAutoPlay"
        >
            <!-- Slides Container -->
            <div class="relative">
                <div class="flex transition-transform duration-500 ease-in-out" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
                    <div v-for="announcement in announcements" :key="announcement.id" class="w-full flex-shrink-0">
                        <!-- Banner Content -->
                        <div
                            class="flex cursor-pointer items-center gap-6 p-6 transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/50"
                            @click="handleAnnouncementClick(announcement)"
                        >
                            <!-- Image -->
                            <div class="flex-shrink-0">
                                <div v-if="announcement.image_url" class="h-20 w-20 overflow-hidden rounded-lg">
                                    <img :src="announcement.image_url" :alt="announcement.title" class="h-full w-full object-cover" />
                                </div>
                                <div
                                    v-else
                                    class="flex h-20 w-20 items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30"
                                >
                                    <Calendar class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <!-- Title -->
                                        <h3 class="line-clamp-2 text-lg font-semibold text-gray-900 dark:text-white">📢 {{ announcement.title }}</h3>

                                        <!-- Excerpt -->
                                        <p class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ announcement.excerpt }}
                                        </p>

                                        <!-- Meta Info -->
                                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                            <!-- Category -->
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                                :class="getCategoryColor(announcement.category.color)"
                                            >
                                                <Tag class="mr-1 h-3 w-3" />
                                                {{ announcement.category.name }}
                                            </span>

                                            <!-- Date -->
                                            <span class="flex items-center">
                                                <Calendar class="mr-1 h-3 w-3" />
                                                {{ formatDate(announcement.published_at) }}
                                            </span>

                                            <!-- Author -->
                                            <span class="flex items-center">
                                                <User class="mr-1 h-3 w-3" />
                                                {{ announcement.author.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <div v-if="announcements.length > 1" class="absolute inset-y-0 left-0 flex items-center">
                <button
                    @click="prevSlide"
                    class="ml-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-gray-700 shadow-lg transition-all hover:scale-110 hover:bg-white dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800"
                    :class="{ 'opacity-50': announcements.length <= 1 }"
                    :disabled="announcements.length <= 1"
                >
                    <ChevronLeft class="h-4 w-4" />
                </button>
            </div>

            <div v-if="announcements.length > 1" class="absolute inset-y-0 right-0 flex items-center">
                <button
                    @click="nextSlide"
                    class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-gray-700 shadow-lg transition-all hover:scale-110 hover:bg-white dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800"
                    :class="{ 'opacity-50': announcements.length <= 1 }"
                    :disabled="announcements.length <= 1"
                >
                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>

            <!-- Dots Indicator -->
            <div v-if="announcements.length > 1" class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                <button
                    v-for="(announcement, index) in announcements"
                    :key="`dot-${index}`"
                    @click="goToSlide(index)"
                    class="h-2 w-2 rounded-full transition-all"
                    :class="[
                        index === currentIndex
                            ? 'w-4 bg-blue-600 dark:bg-blue-400'
                            : 'bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500',
                    ]"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>
