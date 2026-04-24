<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { Calendar, Clock, MapPin, Save, User, X } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
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

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
}

interface AttendanceRecord {
    id: number;
    check_in_time: string | null;
    check_out_time: string | null;
    status: 'present' | 'late' | 'absent' | 'half_day';
    work_duration: number | null;
    late_duration: number | null;
    overtime_duration: number | null;
    date: string;
    user: User;
    office_location: OfficeLocation;
    notes?: string;
}

interface Props {
    attendance: AttendanceRecord;
    officeLocations: OfficeLocation[];
}

const { attendance, officeLocations } = defineProps<Props>();


// Format time for input (remove seconds if present)
const formatTimeForInput = (time: string | null) => {
    if (!time) return '';
    return time.substring(0, 5); // Get HH:MM from HH:MM:SS
};

// Format date for display
const formatDate = (date: string) => {
    const options: Intl.DateTimeFormatOptions = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return new Date(date).toLocaleDateString('id-ID', options);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dasbor', href: '/dashboard' },
    { title: 'Kelola Kehadiran', href: '/admin/attendance' },
    { title: 'Edit Kehadiran', href: '#' },
];
</script>

<template>
    <Head title="Edit Kehadiran" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Kehadiran</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Koreksi data kehadiran karyawan</p>
                </div>
            </div>

            <!-- Employee Info Card -->
            <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-lg font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        {{ attendance.user.name.charAt(0) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ attendance.user.name }}
                        </h3>
                        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-1">
                                <User class="h-3 w-3" />
                                {{ attendance.user.employee_id }}
                            </div>
                            <div v-if="attendance.user.department" class="flex items-center gap-1">
                                <span>{{ attendance.user.department.name }}</span>
                            </div>
                            <div v-if="attendance.user.position" class="flex items-center gap-1">
                                <span>{{ attendance.user.position.title }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <div class="border-b border-gray-200/50 p-6 dark:border-gray-800/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Kehadiran</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Edit informasi kehadiran karyawan</p>
                </div>

                <div class="p-6">
                    <Form :action="`/admin/attendance/${attendance.id}`" method="put" #default="{ errors, processing }" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Date (Readonly) -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <Calendar class="mr-1 inline h-4 w-4" />
                                    Tanggal
                                </label>
                                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    {{ formatDate(attendance.date) }}
                                </div>
                                <input type="hidden" name="date" :value="attendance.date" />
                                <input type="hidden" name="user_id" :value="attendance.user.id" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tanggal tidak dapat diubah</p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Status Kehadiran </label>
                                <select
                                    name="status"
                                    :value="attendance.status"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-400"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.status }"
                                >
                                    <option value="present">Hadir</option>
                                    <option value="late">Terlambat</option>
                                    <option value="absent">Tidak Hadir</option>
                                    <option value="half_day">Setengah Hari</option>
                                </select>
                                <div v-if="errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.status }}
                                </div>
                            </div>

                            <!-- Check In Time -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <Clock class="mr-1 inline h-4 w-4" />
                                    Waktu Check In
                                </label>
                                <input
                                    type="time"
                                    name="check_in_time"
                                    :value="formatTimeForInput(attendance.check_in_time)"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-400"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.check_in_time }"
                                />
                                <div v-if="errors.check_in_time" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.check_in_time }}
                                </div>
                            </div>

                            <!-- Check Out Time -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <Clock class="mr-1 inline h-4 w-4" />
                                    Waktu Check Out
                                </label>
                                <input
                                    type="time"
                                    name="check_out_time"
                                    :value="formatTimeForInput(attendance.check_out_time)"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-400"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.check_out_time }"
                                />
                                <div v-if="errors.check_out_time" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.check_out_time }}
                                </div>
                            </div>

                            <!-- Office Location -->
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <MapPin class="mr-1 inline h-4 w-4" />
                                    Lokasi Kantor
                                </label>
                                <select
                                    name="office_location_id"
                                    :value="attendance.office_location.id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-400"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.office_location_id }"
                                >
                                    <option v-for="location in officeLocations" :key="location.id" :value="location.id">
                                        {{ location.name }} - {{ location.address }}
                                    </option>
                                </select>
                                <div v-if="errors.office_location_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.office_location_id }}
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Catatan </label>
                                <textarea
                                    name="notes"
                                    :value="attendance.notes || ''"
                                    rows="3"
                                    placeholder="Tambahkan catatan (opsional)"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-400"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': errors.notes }"
                                ></textarea>
                                <div v-if="errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center gap-3 border-t border-gray-200/50 pt-6 dark:border-gray-800/50">
                            <button
                                type="submit"
                                :disabled="processing"
                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Save class="mr-2 h-4 w-4" />
                                {{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                            <a
                                href="/admin/attendance"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                <X class="mr-2 h-4 w-4" />
                                Batal
                            </a>
                        </div>
                    </Form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
