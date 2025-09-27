<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, ArrowLeft, User, FileText, AlertCircle, CheckCircle, XCircle, RefreshCw } from 'lucide-vue-next';

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

    <div class="mx-auto max-w-[480px] bg-background min-h-screen">
        <!-- Header -->
        <div class="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b border-border">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center gap-3">
                    <button @click="$inertia.visit('/shift-change-requests')" class="p-2 -ml-2 rounded-lg hover:bg-muted transition-colors">
                        <ArrowLeft class="h-5 w-5" />
                    </button>
                    <h1 class="text-lg font-semibold">Request #{{ request.id }}</h1>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-6">
            <!-- Status Card -->
            <div class="rounded-lg border bg-card p-4" :class="[statusConfig.bgClass, statusConfig.borderClass]">
                <div class="flex items-center gap-3">
                    <component :is="statusConfig.icon" class="h-6 w-6" :class="statusConfig.textClass" />
                    <div>
                        <h2 class="text-lg font-semibold" :class="statusConfig.textClass">
                            {{ statusConfig.text }}
                        </h2>
                        <p class="text-sm opacity-80" :class="statusConfig.textClass">
                            Diajukan {{ formatDateTime(request.requested_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Request Details -->
            <div class="rounded-lg border bg-card p-4">
                <div class="mb-4 flex items-center gap-2">
                    <RefreshCw class="h-4 w-4" />
                    <h3 class="text-lg font-semibold">Detail Permintaan</h3>
                </div>

                <div class="space-y-4">
                    <!-- Date Changes -->
                    <div class="grid gap-3 grid-cols-2">
                        <div class="rounded-md bg-red-50 p-3 dark:bg-red-950/30">
                            <div class="flex items-center gap-2 mb-1">
                                <Calendar class="h-4 w-4 text-red-600 dark:text-red-400" />
                                <p class="text-xs font-medium text-red-800 dark:text-red-200">Libur Saat Ini</p>
                            </div>
                            <p class="text-sm font-semibold text-red-900 dark:text-red-100">
                                {{ formatDate(request.original_date) }}
                            </p>
                        </div>

                        <div class="rounded-md bg-green-50 p-3 dark:bg-green-950/30">
                            <div class="flex items-center gap-2 mb-1">
                                <Clock class="h-4 w-4 text-green-600 dark:text-green-400" />
                                <p class="text-xs font-medium text-green-800 dark:text-green-200">Ganti Kerja</p>
                            </div>
                            <p class="text-sm font-semibold text-green-900 dark:text-green-100">
                                {{ formatDate(request.requested_date) }}
                            </p>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div v-if="request.reason">
                        <div class="flex items-center gap-2 mb-2">
                            <FileText class="h-4 w-4 text-muted-foreground" />
                            <p class="text-sm font-medium">Alasan</p>
                        </div>
                        <div class="rounded-md bg-muted p-3">
                            <p class="text-sm">{{ request.reason }}</p>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                            <p class="text-sm font-medium">Timeline</p>
                        </div>
                        <div class="space-y-2">
                            <div class="text-xs text-muted-foreground">
                                Diajukan {{ formatDateTime(request.requested_at) }}
                            </div>
                            <div v-if="request.reviewed_at" class="text-xs text-muted-foreground">
                                {{ request.status === 'approved' ? 'Disetujui' : 'Ditolak' }} {{ formatDateTime(request.reviewed_at) }}
                                <span v-if="request.reviewer"> oleh {{ request.reviewer.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div v-if="request.admin_notes && request.status !== 'pending'">
                        <div class="flex items-center gap-2 mb-2">
                            <User class="h-4 w-4 text-muted-foreground" />
                            <p class="text-sm font-medium">Catatan Admin</p>
                        </div>
                        <div class="rounded-md bg-muted p-3">
                            <p class="text-sm">{{ request.admin_notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div v-if="request.status === 'pending'" class="space-y-3">
                <button
                    @click="$inertia.visit('/shift-change-requests')"
                    class="w-full rounded-md bg-secondary px-4 py-3 text-sm font-medium text-secondary-foreground hover:bg-secondary/80 transition-colors"
                >
                    Kembali ke Daftar
                </button>

                <form
                    @submit.prevent="$inertia.delete(`/shift-change-requests/${request.id}`)"
                    class="inline w-full"
                >
                    <button
                        type="submit"
                        @click="confirm('Apakah Anda yakin ingin membatalkan request ini?') || $event.preventDefault()"
                        class="w-full rounded-md bg-destructive px-4 py-3 text-sm font-medium text-destructive-foreground hover:bg-destructive/90 transition-colors"
                    >
                        Batalkan Request
                    </button>
                </form>
            </div>
            <div v-else class="space-y-3">
                <button
                    @click="$inertia.visit('/shift-change-requests')"
                    class="w-full rounded-md bg-secondary px-4 py-3 text-sm font-medium text-secondary-foreground hover:bg-secondary/80 transition-colors"
                >
                    Kembali ke Daftar
                </button>
            </div>
        </div>
    </div>
</template>