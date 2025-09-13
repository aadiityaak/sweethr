<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search, Plus, Calendar, Clock, CheckCircle, XCircle, AlertCircle, User, FileText, Filter } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    employee_id: string;
    profile_photo_url?: string;
}

interface LeaveType {
    id: number;
    name: string;
    max_days_per_year: number;
    requires_approval: boolean;
    color?: string;
}

interface LeaveRequest {
    id: number;
    start_date: string;
    end_date: string;
    total_days: number;
    reason: string;
    status: 'pending' | 'approved' | 'rejected';
    applied_at: string;
    reviewed_at?: string;
    reviewer_notes?: string;
    user: User;
    leave_type: LeaveType;
    reviewer?: User;
}

interface Props {
    leaveRequests: {
        data: LeaveRequest[];
        links: any[];
        meta: any;
    };
    leaveTypes: LeaveType[];
    currentUser: User & { is_admin: boolean };
    filters: {
        search?: string;
        status?: string;
        leave_type?: string;
        user?: string;
    };
    stats: {
        pending_count: number;
        approved_count: number;
        rejected_count: number;
        my_pending_count?: number;
    };
}

const { leaveRequests, leaveTypes, currentUser, filters, stats } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Leave Requests',
        href: '/leave-requests',
    },
];

const searchQuery = ref(filters.search || '');
const selectedStatus = ref(filters.status || '');
const selectedLeaveType = ref(filters.leave_type || '');
const selectedUser = ref(filters.user || '');

const filterRequests = () => {
    router.get('/leave-requests', {
        search: searchQuery.value || undefined,
        status: selectedStatus.value || undefined,
        leave_type: selectedLeaveType.value || undefined,
        user: selectedUser.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const approveForm = useForm({});
const rejectForm = useForm({
    notes: '',
});

const approveRequest = (requestId: number) => {
    approveForm.patch(`/leave-requests/${requestId}/approve`, {
        onSuccess: () => {
            // Handle success
        }
    });
};

const rejectRequest = (requestId: number, notes: string) => {
    rejectForm.notes = notes;
    rejectForm.patch(`/leave-requests/${requestId}/reject`, {
        onSuccess: () => {
            rejectForm.notes = '';
            // Handle success
        }
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved':
            return 'text-green-600 bg-green-50 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-600';
        case 'rejected':
            return 'text-red-600 bg-red-50 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-600';
        case 'pending':
            return 'text-orange-600 bg-orange-50 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-600';
        default:
            return 'text-gray-600 bg-gray-50 border-gray-200 dark:bg-gray-900/30 dark:text-gray-400 dark:border-gray-600';
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

const getInitials = (name: string) => {
    return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
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

const showRejectModal = ref<number | null>(null);
const rejectNotes = ref('');

const openRejectModal = (requestId: number) => {
    showRejectModal.value = requestId;
    rejectNotes.value = '';
};

const closeRejectModal = () => {
    showRejectModal.value = null;
    rejectNotes.value = '';
};

const confirmReject = () => {
    if (showRejectModal.value) {
        rejectRequest(showRejectModal.value, rejectNotes.value);
        closeRejectModal();
    }
};
</script>

<template>
    <Head title="Leave Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Leave Requests</h1>
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ currentUser.is_admin ? 'Manage employee leave requests' : 'View and manage your leave requests' }}
                    </p>
                </div>
                <Link
                    href="/leave-requests/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    <Plus class="h-4 w-4" />
                    New Leave Request
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <AlertCircle class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Pending</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ currentUser.is_admin ? stats.pending_count : stats.my_pending_count }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Approved</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.approved_count }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-red-100 p-2 dark:bg-red-900/30">
                            <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Rejected</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.rejected_count }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Total Requests</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ leaveRequests.meta.total }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            @input="filterRequests"
                            type="text"
                            placeholder="Search requests..."
                            class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <select
                        v-model="selectedStatus"
                        @change="filterRequests"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select
                        v-model="selectedLeaveType"
                        @change="filterRequests"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                    >
                        <option value="">All Leave Types</option>
                        <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                    <div class="flex gap-2">
                        <button
                            @click="filterRequests"
                            class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <Filter class="h-4 w-4" />
                            Apply
                        </button>
                    </div>
                </div>
            </div>

            <!-- Leave Requests List -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-950">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Leave Requests</h2>
                </div>

                <div class="space-y-4 p-6 pt-0">
                    <div
                        v-for="request in leaveRequests.data"
                        :key="request.id"
                        class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <div v-if="request.user.profile_photo_url" class="h-12 w-12 rounded-full">
                                    <img
                                        :src="request.user.profile_photo_url"
                                        :alt="request.user.name"
                                        class="h-12 w-12 rounded-full object-cover"
                                    />
                                </div>
                                <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                        {{ getInitials(request.user.name) }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-medium text-gray-900 dark:text-white">
                                            {{ request.user.name }}
                                        </h3>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ request.user.employee_id }}
                                        </span>
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full border px-2 py-1 text-xs font-medium capitalize"
                                            :class="getStatusColor(request.status)"
                                        >
                                            <component :is="getStatusIcon(request.status)" class="h-3 w-3" />
                                            {{ request.status }}
                                        </span>
                                    </div>
                                    <div class="mt-2 grid gap-2 md:grid-cols-2">
                                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <Calendar class="h-4 w-4" />
                                            <span class="font-medium">{{ request.leave_type.name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <Clock class="h-4 w-4" />
                                            {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                                            ({{ request.total_days }} days)
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Reason:</span> {{ request.reason }}
                                        </p>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Applied on {{ formatDateTime(request.applied_at) }}
                                        <span v-if="request.reviewed_at && request.reviewer">
                                            • Reviewed by {{ request.reviewer.name }} on {{ formatDateTime(request.reviewed_at) }}
                                        </span>
                                    </div>
                                    <div v-if="request.reviewer_notes" class="mt-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            <span class="font-medium">Reviewer Notes:</span> {{ request.reviewer_notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="currentUser.is_admin && request.status === 'pending'" class="flex gap-2">
                                <button
                                    @click="approveRequest(request.id)"
                                    class="rounded-lg bg-green-100 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50"
                                >
                                    <CheckCircle class="h-4 w-4" />
                                </button>
                                <button
                                    @click="openRejectModal(request.id)"
                                    class="rounded-lg bg-red-100 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                >
                                    <XCircle class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="leaveRequests.meta.total > leaveRequests.meta.per_page" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Showing {{ leaveRequests.meta.from }} to {{ leaveRequests.meta.to }} of {{ leaveRequests.meta.total }} results
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in leaveRequests.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1 text-sm border rounded',
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'text-gray-600 border-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-800'
                                ]"
                                :disabled="!link.url"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click="closeRejectModal"
        >
            <div
                class="w-full max-w-md rounded-xl border-0 bg-white p-6 dark:bg-gray-950"
                @click.stop
            >
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reject Leave Request</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Please provide a reason for rejecting this leave request:
                </p>
                <div class="mt-4">
                    <textarea
                        v-model="rejectNotes"
                        rows="4"
                        placeholder="Enter rejection reason..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    />
                </div>
                <div class="mt-4 flex gap-3">
                    <button
                        @click="closeRejectModal"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmReject"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Reject Request
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>