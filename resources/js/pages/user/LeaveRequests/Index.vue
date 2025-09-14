<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Clock, CheckCircle, XCircle, AlertCircle, Home, Settings, ArrowLeft, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    employee_id: string;
}

interface LeaveType {
    id: number;
    name: string;
    max_days_per_year: number;
    requires_approval: boolean;
}

interface LeaveRequest {
    id: number;
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    reviewed_at?: string;
    rejection_reason?: string;
    attachment_path?: string;
    attachment_original_name?: string;
    user: User;
    leave_type: LeaveType;
    reviewer?: User;
}

interface Props {
    leaveRequests?: {
        data: LeaveRequest[];
        links: any[];
        meta: any;
    };
    leaveTypes: LeaveType[];
    filters: {
        status?: string;
    };
    stats: {
        pending_count: number;
        approved_count: number;
        rejected_count: number;
        leave_balance: number;
    };
}

const { leaveRequests, leaveTypes, filters, stats } = defineProps<Props>();

const selectedStatus = ref(filters.status || '');

const filterRequests = () => {
    router.get('/leave-requests', {
        status: selectedStatus.value || undefined,
    }, {
        preserveState: true,
        replace: true,
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Permintaan Cuti" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Container -->
        <div class="mx-auto max-w-[480px] bg-background min-h-screen">
            <!-- Mobile Header -->
            <div class="bg-background/95 backdrop-blur-sm border-b sticky top-0 z-40">
                <div class="px-4 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground hover:bg-secondary/80 transition-colors">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                            <div>
                                <h1 class="text-lg font-semibold">Permintaan Cuti</h1>
                                <p class="text-sm text-muted-foreground">Kelola cuti Anda</p>
                            </div>
                        </div>
                        <Link
                            href="/leave-requests/create"
                            class="rounded-md bg-primary p-2 text-primary-foreground hover:bg-primary/90 transition-colors"
                        >
                            <Plus class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="px-4 py-6 pb-24">
                <!-- Stats -->
                <div class="mb-6 grid gap-3 grid-cols-2">
                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-orange-100 dark:bg-orange-900/20 flex items-center justify-center">
                            <AlertCircle class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.pending_count }}</p>
                        <p class="text-xs text-muted-foreground font-medium">Menunggu</p>
                    </div>

                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.approved_count }}</p>
                        <p class="text-xs text-muted-foreground font-medium">Disetujui</p>
                    </div>

                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-red-100 dark:bg-red-900/20 flex items-center justify-center">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.rejected_count }}</p>
                        <p class="text-xs text-muted-foreground font-medium">Ditolak</p>
                    </div>

                    <div class="rounded-lg border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 w-10 h-10 rounded-md bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p class="text-2xl font-bold">{{ stats.leave_balance }}</p>
                        <p class="text-xs text-muted-foreground font-medium">Sisa Cuti</p>
                    </div>
                </div>

                <!-- Filter -->
                <div class="mb-6 rounded-lg border bg-card p-4">
                    <select
                        v-model="selectedStatus"
                        @change="filterRequests"
                        class="w-full rounded-md border bg-background px-3 py-2 text-sm"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>

                <!-- Leave Requests List -->
                <div class="rounded-lg border bg-card">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-semibold flex items-center gap-2">
                            <Calendar class="h-4 w-4" />
                            Riwayat Pengajuan
                        </h2>
                    </div>

                    <div class="divide-y">
                        <div
                            v-for="request in leaveRequests?.data || []"
                            :key="request.id"
                            class="p-4 hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <component
                                        :is="getStatusIcon(request.status)"
                                        class="h-4 w-4"
                                        :class="getStatusColor(request.status)"
                                    />
                                    <span class="text-sm font-medium">
                                        {{ request.leave_type.name }}
                                    </span>
                                </div>
                                <span
                                    class="rounded-full border px-2 py-1 text-xs font-medium"
                                    :class="getStatusBadgeColor(request.status)"
                                >
                                    {{ request.status === 'pending' ? 'Menunggu' : request.status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </div>

                            <div class="mb-2">
                                <p class="text-sm font-medium">{{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}</p>
                                <p class="text-xs text-muted-foreground">{{ request.total_days }} hari</p>
                            </div>

                            <div class="mb-2">
                                <p class="text-sm text-muted-foreground">
                                    <span class="font-medium">Alasan:</span> {{ request.reason }}
                                </p>
                                <div v-if="request.attachment_path" class="mt-2 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <a
                                        :href="`/storage/${request.attachment_path}`"
                                        target="_blank"
                                        class="text-xs text-primary hover:underline"
                                    >
                                        {{ request.attachment_original_name || 'Lihat Lampiran' }}
                                    </a>
                                </div>
                            </div>

                            <div class="text-xs text-muted-foreground">
                                Diajukan {{ formatDateTime(request.created_at) }}
                                <span v-if="request.reviewed_at && request.reviewer">
                                    • Direview {{ formatDateTime(request.reviewed_at) }}
                                </span>
                            </div>

                            <div v-if="request.rejection_reason" class="mt-2 rounded-md bg-muted p-3">
                                <p class="text-sm">
                                    <span class="font-medium">Alasan Penolakan:</span> {{ request.rejection_reason }}
                                </p>
                            </div>
                        </div>

                        <div v-if="!leaveRequests?.data?.length" class="p-8 text-center">
                            <Calendar class="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
                            <p class="text-muted-foreground">Belum ada permintaan cuti</p>
                            <Link
                                href="/leave-requests/create"
                                class="mt-4 inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors"
                            >
                                <Plus class="h-4 w-4" />
                                Ajukan Cuti Pertama
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="leaveRequests?.meta?.total > leaveRequests?.meta?.per_page" class="p-4 border-t">
                        <p class="text-sm text-muted-foreground text-center">
                            Menampilkan {{ leaveRequests.meta.from }} - {{ leaveRequests.meta.to }} dari {{ leaveRequests.meta.total }} data
                        </p>
                    </div>
                </div>
            </div>

            <!-- Fixed Bottom Navigation -->
            <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[480px] bg-background border-t z-50">
                <div class="grid grid-cols-4 py-2">
                    <Link href="/home" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Home class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Beranda</span>
                    </Link>

                    <Link href="/attendance" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Clock class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Absensi</span>
                    </Link>

                    <Link href="/leave-requests" class="flex flex-col items-center py-3 px-2 text-primary">
                        <div class="rounded-md bg-primary/10 p-2 mb-1">
                            <Calendar class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Cuti</span>
                    </Link>

                    <Link href="/user/profile" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Settings class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Profil</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>