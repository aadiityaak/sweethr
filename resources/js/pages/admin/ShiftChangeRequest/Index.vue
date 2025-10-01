<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, Calendar, CheckCircle, Eye, Filter, XCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';

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
    user: User;
    reviewer: User | null;
}

interface Props {
    requests: {
        data: ShiftChangeRequest[];
        links: any;
        meta: any;
    };
    filters: {
        status?: string;
        from_date?: string;
        to_date?: string;
    };
    stats: {
        pending: number;
        approved: number;
        rejected: number;
        total: number;
    };
}

const { requests, filters, stats } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin/dashboard',
    },
    {
        title: 'Request Tukar Libur',
        href: '/admin/shift-change-requests',
    },
];

const statusFilter = ref(filters.status || '');
const fromDate = ref(filters.from_date || '');
const toDate = ref(filters.to_date || '');

// Modal states
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const selectedRequest = ref<number | null>(null);
const rejectReason = ref('');
const processing = ref(false);

// Watch for filter changes and update URL
watch([statusFilter, fromDate, toDate], () => {
    const params = new URLSearchParams();

    if (statusFilter.value) params.append('status', statusFilter.value);
    if (fromDate.value) params.append('from_date', fromDate.value);
    if (toDate.value) params.append('to_date', toDate.value);

    router.get(
        window.location.pathname + '?' + params.toString(),
        {},
        {
            preserveState: true,
            replace: true,
        },
    );
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'pending':
            return { variant: 'secondary', icon: AlertCircle, text: 'Menunggu', class: 'text-amber-600' };
        case 'approved':
            return { variant: 'default', icon: CheckCircle, text: 'Disetujui', class: 'text-green-600' };
        case 'rejected':
            return { variant: 'destructive', icon: XCircle, text: 'Ditolak', class: 'text-red-600' };
        default:
            return { variant: 'secondary', icon: AlertCircle, text: 'Unknown', class: 'text-gray-600' };
    }
};

const clearFilters = () => {
    statusFilter.value = '';
    fromDate.value = '';
    toDate.value = '';
};

const approveRequest = (requestId: number) => {
    selectedRequest.value = requestId;
    showApproveModal.value = true;
};

const rejectRequest = (requestId: number) => {
    selectedRequest.value = requestId;
    rejectReason.value = '';
    showRejectModal.value = true;
};

const handleApprove = () => {
    if (selectedRequest.value) {
        processing.value = true;
        router.patch(
            `/admin/shift-change-requests/${selectedRequest.value}/approve`,
            {},
            {
                onSuccess: () => {
                    processing.value = false;
                    showApproveModal.value = false;
                    selectedRequest.value = null;
                },
                onError: () => {
                    processing.value = false;
                },
            },
        );
    }
};

const handleReject = () => {
    if (selectedRequest.value) {
        processing.value = true;
        router.patch(
            `/admin/shift-change-requests/${selectedRequest.value}/reject`,
            {
                admin_notes: rejectReason.value,
            },
            {
                onSuccess: () => {
                    processing.value = false;
                    showRejectModal.value = false;
                    selectedRequest.value = null;
                    rejectReason.value = '';
                },
                onError: () => {
                    processing.value = false;
                },
            },
        );
    }
};

const cancelApprove = () => {
    showApproveModal.value = false;
    selectedRequest.value = null;
};

const cancelReject = () => {
    showRejectModal.value = false;
    selectedRequest.value = null;
    rejectReason.value = '';
};
</script>

<template>
    <Head title="Kelola Request Tukar Libur" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Request Tukar Libur</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola permintaan tukar jadwal libur karyawan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="mb-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Requests -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Request</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Semua permintaan tukar libur
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ stats.total }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">100%</span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Pending Requests -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                                <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Menunggu</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Permintaan menunggu persetujuan
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-amber-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-amber-50/50 px-3 py-2 dark:bg-amber-950/30">
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Menunggu</span>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ stats.pending }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total ? Math.round((stats.pending / stats.total) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Approved Requests -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Disetujui</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Permintaan yang disetujui
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Disetujui</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ stats.approved }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total ? Math.round((stats.approved / stats.total) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Rejected Requests -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-red-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-red-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/20">
                                <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Ditolak</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Permintaan yang ditolak
                                </p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-red-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-red-50/50 px-3 py-2 dark:bg-red-950/30">
                            <span class="text-sm font-medium text-red-700 dark:text-red-400">Ditolak</span>
                            <span class="text-sm font-semibold text-red-800 dark:text-red-300">{{ stats.rejected }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.total ? Math.round((stats.rejected / stats.total) * 100) : 0 }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-red-500/5 to-rose-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="group relative mb-10 overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30"
            >
                <div class="border-b border-gray-200/50 p-6 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-500/10 ring-1 ring-gray-500/20">
                            <Filter class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter & Pencarian</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Filter request tukar libur berdasarkan kriteria</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                id="status"
                                v-model="statusFilter"
                                class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset focus:ring-2 focus:ring-blue-600 focus:ring-inset sm:text-sm sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-600"
                            >
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>

                        <div>
                            <label for="from_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                            <DatePicker id="from_date" v-model="fromDate" placeholder="Pilih tanggal mulai" class="mt-1 h-10 w-full" />
                        </div>

                        <div>
                            <label for="to_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                            <DatePicker id="to_date" v-model="toDate" placeholder="Pilih tanggal akhir" class="mt-1 h-10 w-full" />
                        </div>

                        <div class="flex items-end">
                            <Button variant="outline" @click="clearFilters" class="h-10 w-full"> Reset Filter </Button>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <div class="mt-4">
                        <button
                            type="button"
                            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                            @click="clearFilters"
                        >
                            Bersihkan Filter
                        </button>
                    </div>
                </div>
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-gray-500/5 to-slate-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
            </div>

            <!-- Requests List -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Request</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ requests.data.length }} request ditemukan</p>
                        </div>
                    </div>
                </div>

                <div v-if="requests.data.length === 0" class="p-12 text-center">
                    <Calendar class="mx-auto mb-4 h-12 w-12 text-gray-300 dark:text-gray-600" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Tidak ada request</h3>
                    <p class="text-gray-600 dark:text-gray-400">Belum ada request tukar libur yang sesuai dengan filter.</p>
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="request in requests.data"
                        :key="request.id"
                        class="p-6 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-800/50"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Employee Info -->
                                <div class="mb-4 flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                    >
                                        {{ request.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ request.user.name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ request.user.employee_id }} • {{ request.user.department?.name }}
                                        </p>
                                    </div>
                                    <Badge :variant="getStatusBadge(request.status).variant as any">
                                        {{ getStatusBadge(request.status).text }}
                                    </Badge>
                                </div>

                                <!-- Date Changes -->
                                <div class="mb-4 grid gap-3 md:grid-cols-2">
                                    <div class="rounded-lg bg-red-50 p-3 dark:bg-red-950/30">
                                        <p class="text-sm font-medium text-red-800 dark:text-red-200">Libur Saat Ini</p>
                                        <p class="text-sm text-red-600 dark:text-red-300">{{ formatDate(request.original_date) }}</p>
                                    </div>
                                    <div class="rounded-lg bg-green-50 p-3 dark:bg-green-950/30">
                                        <p class="text-sm font-medium text-green-800 dark:text-green-200">Ganti Kerja</p>
                                        <p class="text-sm text-green-600 dark:text-green-300">{{ formatDate(request.requested_date) }}</p>
                                    </div>
                                </div>

                                <!-- Request Info -->
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <p>Diajukan pada {{ formatDateTime(request.requested_at) }}</p>
                                    <p v-if="request.reason" class="mt-1"><span class="font-medium">Alasan:</span> {{ request.reason }}</p>
                                    <p v-if="request.reviewed_at && request.reviewer" class="mt-1">
                                        <span class="font-medium">Ditinjau oleh:</span> {{ request.reviewer.name }} pada
                                        {{ formatDateTime(request.reviewed_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="ml-4 flex gap-2">
                                <Link :href="`/admin/shift-change-requests/${request.id}`">
                                    <Button variant="outline" size="sm">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </Link>

                                <template v-if="request.status === 'pending'">
                                    <Button variant="default" size="sm" @click="approveRequest(request.id)" class="bg-green-600 hover:bg-green-700">
                                        <CheckCircle class="h-4 w-4" />
                                    </Button>
                                    <Button variant="destructive" size="sm" @click="rejectRequest(request.id)">
                                        <XCircle class="h-4 w-4" />
                                    </Button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="requests.links && requests.links.length > 3" class="border-t border-gray-200/50 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan {{ requests.meta?.from || 0 }} sampai {{ requests.meta?.to || 0 }} dari {{ requests.meta?.total || 0 }} hasil
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="(link, index) in requests.links"
                                :key="index"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="[
                                    'rounded-md px-3 py-2 text-sm transition-colors',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                          ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                          : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500',
                                ]"
                            />
                        </div>
                    </div>
                </div>
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
            </div>
        </div>

        <!-- Approve Confirmation Modal -->
        <ConfirmationModal
            :show="showApproveModal"
            title="Setujui Request"
            message="Apakah Anda yakin ingin menyetujui request tukar libur ini?"
            confirm-text="Ya, Setujui"
            cancel-text="Batal"
            type="info"
            :processing="processing"
            @confirm="handleApprove"
            @cancel="cancelApprove"
        />

        <!-- Reject Confirmation Modal -->
        <ConfirmationModal
            :show="showRejectModal"
            title="Tolak Request"
            message="Apakah Anda yakin ingin menolak request tukar libur ini?"
            confirm-text="Ya, Tolak"
            cancel-text="Batal"
            type="danger"
            :processing="processing"
            @confirm="handleReject"
            @cancel="cancelReject"
        >
            <template #message>
                <div class="space-y-3">
                    <p>Apakah Anda yakin ingin menolak request tukar libur ini?</p>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Alasan Penolakan (opsional) </label>
                        <textarea
                            v-model="rejectReason"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Masukkan alasan penolakan..."
                            :disabled="processing"
                        ></textarea>
                    </div>
                </div>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
