<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Clock, MapPin, CheckCircle, XCircle, AlertTriangle, Calendar } from 'lucide-vue-next';

interface OfficeLocation {
    id: number;
    name: string;
}

interface Attendance {
    id: number;
    date: string;
    check_in_time: string | null;
    check_out_time: string | null;
    work_duration: number | null;
    overtime_duration: number;
    status: string;
    office_location: OfficeLocation;
}

interface TodayAttendance {
    id: number;
    check_in_time: string | null;
    check_out_time: string | null;
    status: string;
    work_duration: number | null;
    office_location: OfficeLocation;
}

interface Props {
    attendances?: {
        data: Attendance[];
        links: any[];
        meta: any;
    };
    todayAttendance: TodayAttendance | null;
    filters: {
        month?: string;
        year?: string;
    };
}

const { attendances, todayAttendance, filters } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Attendance',
        href: '/attendance',
    },
];

const formatTime = (time: string | null) => {
    if (!time) return '--:--';
    return time.substring(0, 5);
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return '--';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'present':
            return 'text-green-600 bg-green-50 border-green-200';
        case 'late':
            return 'text-orange-600 bg-orange-50 border-orange-200';
        case 'absent':
            return 'text-red-600 bg-red-50 border-red-200';
        default:
            return 'text-gray-600 bg-gray-50 border-gray-200';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'present':
            return CheckCircle;
        case 'late':
            return AlertTriangle;
        case 'absent':
            return XCircle;
        default:
            return Clock;
    }
};

const filterAttendance = (month?: string, year?: string) => {
    router.get('/attendance', { month, year }, { preserveState: true });
};
</script>

<template>
    <Head title="Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Attendance</h1>
                    <p class="text-gray-600 dark:text-gray-300">Track your daily attendance and work hours</p>
                </div>
                <Link
                    v-if="!todayAttendance?.check_in_time"
                    href="/attendance/check-in"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                >
                    <Clock class="h-4 w-4" />
                    Check In Now
                </Link>
            </div>

            <!-- Today's Status -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Today's Status</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                            <Clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Check In</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ formatTime(todayAttendance?.check_in_time) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-red-100 p-2 dark:bg-red-900/30">
                            <Clock class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Check Out</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ formatTime(todayAttendance?.check_out_time) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <Calendar class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Total Hours</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ formatDuration(todayAttendance?.work_duration) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="todayAttendance" class="mt-4 flex items-center gap-2">
                    <div class="rounded-lg bg-green-100 p-1 dark:bg-green-900/30">
                        <MapPin class="h-4 w-4 text-green-600 dark:text-green-400" />
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        Location: {{ todayAttendance.office_location.name }}
                    </span>
                </div>

                <!-- Check Out Button -->
                <div v-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time" class="mt-4">
                    <button
                        @click="$refs.checkOutModal.showModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        <Clock class="h-4 w-4" />
                        Check Out Now
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-950">
                <div class="flex gap-4">
                    <select
                        :value="filters.month"
                        @change="filterAttendance($event.target.value, filters.year)"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                    >
                        <option value="">All Months</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <select
                        :value="filters.year"
                        @change="filterAttendance(filters.month, $event.target.value)"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                    >
                        <option value="">All Years</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-950">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Attendance History</h2>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Date</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Check In</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Check Out</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Hours</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Location</th>
                            </tr>
                        </thead>
                        <tbody class="space-y-2">
                            <tr
                                v-for="attendance in attendances?.data || []"
                                :key="attendance.id"
                                class="border-b border-gray-100 dark:border-gray-800"
                            >
                                <td class="py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ new Date(attendance.date).toLocaleDateString() }}
                                </td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <component
                                            :is="getStatusIcon(attendance.status)"
                                            class="h-4 w-4"
                                            :class="getStatusColor(attendance.status).split(' ')[0]"
                                        />
                                        <span
                                            class="rounded-full border px-2 py-1 text-xs font-medium capitalize"
                                            :class="getStatusColor(attendance.status)"
                                        >
                                            {{ attendance.status }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ formatTime(attendance.check_in_time) }}
                                </td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ formatTime(attendance.check_out_time) }}
                                </td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ formatDuration(attendance.work_duration) }}
                                </td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ attendance.office_location.name }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="attendances?.meta?.total > attendances?.meta?.per_page" class="mt-4 flex justify-center">
                    <p class="text-sm text-gray-500">
                        Showing {{ attendances.meta.from }} to {{ attendances.meta.to }} of {{ attendances.meta.total }} results
                    </p>
                </div>
            </div>
        </div>

        <!-- Check Out Modal -->
        <dialog ref="checkOutModal" class="rounded-xl border-0 p-6 backdrop:bg-black/50">
            <div class="w-96">
                <h3 class="text-lg font-semibold text-gray-900">Check Out</h3>
                <p class="mt-2 text-sm text-gray-600">Are you sure you want to check out now?</p>
                <div class="mt-4 flex gap-3">
                    <button
                        @click="$refs.checkOutModal.close()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Confirm Check Out
                    </button>
                </div>
            </div>
        </dialog>
    </AppLayout>
</template>