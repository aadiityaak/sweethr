<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, CheckCircle, Clock, Plus, RefreshCw, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

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
    error?: string;
}

const { requests, monthlyCount, monthlyLimit, error } = defineProps<Props>();

const selectedStatus = ref('');

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved':
            return 'text-green-600';
        case 'rejected':
            return 'text-destructive';
        case 'pending':
            return 'text-orange-600';
        default:
            return 'text-muted-foreground';
    }
};

const getStatusBadgeColor = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-50 text-green-700 border-green-200';
        case 'rejected':
            return 'bg-red-50 text-red-700 border-red-200';
        case 'pending':
            return 'bg-orange-50 text-orange-700 border-orange-200';
        default:
            return 'bg-muted text-muted-foreground border-border';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'approved':
            return CheckCircle;
        case 'rejected':
            return XCircle;
        case 'pending':
            return AlertCircle;
        default:
            return Clock;
    }
};

const filterRequests = () => {
    router.get(
        '/shift-change-requests',
        {
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const isLimitReached = monthlyCount >= monthlyLimit;
</script>

<template>
    <Head title="Request Tukar Libur" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Container -->
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <!-- Mobile Header -->
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                            <div>
                                <h1 class="text-lg font-semibold">Request Tukar Libur</h1>
                                <p class="text-sm text-muted-foreground">Kelola tukar jadwal libur</p>
                            </div>
                        </div>
                        <Link
                            href="/shift-change-requests/create"
                            :class="{
                                'cursor-not-allowed opacity-50': isLimitReached,
                            }"
                            class="rounded-md bg-primary p-2 text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            <Plus class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="px-4 py-6 pb-24">
                <!-- Error Notification -->
                <div v-if="error" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/30">
                    <div class="flex items-center gap-3">
                        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Terjadi Masalah Koneksi</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                {{ error }}
                            </p>
                        </div>
                        <button
                            @click="$inertia.reload()"
                            class="rounded-md bg-red-100 p-2 text-red-600 transition-colors hover:bg-red-200 dark:bg-red-900 dark:text-red-400 dark:hover:bg-red-800"
                        >
                            <RefreshCw class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mb-6 grid grid-cols-2 gap-3">
                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-md bg-blue-100 dark:bg-blue-900/20">
                            <RefreshCw class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ monthlyCount }}/{{ monthlyLimit }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Request Bulan Ini</p>
                    </div>

                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 dark:bg-orange-900/20">
                            <AlertCircle class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ requests?.data?.filter((r) => r.status === 'pending').length || 0 }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Menunggu</p>
                    </div>

                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-md bg-green-100 dark:bg-green-900/20">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ requests?.data?.filter((r) => r.status === 'approved').length || 0 }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Disetujui</p>
                    </div>

                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-md bg-red-100 dark:bg-red-900/20">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ requests?.data?.filter((r) => r.status === 'rejected').length || 0 }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Ditolak</p>
                    </div>
                </div>

                <!-- Filter -->
                <div class="mb-6 rounded-lg border bg-card p-4">
                    <select v-model="selectedStatus" @change="filterRequests" class="w-full rounded-md border bg-background px-3 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>

                <!-- Requests List -->
                <div class="rounded-lg border bg-card">
                    <div class="border-b p-4">
                        <h2 class="flex items-center gap-2 text-lg font-semibold">
                            <RefreshCw class="h-4 w-4" />
                            Riwayat Request
                        </h2>
                    </div>

                    <div class="divide-y">
                        <div v-if="!requests?.data?.length && !error" class="p-8 text-center">
                            <RefreshCw class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p class="text-muted-foreground">Belum ada request tukar libur</p>
                            <Link
                                href="/shift-change-requests/create"
                                class="mt-4 inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                                :class="{ 'cursor-not-allowed opacity-50': isLimitReached }"
                            >
                                <Plus class="h-4 w-4" />
                                Buat Request Pertama
                            </Link>
                        </div>

                        <div v-else-if="error && !requests?.data?.length" class="p-8 text-center">
                            <AlertCircle class="mx-auto mb-4 h-12 w-12 text-red-500" />
                            <p class="mb-4 text-muted-foreground">Data tidak dapat dimuat</p>
                            <button
                                @click="$inertia.reload()"
                                class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                            >
                                <RefreshCw class="h-4 w-4" />
                                Coba Lagi
                            </button>
                        </div>

                        <div v-for="request in requests?.data || []" :key="request.id" class="p-4 transition-colors hover:bg-muted/50">
                            <div class="mb-2 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <component :is="getStatusIcon(request.status)" class="h-4 w-4" :class="getStatusColor(request.status)" />
                                    <span class="text-sm font-medium"> Request #{{ request.id }} </span>
                                </div>
                                <span class="rounded-full border px-2 py-1 text-xs font-medium" :class="getStatusBadgeColor(request.status)">
                                    {{ request.status === 'pending' ? 'Menunggu' : request.status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </div>

                            <div class="mb-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="rounded-md bg-red-50 p-2 dark:bg-red-950/30">
                                        <p class="text-xs font-medium text-red-800 dark:text-red-200">Libur Saat Ini</p>
                                        <p class="text-sm text-red-600 dark:text-red-300">{{ formatDate(request.original_date) }}</p>
                                    </div>
                                    <div class="rounded-md bg-green-50 p-2 dark:bg-green-950/30">
                                        <p class="text-xs font-medium text-green-800 dark:text-green-200">Ganti Kerja</p>
                                        <p class="text-sm text-green-600 dark:text-green-300">{{ formatDate(request.requested_date) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="request.reason" class="mb-2">
                                <p class="text-sm text-muted-foreground"><span class="font-medium">Alasan:</span> {{ request.reason }}</p>
                            </div>

                            <div class="text-xs text-muted-foreground">
                                Diajukan {{ formatDateTime(request.requested_at) }}
                                <span v-if="request.reviewed_at && request.reviewer"> • Direview {{ formatDateTime(request.reviewed_at) }} </span>
                            </div>

                            <div v-if="request.admin_notes && request.status !== 'pending'" class="mt-2 rounded-md bg-muted p-3">
                                <p class="text-sm"><span class="font-medium">Catatan Admin:</span> {{ request.admin_notes }}</p>
                            </div>

                            <div v-if="request.status === 'pending'" class="mt-3 flex gap-2">
                                <Link :href="`/shift-change-requests/${request.id}`" class="flex-1">
                                    <div
                                        class="w-full rounded-md bg-secondary px-3 py-2 text-center text-sm font-medium text-secondary-foreground transition-colors hover:bg-secondary/80"
                                    >
                                        Lihat Detail
                                    </div>
                                </Link>
                                <form @submit.prevent="$inertia.delete(`/shift-change-requests/${request.id}`)" class="inline">
                                    <button
                                        type="submit"
                                        @click="confirm('Apakah Anda yakin ingin membatalkan request ini?') || $event.preventDefault()"
                                        class="rounded-md bg-destructive px-3 py-2 text-sm font-medium text-destructive-foreground transition-colors hover:bg-destructive/90"
                                    >
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                            <div v-else class="mt-3">
                                <Link :href="`/shift-change-requests/${request.id}`" class="block">
                                    <div
                                        class="w-full rounded-md bg-secondary px-3 py-2 text-center text-sm font-medium text-secondary-foreground transition-colors hover:bg-secondary/80"
                                    >
                                        Lihat Detail
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="requests.meta?.total > requests.meta?.per_page" class="border-t p-4">
                        <p class="text-center text-sm text-muted-foreground">
                            Menampilkan {{ requests.meta.from }} - {{ requests.meta.to }} dari {{ requests.meta.total }} data
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <BottomNavigation current-route="/shift-change-requests" />
    </div>
</template>
