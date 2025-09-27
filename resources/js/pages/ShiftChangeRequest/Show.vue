<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, ArrowLeft, User, FileText, AlertCircle, CheckCircle, XCircle } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    employee_id: string;
    department?: {
        id: number;
        name: string;
    };
    position?: {
        id: number;
        title: string;
    };
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
    request: ShiftChangeRequest;
}

const { request } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Request Tukar Libur',
        href: '/shift-change-requests',
    },
    {
        title: `Request #${request.id}`,
        href: `/shift-change-requests/${request.id}`,
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusConfig = (status: string) => {
    switch (status) {
        case 'pending':
            return {
                variant: 'secondary',
                icon: AlertCircle,
                text: 'Menunggu Persetujuan',
                bgClass: 'bg-amber-50 dark:bg-amber-950/30',
                textClass: 'text-amber-800 dark:text-amber-200',
                borderClass: 'border-amber-200 dark:border-amber-800'
            };
        case 'approved':
            return {
                variant: 'default',
                icon: CheckCircle,
                text: 'Disetujui',
                bgClass: 'bg-green-50 dark:bg-green-950/30',
                textClass: 'text-green-800 dark:text-green-200',
                borderClass: 'border-green-200 dark:border-green-800'
            };
        case 'rejected':
            return {
                variant: 'destructive',
                icon: XCircle,
                text: 'Ditolak',
                bgClass: 'bg-red-50 dark:bg-red-950/30',
                textClass: 'text-red-800 dark:text-red-200',
                borderClass: 'border-red-200 dark:border-red-800'
            };
        default:
            return {
                variant: 'secondary',
                icon: AlertCircle,
                text: 'Unknown',
                bgClass: 'bg-gray-50 dark:bg-gray-950/30',
                textClass: 'text-gray-800 dark:text-gray-200',
                borderClass: 'border-gray-200 dark:border-gray-800'
            };
    }
};

const statusConfig = getStatusConfig(request.status);
</script>

<template>
    <Head :title="`Request Tukar Libur #${request.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                Request Tukar Libur #{{ request.id }}
                            </h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">
                                Detail permintaan tukar jadwal libur
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <Link href="/shift-change-requests">
                            <Button variant="outline">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Kembali
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="mb-8 rounded-xl border p-6 shadow-sm" :class="[statusConfig.bgClass, statusConfig.borderClass]">
                <div class="flex items-center gap-3">
                    <component :is="statusConfig.icon" class="h-8 w-8" :class="statusConfig.textClass" />
                    <div>
                        <h2 class="text-xl font-semibold" :class="statusConfig.textClass">
                            {{ statusConfig.text }}
                        </h2>
                        <p class="text-sm opacity-80" :class="statusConfig.textClass">
                            Diajukan pada {{ formatDateTime(request.requested_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Request Details -->
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Detail Permintaan
                        </h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Date Changes -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/30">
                                <div class="flex items-center gap-2 mb-2">
                                    <Calendar class="h-4 w-4 text-red-600 dark:text-red-400" />
                                    <p class="text-sm font-medium text-red-800 dark:text-red-200">Tanggal Libur Saat Ini</p>
                                </div>
                                <p class="text-lg font-semibold text-red-900 dark:text-red-100">
                                    {{ formatDate(request.original_date) }}
                                </p>
                            </div>

                            <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950/30">
                                <div class="flex items-center gap-2 mb-2">
                                    <Clock class="h-4 w-4 text-green-600 dark:text-green-400" />
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Tanggal Ganti Kerja</p>
                                </div>
                                <p class="text-lg font-semibold text-green-900 dark:text-green-100">
                                    {{ formatDate(request.requested_date) }}
                                </p>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div v-if="request.reason">
                            <div class="flex items-center gap-2 mb-3">
                                <FileText class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Alasan</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                <p class="text-gray-900 dark:text-white">{{ request.reason }}</p>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <Clock class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Timeline</p>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                        <div class="h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Request diajukan</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(request.requested_at) }}</p>
                                    </div>
                                </div>

                                <div v-if="request.reviewed_at" class="flex items-center gap-3">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full"
                                         :class="request.status === 'approved' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30'">
                                        <div class="h-2 w-2 rounded-full"
                                             :class="request.status === 'approved' ? 'bg-green-600 dark:bg-green-400' : 'bg-red-600 dark:bg-red-400'"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Request {{ request.status === 'approved' ? 'disetujui' : 'ditolak' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(request.reviewed_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review Information -->
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                            <User class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Informasi Review
                        </h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Current Status -->
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Saat Ini</p>
                            <Badge :variant="statusConfig.variant as any" class="text-sm px-3 py-1">
                                {{ statusConfig.text }}
                            </Badge>
                        </div>

                        <!-- Reviewer Info -->
                        <div v-if="request.reviewer">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Ditinjau Oleh</p>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ request.reviewer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ request.reviewer.name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ request.reviewer.employee_id }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div v-if="request.admin_notes">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Catatan Admin</p>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                <p class="text-gray-900 dark:text-white">{{ request.admin_notes }}</p>
                            </div>
                        </div>

                        <!-- Pending Message -->
                        <div v-if="request.status === 'pending'" class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                            <div class="flex">
                                <AlertCircle class="h-5 w-5 text-amber-400 shrink-0" />
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Menunggu Review</p>
                                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                        Request Anda sedang menunggu untuk ditinjau oleh admin. Anda akan mendapat notifikasi setelah request diproses.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-end gap-3">
                <Link href="/shift-change-requests">
                    <Button variant="outline">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali ke Daftar
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
                        @click="confirm('Apakah Anda yakin ingin membatalkan request ini?') || $event.preventDefault()"
                    >
                        Batalkan Request
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>