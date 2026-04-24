<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Clock, Edit, Eye, EyeOff, Tag, User } from 'lucide-vue-next';

interface AnnouncementCategory {
    id: number;
    name: string;
    color: string;
    icon: string;
}

interface User {
    id: number;
    name: string;
    employee_id: string;
}

interface Announcement {
    id: number;
    title: string;
    content: string;
    excerpt: string;
    category: AnnouncementCategory;
    author: User;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    is_active: boolean;
    published_at: string;
    expires_at: string | null;
    created_at: string;
    image_url: string | null;
}

interface Props {
    announcement: Announcement;
}

const { announcement } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Pengumuman', href: '/admin/announcements' },
    { title: announcement.title, href: `/admin/announcements/${announcement.id}` },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getPriorityColor = (priority: string) => {
    const colors = {
        low: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        normal: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
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
</script>

<template>
    <Head :title="`Detail Pengumuman - ${announcement.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detail Pengumuman</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Lihat detail lengkap pengumuman</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="`/admin/announcements/${announcement.id}/edit`"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                        <Link
                            href="/admin/announcements"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="space-y-6">
                <!-- Announcement Card -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-950 dark:ring-white/10">
                    <!-- Header Image -->
                    <div v-if="announcement.image_url" class="aspect-video w-full overflow-hidden">
                        <img :src="announcement.image_url" :alt="announcement.title" class="h-full w-full object-cover" />
                    </div>

                    <div class="p-6">
                        <!-- Title and Meta -->
                        <div class="mb-6">
                            <div class="mb-4 flex flex-wrap items-center gap-3">
                                <!-- Category -->
                                <span
                                    :class="getCategoryColor(announcement.category.color)"
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                >
                                    <Tag class="mr-1 h-3 w-3" />
                                    {{ announcement.category.name }}
                                </span>

                                <!-- Priority -->
                                <span
                                    :class="getPriorityColor(announcement.priority)"
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                >
                                    {{ getPriorityLabel(announcement.priority) }}
                                </span>

                                <!-- Status -->
                                <span
                                    :class="
                                        announcement.is_active
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                    "
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                >
                                    <component :is="announcement.is_active ? Eye : EyeOff" class="mr-1 h-3 w-3" />
                                    {{ announcement.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ announcement.title }}
                            </h1>

                            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <User class="mr-1 h-4 w-4" />
                                    <span>{{ announcement.author.name }}</span>
                                    <span class="ml-1 text-xs">({{ announcement.author.employee_id }})</span>
                                </div>
                                <div class="flex items-center">
                                    <Calendar class="mr-1 h-4 w-4" />
                                    <span>{{ formatDate(announcement.published_at) }}</span>
                                </div>
                                <div v-if="announcement.expires_at" class="flex items-center">
                                    <Clock class="mr-1 h-4 w-4" />
                                    <span>Berakhir: {{ formatDate(announcement.expires_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div v-if="announcement.excerpt" class="mb-6">
                            <p class="border-l-4 border-blue-500 pl-4 text-lg text-gray-600 italic dark:text-gray-300">
                                {{ announcement.excerpt }}
                            </p>
                        </div>

                        <!-- Content -->
                        <div class="prose prose-gray dark:prose-invert max-w-none">
                            <div v-html="announcement.content"></div>
                        </div>
                    </div>
                </div>

                <!-- Information Cards -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Publication Info -->
                    <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-950 dark:ring-white/10">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Informasi Publikasi</h3>
                        <dl class="mt-4 space-y-3">
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Tanggal Publikasi</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(announcement.published_at) }}
                                </dd>
                            </div>
                            <div v-if="announcement.expires_at">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Tanggal Berakhir</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(announcement.expires_at) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="text-sm font-medium">
                                    <span :class="announcement.is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400'">
                                        {{ announcement.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Category Info -->
                    <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-950 dark:ring-white/10">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Kategori</h3>
                        <div class="mt-4">
                            <div
                                :class="getCategoryColor(announcement.category.color)"
                                class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium"
                            >
                                <Tag class="mr-2 h-4 w-4" />
                                {{ announcement.category.name }}
                            </div>
                        </div>
                    </div>

                    <!-- Priority Info -->
                    <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-950 dark:ring-white/10">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Prioritas</h3>
                        <div class="mt-4">
                            <div
                                :class="getPriorityColor(announcement.priority)"
                                class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium"
                            >
                                {{ getPriorityLabel(announcement.priority) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
