<template>
    <Head title="Admin - Kelola Cuti" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kelola Cuti</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola pengajuan cuti karyawan</p>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Requests -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                <Calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pengajuan</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_requests }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-yellow-500/10">
                                <Clock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Menunggu</p>
                                <div class="flex items-baseline gap-2">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.pending_count }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">({{ stats.pending_percentage }}%)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approved -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10">
                                <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Disetujui</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.approved_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejected -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                                <XCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ditolak</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.rejected_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="mb-8 grid gap-6 lg:grid-cols-2">
                <!-- Monthly Trend Chart -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Pengajuan 6 Bulan Terakhir</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Statistik pengajuan cuti bulanan</p>
                        </div>
                        <div class="h-80">
                            <Chart :data="monthlyChartData" type="line" :options="{ responsive: true, maintainAspectRatio: false }" />
                        </div>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Status</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Perbandingan status pengajuan</p>
                        </div>
                        <div class="h-80">
                            <Chart :data="statusChartData" type="doughnut" :options="{ responsive: true, maintainAspectRatio: false }" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-6">
                        <div class="md:col-span-2">
                            <Input v-model="search" placeholder="Cari karyawan..." class="w-full" @input="debouncedSearch" />
                        </div>
                        <div>
                            <select
                                v-model="selectedStatus"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                                @change="applyFilters"
                            >
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="selectedLeaveType"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                                @change="applyFilters"
                            >
                                <option value="">Semua Tipe</option>
                                <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="selectedDepartment"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                                @change="applyFilters"
                            >
                                <option value="">Semua Departemen</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <Button variant="outline" @click="clearFilters" class="w-full">
                                <FilterX class="mr-2 h-4 w-4" />
                                Reset
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Requests Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="flex items-center text-lg font-semibold text-gray-900 dark:text-white">
                        <Calendar class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                        Daftar Pengajuan Cuti
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ leaveRequests.total }} pengajuan ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Karyawan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tipe Cuti</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Durasi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Diajukan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="request in leaveRequests.data"
                                :key="request.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{
                                                getInitials(request.user.name)
                                            }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ request.user.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ request.user.employee_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                    >
                                        {{ request.leave_type.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(request.start_date) }}</p>
                                        <p class="text-gray-500 dark:text-gray-400">s/d {{ formatDate(request.end_date) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">{{ request.duration }} hari</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': request.status === 'pending',
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': request.status === 'approved',
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': request.status === 'rejected',
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ getStatusLabel(request.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(request.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Button
                                            v-if="request.status === 'pending'"
                                            size="sm"
                                            @click="approveRequest(request)"
                                            class="text-green-600 hover:bg-green-50"
                                            variant="ghost"
                                        >
                                            <Check class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            v-if="request.status === 'pending'"
                                            size="sm"
                                            @click="openRejectModal(request)"
                                            class="text-red-600 hover:bg-red-50"
                                            variant="ghost"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                        <Button size="sm" variant="ghost" @click="viewDetails(request)">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="leaveRequests.total > 10" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ leaveRequests.from || 0 }} sampai {{ leaveRequests.to || 0 }} dari {{ leaveRequests.total }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in leaveRequests.links"
                                :key="link.label"
                                :disabled="!link.url"
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                                class="pagination-btn"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <Dialog v-model:open="showDetailsModal">
            <DialogContent class="flex max-h-[90vh] flex-col sm:max-w-2xl">
                <DialogHeader class="shrink-0">
                    <DialogTitle>Detail Pengajuan Cuti</DialogTitle>
                    <DialogDescription> Informasi lengkap pengajuan cuti {{ selectedRequest?.user.name }} </DialogDescription>
                </DialogHeader>
                <div v-if="selectedRequest" class="flex-1 space-y-6 overflow-y-auto pr-2">
                    <!-- Employee Info -->
                    <div class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Informasi Karyawan</h4>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Nama</label>
                                <p class="text-sm">{{ selectedRequest.user.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">ID Karyawan</label>
                                <p class="text-sm">{{ selectedRequest.user.employee_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Details -->
                    <div class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Detail Cuti</h4>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tipe Cuti</label>
                                <p class="text-sm">{{ selectedRequest.leave_type.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Status</label>
                                <span
                                    :class="{
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400':
                                            selectedRequest.status === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': selectedRequest.status === 'approved',
                                        'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': selectedRequest.status === 'rejected',
                                    }"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                >
                                    {{ getStatusLabel(selectedRequest.status) }}
                                </span>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tanggal Mulai</label>
                                <p class="text-sm">{{ formatDate(selectedRequest.start_date) }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tanggal Selesai</label>
                                <p class="text-sm">{{ formatDate(selectedRequest.end_date) }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Durasi</label>
                                <p class="text-sm">{{ selectedRequest.duration }} hari</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tanggal Pengajuan</label>
                                <p class="text-sm">{{ formatDate(selectedRequest.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Alasan Cuti</h4>
                        <p class="text-sm whitespace-pre-wrap">{{ selectedRequest.reason || 'Tidak ada alasan yang diberikan' }}</p>
                    </div>

                    <!-- Admin Notes (if exists) -->
                    <div v-if="selectedRequest.admin_notes" class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Catatan Admin</h4>
                        <p class="text-sm whitespace-pre-wrap">{{ selectedRequest.admin_notes }}</p>
                    </div>
                </div>
                <DialogFooter class="mt-4 shrink-0 border-t pt-4">
                    <Button variant="outline" @click="showDetailsModal = false">Tutup</Button>
                    <div v-if="selectedRequest?.status === 'pending'" class="flex gap-2">
                        <Button
                            @click="
                                approveRequest(selectedRequest);
                                showDetailsModal = false;
                            "
                            class="bg-green-600 hover:bg-green-700"
                        >
                            <Check class="mr-2 h-4 w-4" />
                            Setujui
                        </Button>
                        <Button
                            @click="
                                openRejectModal(selectedRequest);
                                showDetailsModal = false;
                            "
                            class="bg-red-600 hover:bg-red-700"
                        >
                            <X class="mr-2 h-4 w-4" />
                            Tolak
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Approve Confirmation Modal -->
        <ConfirmationModal
            v-if="showApproveModal"
            :title="`Setujui Pengajuan Cuti`"
            :message="`Yakin ingin menyetujui pengajuan cuti ${selectedRequest?.user.name}?`"
            confirm-text="Setujui"
            cancel-text="Batal"
            confirm-variant="success"
            @confirm="confirmApprove"
            @cancel="showApproveModal = false"
        />

        <!-- Reject Modal -->
        <Dialog v-model:open="showRejectModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Tolak Pengajuan Cuti</DialogTitle>
                    <DialogDescription> Berikan alasan penolakan untuk {{ selectedRequest?.user.name }} </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Alasan Penolakan *</label>
                        <Textarea v-model="rejectForm.admin_notes" placeholder="Jelaskan alasan penolakan..." class="mt-1" rows="4" />
                        <p v-if="rejectForm.errors.admin_notes" class="mt-1 text-sm text-red-600">
                            {{ rejectForm.errors.admin_notes }}
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showRejectModal = false">Batal</Button>
                    <Button @click="submitReject" :disabled="rejectForm.processing" class="bg-red-600 hover:bg-red-700">
                        {{ rejectForm.processing ? 'Menolak...' : 'Tolak Pengajuan' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import { Button } from '@/components/ui/button';
import Chart from '@/components/ui/Chart.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Calendar, Check, CheckCircle, Clock, Eye, FilterX, X, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface LeaveRequest {
    id: number;
    user: {
        id: number;
        name: string;
        employee_id: string;
    };
    leave_type: {
        id: number;
        name: string;
    };
    start_date: string;
    end_date: string;
    duration: number;
    status: 'pending' | 'approved' | 'rejected';
    reason: string;
    admin_notes?: string;
    created_at: string;
}

interface Stats {
    total_requests: number;
    pending_count: number;
    approved_count: number;
    rejected_count: number;
    this_month_count: number;
    pending_percentage: number;
}

interface Props {
    leaveRequests: {
        data: LeaveRequest[];
        total: number;
        from: number;
        to: number;
        links: any[];
    };
    stats: Stats;
    monthlyTrend: any[];
    leaveTypes: any[];
    departments: any[];
    filters: {
        search?: string;
        status?: string;
        leave_type?: string;
        department?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Kelola Cuti',
        href: '/admin/leave-requests',
    },
];

// Filter states
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedLeaveType = ref(props.filters.leave_type || '');
const selectedDepartment = ref(props.filters.department || '');

// Modal states
const showRejectModal = ref(false);
const showDetailsModal = ref(false);
const showApproveModal = ref(false);
const selectedRequest = ref<LeaveRequest | null>(null);

// Toast
const { toast } = useToast();

// Forms
const rejectForm = useForm({
    admin_notes: '',
});

// Chart data
const monthlyChartData = computed(() => ({
    labels: props.monthlyTrend.map((item) => item.month),
    datasets: [
        {
            label: 'Total Pengajuan',
            data: props.monthlyTrend.map((item) => item.count),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
        },
        {
            label: 'Disetujui',
            data: props.monthlyTrend.map((item) => item.approved),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
        },
    ],
}));

const statusChartData = computed(() => ({
    labels: ['Menunggu', 'Disetujui', 'Ditolak'],
    datasets: [
        {
            data: [props.stats.pending_count, props.stats.approved_count, props.stats.rejected_count],
            backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
            borderWidth: 0,
        },
    ],
}));

// Methods
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase();
};

const getStatusLabel = (status: string) => {
    const labels = {
        pending: 'Menunggu',
        approved: 'Disetujui',
        rejected: 'Ditolak',
    };
    return labels[status as keyof typeof labels] || status;
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    router.get(
        '/admin/leave-requests',
        {
            search: search.value,
            status: selectedStatus.value,
            leave_type: selectedLeaveType.value,
            department: selectedDepartment.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedLeaveType.value = '';
    selectedDepartment.value = '';
    router.get('/admin/leave-requests');
};

const approveRequest = (request: LeaveRequest) => {
    selectedRequest.value = request;
    showApproveModal.value = true;
};

const confirmApprove = () => {
    if (!selectedRequest.value) return;

    router.patch(
        `/admin/leave-requests/${selectedRequest.value.id}/approve`,
        {},
        {
            onSuccess: () => {
                toast({
                    title: 'Berhasil!',
                    description: `Pengajuan cuti ${selectedRequest.value?.user.name} telah disetujui.`,
                    variant: 'success',
                    duration: 3000,
                });
                showApproveModal.value = false;
                selectedRequest.value = null;
            },
            onError: () => {
                toast({
                    title: 'Gagal!',
                    description: 'Terjadi kesalahan saat menyetujui pengajuan.',
                    variant: 'destructive',
                    duration: 4000,
                });
            },
        },
    );
};

const openRejectModal = (request: LeaveRequest) => {
    selectedRequest.value = request;
    rejectForm.reset();
    showRejectModal.value = true;
};

const submitReject = () => {
    if (!selectedRequest.value) return;

    rejectForm.patch(`/admin/leave-requests/${selectedRequest.value.id}/reject`, {
        onSuccess: () => {
            toast({
                title: 'Berhasil!',
                description: `Pengajuan cuti ${selectedRequest.value?.user.name} telah ditolak.`,
                variant: 'warning',
                duration: 3000,
            });
            showRejectModal.value = false;
            selectedRequest.value = null;
        },
        onError: () => {
            toast({
                title: 'Gagal!',
                description: 'Terjadi kesalahan saat menolak pengajuan.',
                variant: 'destructive',
                duration: 4000,
            });
        },
    });
};

const viewDetails = (request: LeaveRequest) => {
    selectedRequest.value = request;
    showDetailsModal.value = true;
};
</script>

<style>
.pagination-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
