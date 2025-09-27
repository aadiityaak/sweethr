<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, Plus, Eye, Trash2, AlertCircle, CheckCircle, XCircle } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    employee_id: string;
}

interface ShiftChangeRequest {
    id: number;
    original_date: string;
    requested_date: string;
    reason: string | null;
    status: 'pending' | 'approved' | 'rejected';
    requested_at: string;
    reviewed_at: string | null;
    admin_notes: string | null;
    reviewer: User | null;
}

interface Props {
    requests: {
        data: ShiftChangeRequest[];
        links: any;
        meta: any;
    };
    monthlyCount: number;
    monthlyLimit: number;
}

const { requests, monthlyCount, monthlyLimit } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Request Tukar Libur',
        href: '/shift-change-requests',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'pending':
            return { variant: 'secondary', icon: AlertCircle, text: 'Menunggu' };
        case 'approved':
            return { variant: 'default', icon: CheckCircle, text: 'Disetujui' };
        case 'rejected':
            return { variant: 'destructive', icon: XCircle, text: 'Ditolak' };
        default:
            return { variant: 'secondary', icon: AlertCircle, text: 'Unknown' };
    }
};

const isLimitReached = monthlyCount >= monthlyLimit;
</script>

<template>
    <Head title="Request Tukar Libur" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                Request Tukar Libur
                            </h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">
                                Kelola permintaan tukar jadwal libur Anda
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            href="/shift-change-requests/create"
                            :class="{
                                'opacity-50 cursor-not-allowed': isLimitReached
                            }"
                        >
                            <Button
                                :disabled="isLimitReached"
                                class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Buat Request
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Monthly Stats -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Request Bulan Ini</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ monthlyCount }}/{{ monthlyLimit }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-green-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-green-950/30">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-500/10 ring-1 ring-green-500/20">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Disetujui</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ requests.data.filter(r => r.status === 'approved').length }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                            <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Menunggu</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ requests.data.filter(r => r.status === 'pending').length }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-red-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-red-950/30">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/20">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ditolak</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ requests.data.filter(r => r.status === 'rejected').length }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests List -->
            <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <Calendar class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Riwayat Request
                    </h3>
                </div>

                <div v-if="requests.data.length === 0" class="p-12 text-center">
                    <Calendar class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum ada request</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Anda belum pernah mengajukan request tukar libur.
                    </p>
                    <Link href="/shift-change-requests/create">
                        <Button
                            :disabled="isLimitReached"
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Buat Request Pertama
                        </Button>
                    </Link>
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="request in requests.data"
                        :key="request.id"
                        class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <component
                                        :is="getStatusBadge(request.status).icon"
                                        class="h-5 w-5"
                                        :class="{
                                            'text-amber-600': request.status === 'pending',
                                            'text-green-600': request.status === 'approved',
                                            'text-red-600': request.status === 'rejected'
                                        }"
                                    />
                                    <Badge :variant="getStatusBadge(request.status).variant as any">
                                        {{ getStatusBadge(request.status).text }}
                                    </Badge>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDateTime(request.requested_at) }}
                                    </span>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="rounded-lg bg-red-50 p-3 dark:bg-red-950/30">
                                        <p class="text-sm font-medium text-red-800 dark:text-red-200">Libur Saat Ini</p>
                                        <p class="text-sm text-red-600 dark:text-red-300">{{ formatDate(request.original_date) }}</p>
                                    </div>
                                    <div class="rounded-lg bg-green-50 p-3 dark:bg-green-950/30">
                                        <p class="text-sm font-medium text-green-800 dark:text-green-200">Ganti Kerja</p>
                                        <p class="text-sm text-green-600 dark:text-green-300">{{ formatDate(request.requested_date) }}</p>
                                    </div>
                                </div>

                                <div v-if="request.reason" class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Alasan:</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ request.reason }}</p>
                                </div>

                                <div v-if="request.admin_notes && request.status !== 'pending'" class="mt-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Admin:</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ request.admin_notes }}</p>
                                    <p v-if="request.reviewer" class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                        Ditinjau oleh: {{ request.reviewer.name }} pada {{ formatDateTime(request.reviewed_at!) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2 ml-4">
                                <Link :href="`/shift-change-requests/${request.id}`">
                                    <Button variant="outline" size="sm">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </Link>

                                <form
                                    v-if="request.status === 'pending'"
                                    @submit.prevent="$inertia.delete(`/shift-change-requests/${request.id}`)"
                                    class="inline"
                                >
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        size="sm"
                                        @click="confirm('Apakah Anda yakin ingin membatalkan request ini?') || $event.preventDefault()"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="requests.links && requests.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan {{ requests.meta.from }} sampai {{ requests.meta.to }} dari {{ requests.meta.total }} hasil
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="(link, index) in requests.links"
                                :key="index"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md transition-colors',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                        ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500'
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>