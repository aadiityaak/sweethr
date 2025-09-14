<template>
    <Head title="Admin - Kelola Cuti" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Cuti</h1>
                    <p class="text-muted-foreground">Kelola pengajuan cuti karyawan</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Pengajuan</p>
                            <p class="text-2xl font-bold">{{ stats.total_requests }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/20">
                            <Clock class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Menunggu</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-2xl font-bold">{{ stats.pending_count }}</p>
                                <p class="text-sm text-muted-foreground">({{ stats.pending_percentage }}%)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Disetujui</p>
                            <p class="text-2xl font-bold">{{ stats.approved_count }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/20">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Ditolak</p>
                            <p class="text-2xl font-bold">{{ stats.rejected_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Monthly Trend Chart -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Tren Pengajuan 6 Bulan Terakhir</h3>
                        <p class="text-sm text-muted-foreground">Statistik pengajuan cuti bulanan</p>
                    </div>
                    <div class="h-80">
                        <AttendanceChart
                            :data="monthlyChartData"
                            type="line"
                            :options="{ responsive: true, maintainAspectRatio: false }"
                        />
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Distribusi Status</h3>
                        <p class="text-sm text-muted-foreground">Perbandingan status pengajuan</p>
                    </div>
                    <div class="h-80">
                        <AttendanceChart
                            :data="statusChartData"
                            type="doughnut"
                            :options="{ responsive: true, maintainAspectRatio: false }"
                        />
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card p-6">
                <div class="grid gap-4 md:grid-cols-6">
                    <div class="md:col-span-2">
                        <Input
                            v-model="search"
                            placeholder="Cari karyawan..."
                            class="w-full"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <select
                            v-model="selectedStatus"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
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
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
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
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
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

            <!-- Leave Requests Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Daftar Pengajuan Cuti</h3>
                    <p class="text-sm text-muted-foreground">{{ leaveRequests.total }} pengajuan ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="text-left">
                                <th class="p-4 font-semibold">Karyawan</th>
                                <th class="p-4 font-semibold">Tipe Cuti</th>
                                <th class="p-4 font-semibold">Tanggal</th>
                                <th class="p-4 font-semibold">Durasi</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Diajukan</th>
                                <th class="p-4 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in leaveRequests.data" :key="request.id" class="border-b hover:bg-muted/50">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-medium text-primary-foreground">
                                            {{ getInitials(request.user.name) }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ request.user.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ request.user.employee_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                        {{ request.leave_type.name }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="text-sm">
                                        <p class="font-medium">{{ formatDate(request.start_date) }}</p>
                                        <p class="text-muted-foreground">s/d {{ formatDate(request.end_date) }}</p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-medium">{{ request.duration }} hari</span>
                                </td>
                                <td class="p-4">
                                    <span
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': request.status === 'pending',
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': request.status === 'approved',
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': request.status === 'rejected'
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ getStatusLabel(request.status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-muted-foreground">
                                    {{ formatDate(request.created_at) }}
                                </td>
                                <td class="p-4">
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
                <div class="flex items-center justify-between p-4 border-t">
                    <div class="text-sm text-muted-foreground">
                        Menampilkan {{ leaveRequests.from || 0 }}-{{ leaveRequests.to || 0 }} dari {{ leaveRequests.total }} hasil
                    </div>
                    <div class="flex items-center gap-2">
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

        <!-- Reject Modal -->
        <Dialog v-model:open="showRejectModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Tolak Pengajuan Cuti</DialogTitle>
                    <DialogDescription>
                        Berikan alasan penolakan untuk {{ selectedRequest?.user.name }}
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Alasan Penolakan *</label>
                        <Textarea
                            v-model="rejectForm.admin_notes"
                            placeholder="Jelaskan alasan penolakan..."
                            class="mt-1"
                            rows="4"
                        />
                        <p v-if="rejectForm.errors.admin_notes" class="text-sm text-red-600 mt-1">
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
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import AttendanceChart from '@/components/AttendanceChart.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import {
    Calendar,
    Clock,
    CheckCircle,
    XCircle,
    FilterX,
    Check,
    X,
    Eye,
} from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';

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
const selectedRequest = ref<LeaveRequest | null>(null);

// Forms
const rejectForm = useForm({
    admin_notes: '',
});

// Chart data
const monthlyChartData = computed(() => ({
    labels: props.monthlyTrend.map(item => item.month),
    datasets: [
        {
            label: 'Total Pengajuan',
            data: props.monthlyTrend.map(item => item.count),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
        },
        {
            label: 'Disetujui',
            data: props.monthlyTrend.map(item => item.approved),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
        },
    ],
}));

const statusChartData = computed(() => ({
    labels: ['Menunggu', 'Disetujui', 'Ditolak'],
    datasets: [{
        data: [props.stats.pending_count, props.stats.approved_count, props.stats.rejected_count],
        backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
        borderWidth: 0,
    }],
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
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
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
    router.get('/admin/leave-requests', {
        search: search.value,
        status: selectedStatus.value,
        leave_type: selectedLeaveType.value,
        department: selectedDepartment.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedLeaveType.value = '';
    selectedDepartment.value = '';
    router.get('/admin/leave-requests');
};

const approveRequest = (request: LeaveRequest) => {
    if (confirm(`Setujui pengajuan cuti ${request.user.name}?`)) {
        router.patch(`/admin/leave-requests/${request.id}/approve`, {});
    }
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
            showRejectModal.value = false;
            selectedRequest.value = null;
        },
    });
};

const viewDetails = (request: LeaveRequest) => {
    // Implementation for viewing details - could open a modal or navigate to detail page
    console.log('View details:', request);
};
</script>

<style>
.pagination-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>