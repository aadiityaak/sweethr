<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Clock, Eye, CheckCircle, XCircle, AlertCircle, Filter, Search, Download } from 'lucide-vue-next';
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

// Watch for filter changes and update URL
watch([statusFilter, fromDate, toDate], () => {
    const params = new URLSearchParams();

    if (statusFilter.value) params.append('status', statusFilter.value);
    if (fromDate.value) params.append('from_date', fromDate.value);
    if (toDate.value) params.append('to_date', toDate.value);

    router.get(window.location.pathname + '?' + params.toString(), {}, {
        preserveState: true,
        replace: true,
    });
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
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
    if (confirm('Apakah Anda yakin ingin menyetujui request ini?')) {
        router.patch(`/admin/shift-change-requests/${requestId}/approve`);
    }
};

const rejectRequest = (requestId: number) => {
    const reason = prompt('Alasan penolakan (opsional):');
    if (reason !== null) {
        router.patch(`/admin/shift-change-requests/${requestId}/reject`, {
            admin_notes: reason
        });
    }
};
</script>

<template>
    <Head title="Kelola Request Tukar Libur" />

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
                                Kelola permintaan tukar jadwal libur karyawan
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Request</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
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
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.pending }}</p>
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
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.approved }}</p>
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
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.rejected }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-8 rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                        <Filter class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <Select v-model="statusFilter">
                            <SelectTrigger>
                                <SelectValue placeholder="Semua Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Semua Status</SelectItem>
                                <SelectItem value="pending">Menunggu</SelectItem>
                                <SelectItem value="approved">Disetujui</SelectItem>
                                <SelectItem value="rejected">Ditolak</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dari Tanggal</label>
                        <Input
                            type="date"
                            v-model="fromDate"
                            class="h-10"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sampai Tanggal</label>
                        <Input
                            type="date"
                            v-model="toDate"
                            class="h-10"
                        />
                    </div>

                    <div class="flex items-end">
                        <Button variant="outline" @click="clearFilters" class="h-10">
                            Reset Filter
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <Calendar class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Daftar Request
                    </h3>
                </div>

                <div v-if="requests.data.length === 0" class="p-12 text-center">
                    <Calendar class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak ada request</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Belum ada request tukar libur yang sesuai dengan filter.
                    </p>
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="request in requests.data"
                        :key="request.id"
                        class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Employee Info -->
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
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
                                <div class="grid gap-3 mb-4 md:grid-cols-2">
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
                                    <p v-if="request.reason" class="mt-1">
                                        <span class="font-medium">Alasan:</span> {{ request.reason }}
                                    </p>
                                    <p v-if="request.reviewed_at && request.reviewer" class="mt-1">
                                        <span class="font-medium">Ditinjau oleh:</span> {{ request.reviewer.name }} pada {{ formatDateTime(request.reviewed_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 ml-4">
                                <Link :href="`/admin/shift-change-requests/${request.id}`">
                                    <Button variant="outline" size="sm">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </Link>

                                <template v-if="request.status === 'pending'">
                                    <Button
                                        variant="default"
                                        size="sm"
                                        @click="approveRequest(request.id)"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        <CheckCircle class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="rejectRequest(request.id)"
                                    >
                                        <XCircle class="h-4 w-4" />
                                    </Button>
                                </template>
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