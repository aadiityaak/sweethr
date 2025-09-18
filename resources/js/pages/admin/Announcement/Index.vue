<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Edit, Trash2, Eye, ToggleLeft, Calendar, User, Tag } from 'lucide-vue-next';

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
    announcements: {
        data: Announcement[];
        links: any[];
        meta: any;
    };
}

const { announcements } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Pengumuman', href: '/admin/announcements' },
];

const toggleStatus = (announcement: Announcement) => {
    const form = useForm({});
    form.patch(`/admin/announcements/${announcement.id}/toggle-status`, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteAnnouncement = (announcement: Announcement) => {
    if (confirm(`Yakin ingin menghapus pengumuman "${announcement.title}"?`)) {
        const form = useForm({});
        form.delete(`/admin/announcements/${announcement.id}`, {
            preserveState: true,
            preserveScroll: true,
        });
    }
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
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Kelola Pengumuman" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Kelola Pengumuman
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Buat dan kelola pengumuman untuk karyawan
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            href="/admin/announcements/create"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Buat Pengumuman
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Announcements Table -->
            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Pengumuman
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Kategori
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Prioritas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Dibuat
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            <tr
                                v-for="announcement in announcements.data"
                                :key="announcement.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-900"
                            >
                                <!-- Announcement Info -->
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-4">
                                        <div
                                            v-if="announcement.image_url"
                                            class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg"
                                        >
                                            <img
                                                :src="announcement.image_url"
                                                :alt="announcement.title"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800"
                                        >
                                            <Calendar class="h-6 w-6 text-gray-400" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ announcement.title }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                                {{ announcement.excerpt }}
                                            </p>
                                            <div class="mt-2 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                                <User class="h-3 w-3" />
                                                <span>{{ announcement.author.name }}</span>
                                                <span>•</span>
                                                <span>{{ formatDate(announcement.published_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="getCategoryColor(announcement.category.color)"
                                    >
                                        <Tag class="mr-1 h-3 w-3" />
                                        {{ announcement.category.name }}
                                    </span>
                                </td>

                                <!-- Priority -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="getPriorityColor(announcement.priority)"
                                    >
                                        {{ announcement.priority }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleStatus(announcement)"
                                        class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                                        :class="announcement.is_active
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-900/50'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                                    >
                                        <component
                                            :is="announcement.is_active ? Toggle : ToggleLeft"
                                            class="h-4 w-4"
                                        />
                                        {{ announcement.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>

                                <!-- Created -->
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(announcement.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/admin/announcements/${announcement.id}`"
                                            class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 ring-1 ring-blue-600/20 transition-colors hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:ring-blue-400/30 dark:hover:bg-blue-950"
                                        >
                                            <Eye class="mr-1 h-3 w-3" />
                                            Lihat
                                        </Link>
                                        <Link
                                            :href="`/admin/announcements/${announcement.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20 transition-colors hover:bg-amber-100 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-400/30 dark:hover:bg-amber-950"
                                        >
                                            <Edit class="mr-1 h-3 w-3" />
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteAnnouncement(announcement)"
                                            class="inline-flex items-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 transition-colors hover:bg-red-100 dark:bg-red-950/50 dark:text-red-400 dark:ring-red-400/30 dark:hover:bg-red-950"
                                        >
                                            <Trash2 class="mr-1 h-3 w-3" />
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div
                    v-if="announcements.data.length === 0"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <Calendar class="h-12 w-12 text-gray-400 dark:text-gray-600" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                        Belum ada pengumuman
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Mulai dengan membuat pengumuman pertama untuk karyawan.
                    </p>
                    <Link
                        href="/admin/announcements/create"
                        class="mt-4 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Buat Pengumuman
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="announcements.data.length > 0"
                    class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-950 sm:px-6"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="announcements.links[0].url"
                                :href="announcements.links[0].url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="announcements.links[announcements.links.length - 1].url"
                                :href="announcements.links[announcements.links.length - 1].url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Menampilkan
                                    <span class="font-medium">{{ announcements.meta.from || 0 }}</span>
                                    hingga
                                    <span class="font-medium">{{ announcements.meta.to || 0 }}</span>
                                    dari
                                    <span class="font-medium">{{ announcements.meta.total }}</span>
                                    hasil
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                    <Link
                                        v-for="(link, index) in announcements.links"
                                        :key="index"
                                        v-show="link.url"
                                        :href="link.url || '#'"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium transition-colors"
                                        :class="[
                                            link.active
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-950 dark:border-blue-400 dark:text-blue-400'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-950 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === announcements.links.length - 1 ? 'rounded-r-md' : '',
                                            'border'
                                        ]"
                                        v-html="link.label"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>