<template>
    <Head :title="`Shift ${workShift.name}`" />

    <AppLayout>
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detail Shift Kerja</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Informasi lengkap shift "{{ workShift.name }}"</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="`/admin/work-shifts/${workShift.id}/edit`"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                        >
                            <Edit class="h-4 w-4" />
                            Edit Shift
                        </Link>
                        <Link
                            href="/admin/work-shifts"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Kembali
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Shift Info Cards -->
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Shift</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nama Shift</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ workShift.name }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Kode</label>
                                        <p class="font-mono text-lg font-semibold text-gray-900 dark:text-white">{{ workShift.code }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</label>
                                        <div class="mt-2 flex items-center gap-2">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': workShift.is_active,
                                                    'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !workShift.is_active,
                                                }"
                                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                            >
                                                {{ workShift.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            <span
                                                v-if="workShift.is_night_shift"
                                                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/20 dark:text-amber-400"
                                            >
                                                <Moon class="h-3 w-3" />
                                                Shift Malam
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Waktu Kerja</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ formatTime(workShift.start_time) }} - {{ formatTime(workShift.end_time) }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Durasi Kerja</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatWorkHours(workShift.work_hours) }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Istirahat</label>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ workShift.break_duration }} menit</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Hari Kerja</label>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="day in workShift.workdays"
                                        :key="day"
                                        class="inline-flex items-center rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-semibold text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                    >
                                        {{ getDayName(day) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Multiplier Lembur</label>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ workShift.overtime_multiplier }}x</p>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Assignments -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Karyawan Ter-assign
                                    <span class="ml-2 text-gray-500 dark:text-gray-400">({{ assignedEmployees.length }})</span>
                                </h3>
                                <Button @click="showAssignModal = true" size="sm" class="inline-flex items-center gap-2">
                                    <UserPlus class="h-4 w-4" />
                                    Assign Karyawan
                                </Button>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="assignedEmployees.length > 0" class="space-y-3">
                                <div
                                    v-for="assignment in assignedEmployees"
                                    :key="assignment.id"
                                    class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4 transition-colors hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-750"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white"
                                        >
                                            {{ getInitials(assignment.user.name) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ assignment.user.name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ assignment.user.employee_id }}</p>
                                            <div class="mt-1 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-500">
                                                <span class="capitalize">{{ formatAssignmentType(assignment.assignment_type) }}</span>
                                                <span>•</span>
                                                <span>Efektif: {{ formatDate(assignment.effective_date) }}</span>
                                                <span v-if="assignment.end_date">
                                                    •
                                                    <span>Berakhir: {{ formatDate(assignment.end_date) }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="unassignEmployee(assignment)"
                                        class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/20"
                                    >
                                        <UserMinus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>

                            <div v-else class="py-12 text-center text-gray-500 dark:text-gray-400">
                                <Users class="mx-auto mb-4 h-16 w-16 opacity-30" />
                                <p class="text-sm">Belum ada karyawan yang ditugaskan ke shift ini</p>
                                <Button @click="showAssignModal = true" size="sm" variant="outline" class="mt-4">
                                    <UserPlus class="mr-2 h-4 w-4" />
                                    Assign Karyawan Sekarang
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Aksi Cepat</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-2">
                                <Link
                                    :href="`/admin/work-shifts/${workShift.id}/edit`"
                                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    <Edit class="h-4 w-4" />
                                    Edit Shift
                                </Link>
                                <button
                                    @click="toggleShiftStatus"
                                    class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-left text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    <Power class="h-4 w-4" />
                                    {{ workShift.is_active ? 'Nonaktifkan' : 'Aktifkan' }} Shift
                                </button>
                                <button
                                    @click="deleteShift"
                                    :disabled="assignedEmployees.length > 0"
                                    class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-left text-sm font-medium text-red-600 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-950/20"
                                >
                                    <Trash class="h-4 w-4" />
                                    Hapus Shift
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Karyawan:</span>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ assignedEmployees.length }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Hari Kerja:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ workShift.workdays.length }} hari/minggu</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Jam per Hari:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatWorkHours(workShift.work_hours) }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Jam per Minggu:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatWorkHours(workShift.work_hours * workShift.workdays.length) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Employee Modal -->
        <Dialog v-model:open="showAssignModal">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Assign Karyawan ke Shift</DialogTitle>
                    <DialogDescription> Pilih karyawan untuk ditugaskan ke shift "{{ workShift.name }}" </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitAssignment" class="space-y-6">
                    <!-- Employee Selection -->
                    <div class="space-y-2">
                        <Label>Pilih Karyawan *</Label>
                        <div class="custom-scrollbar max-h-60 space-y-2 overflow-y-auto rounded-md border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                            <div v-if="availableEmployees.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada karyawan yang tersedia untuk ditugaskan
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="employee in availableEmployees" :key="employee.id" class="flex items-center gap-3">
                                    <input
                                        :id="`emp-${employee.id}`"
                                        type="checkbox"
                                        :value="employee.id"
                                        v-model="assignForm.employee_ids"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                    />
                                    <label
                                        :for="`emp-${employee.id}`"
                                        class="flex flex-1 cursor-pointer items-center gap-3 rounded-md p-2 transition-colors hover:bg-white dark:hover:bg-gray-700"
                                    >
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white">
                                            {{ getInitials(employee.name) }}
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ employee.name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ employee.employee_id }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <p v-if="assignForm.errors.employee_ids" class="text-sm text-red-600 dark:text-red-400">
                            {{ assignForm.errors.employee_ids }}
                        </p>
                        <p v-if="assignForm.employee_ids.length > 0" class="text-sm font-medium text-blue-600 dark:text-blue-400">
                            {{ assignForm.employee_ids.length }} karyawan terpilih
                        </p>
                    </div>

                    <!-- Assignment Details -->
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="effective_date">Tanggal Efektif *</Label>
                            <DatePicker v-model="assignForm.effective_date" :min="today" placeholder="Pilih tanggal efektif" />
                            <p v-if="assignForm.errors.effective_date" class="text-sm text-red-600 dark:text-red-400">
                                {{ assignForm.errors.effective_date }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="end_date">Tanggal Berakhir (Opsional)</Label>
                            <DatePicker v-model="assignForm.end_date" :min="assignForm.effective_date || today" placeholder="Pilih tanggal berakhir" />
                            <p v-if="assignForm.errors.end_date" class="text-sm text-red-600 dark:text-red-400">
                                {{ assignForm.errors.end_date }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="assignment_type">Tipe Penugasan *</Label>
                        <select
                            id="assignment_type"
                            v-model="assignForm.assignment_type"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="permanent">Permanen</option>
                            <option value="temporary">Sementara</option>
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                        </select>
                        <p v-if="assignForm.errors.assignment_type" class="text-sm text-red-600">
                            {{ assignForm.errors.assignment_type }}
                        </p>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAssignModal = false" :disabled="assignForm.processing">Batal</Button>
                        <Button type="submit" :disabled="assignForm.processing || assignForm.employee_ids.length === 0">
                            {{ assignForm.processing ? 'Menyimpan...' : 'Assign Karyawan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Moon, Power, Trash, UserMinus, UserPlus, Users } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface WorkShift {
    id: number;
    name: string;
    code: string;
    start_time: string;
    end_time: string;
    work_hours: number;
    break_duration: number;
    overtime_multiplier: number;
    workdays: number[];
    is_night_shift: boolean;
    is_active: boolean;
    employee_shifts_count: number;
}

interface EmployeeAssignment {
    id: number;
    assignment_type: string;
    effective_date: string;
    end_date: string | null;
    user: {
        id: number;
        name: string;
        employee_id: string;
    };
}

interface Employee {
    id: number;
    name: string;
    employee_id: string;
}

interface Props {
    workShift: WorkShift;
    assignedEmployees: EmployeeAssignment[];
    availableEmployees: Employee[];
}

const props = defineProps<Props>();

const showAssignModal = ref(false);

// Get today's date in YYYY-MM-DD format
const today = new Date().toISOString().split('T')[0];

const assignForm = useForm({
    employee_ids: [] as number[],
    effective_date: today,
    end_date: '',
    assignment_type: 'permanent',
});

// Reset form when modal closes
watch(showAssignModal, (isOpen) => {
    if (!isOpen) {
        assignForm.reset();
        assignForm.clearErrors();
    }
});

const daysOfWeek = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

const formatTime = (time: string) => {
    return time.substring(0, 5); // HH:MM format
};

const formatWorkHours = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}j ${mins}m`;
};

const getDayName = (dayNumber: number) => {
    return daysOfWeek[dayNumber];
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase();
};

const toggleShiftStatus = () => {
    const action = props.workShift.is_active ? 'nonaktifkan' : 'aktifkan';
    if (confirm(`Yakin ingin ${action} shift "${props.workShift.name}"?`)) {
        router.put(`/admin/work-shifts/${props.workShift.id}`, {
            ...props.workShift,
            is_active: !props.workShift.is_active,
        });
    }
};

const unassignEmployee = (assignment: EmployeeAssignment) => {
    if (confirm(`Yakin ingin menghapus penugasan ${assignment.user.name} dari shift ini?`)) {
        router.post(
            `/admin/work-shifts/${props.workShift.id}/unassign`,
            {
                employee_shift_id: assignment.id,
            },
            {
                preserveScroll: true,
            },
        );
    }
};

const deleteShift = () => {
    if (confirm(`Yakin ingin menghapus shift "${props.workShift.name}"? Aksi ini tidak dapat dibatalkan.`)) {
        router.delete(`/admin/work-shifts/${props.workShift.id}`);
    }
};

const submitAssignment = () => {
    assignForm.post(`/admin/work-shifts/${props.workShift.id}/assign`, {
        preserveScroll: true,
        onSuccess: () => {
            showAssignModal.value = false;
            assignForm.reset();
        },
    });
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatAssignmentType = (type: string) => {
    const types: Record<string, string> = {
        permanent: 'Permanen',
        temporary: 'Sementara',
        weekly: 'Mingguan',
        monthly: 'Bulanan',
    };
    return types[type] || type;
};
</script>

<style scoped>
/* Custom scrollbar styling similar to sidebar */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(107, 114, 128, 0.7);
}

.dark .custom-scrollbar {
    scrollbar-color: rgba(75, 85, 99, 0.5) transparent;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(75, 85, 99, 0.5);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(55, 65, 81, 0.7);
}
</style>
