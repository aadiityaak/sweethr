<template>
    <Head :title="`Shift ${workShift.name}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Detail Shift Kerja</h1>
                    <p class="text-muted-foreground">Informasi lengkap shift "{{ workShift.name }}"</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="`/admin/work-shifts/${workShift.id}/edit`"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <Edit class="h-4 w-4" />
                        Edit Shift
                    </Link>
                    <Link
                        href="/admin/work-shifts"
                        class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Shift Info Cards -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Informasi Shift</h3>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Nama Shift</label>
                                    <p class="text-lg font-semibold">{{ workShift.name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Kode</label>
                                    <p class="font-mono text-lg">{{ workShift.code }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Status</label>
                                    <div class="mt-1">
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': workShift.is_active,
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !workShift.is_active,
                                            }"
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        >
                                            {{ workShift.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        <span
                                            v-if="workShift.is_night_shift"
                                            class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/20 dark:text-amber-400"
                                        >
                                            <Moon class="mr-1 h-3 w-3" />
                                            Shift Malam
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Waktu Kerja</label>
                                    <p class="text-lg font-semibold">{{ formatTime(workShift.start_time) }} - {{ formatTime(workShift.end_time) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Durasi Kerja</label>
                                    <p class="text-lg font-semibold">{{ formatWorkHours(workShift.work_hours) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Istirahat</label>
                                    <p class="text-lg font-semibold">{{ workShift.break_duration }} menit</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="text-sm font-medium text-muted-foreground">Hari Kerja</label>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span
                                    v-for="day in workShift.workdays"
                                    :key="day"
                                    class="inline-flex items-center rounded-full bg-primary px-3 py-1 text-xs font-medium text-primary-foreground"
                                >
                                    {{ getDayName(day) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="text-sm font-medium text-muted-foreground">Multiplier Lembur</label>
                            <p class="text-lg font-semibold">{{ workShift.overtime_multiplier }}x</p>
                        </div>
                    </div>

                    <!-- Employee Assignments -->
                    <div class="rounded-lg border bg-card p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Karyawan Ter-assign ({{ assignedEmployees.length }})</h3>
                            <Button @click="showAssignModal = true" size="sm" class="inline-flex items-center gap-2">
                                <UserPlus class="h-4 w-4" />
                                Assign Karyawan
                            </Button>
                        </div>

                        <div v-if="assignedEmployees.length > 0" class="space-y-3">
                            <div
                                v-for="assignment in assignedEmployees"
                                :key="assignment.id"
                                class="flex items-center justify-between rounded-md border p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-medium text-primary-foreground"
                                    >
                                        {{ getInitials(assignment.user.name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ assignment.user.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ assignment.user.employee_id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">
                                        {{ assignment.assignment_type }}
                                    </span>
                                    <Button size="sm" variant="ghost" @click="unassignEmployee(assignment)" class="text-red-600 hover:bg-red-50">
                                        <UserMinus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-8 text-center text-muted-foreground">
                            <Users class="mx-auto mb-4 h-12 w-12 opacity-50" />
                            <p>Belum ada karyawan yang ditugaskan ke shift ini</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Aksi Cepat</h3>
                        <div class="space-y-2">
                            <Link
                                :href="`/admin/work-shifts/${workShift.id}/edit`"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted"
                            >
                                <Edit class="h-4 w-4" />
                                Edit Shift
                            </Link>
                            <button @click="toggleShiftStatus" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted">
                                <Power class="h-4 w-4" />
                                {{ workShift.is_active ? 'Nonaktifkan' : 'Aktifkan' }} Shift
                            </button>
                            <button
                                @click="deleteShift"
                                :disabled="assignedEmployees.length > 0"
                                class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <Trash class="h-4 w-4" />
                                Hapus Shift
                            </button>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Statistik</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Total Karyawan:</span>
                                <span class="font-medium">{{ assignedEmployees.length }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Hari Kerja:</span>
                                <span class="font-medium">{{ workShift.workdays.length }} hari/minggu</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Jam per Hari:</span>
                                <span class="font-medium">{{ formatWorkHours(workShift.work_hours) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-muted-foreground">Jam per Minggu:</span>
                                <span class="font-medium">
                                    {{ formatWorkHours(workShift.work_hours * workShift.workdays.length) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Employee Modal -->
        <Dialog v-model:open="showAssignModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Assign Karyawan ke Shift</DialogTitle>
                    <DialogDescription> Pilih karyawan untuk ditugaskan ke shift "{{ workShift.name }}" </DialogDescription>
                </DialogHeader>

                <!-- Modal content would go here -->
                <p class="text-sm text-muted-foreground">Fitur ini akan dikembangkan lebih lanjut untuk memilih dan menugaskan karyawan.</p>

                <DialogFooter>
                    <Button variant="outline" @click="showAssignModal = false">Batal</Button>
                    <Button @click="showAssignModal = false">Assign</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Moon, Power, Trash, UserMinus, UserPlus, Users } from 'lucide-vue-next';
import { ref } from 'vue';

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
    user: {
        id: number;
        name: string;
        employee_id: string;
    };
}

interface Props {
    workShift: WorkShift;
    assignedEmployees: EmployeeAssignment[];
}

const props = defineProps<Props>();

const showAssignModal = ref(false);

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
        router.post(`/admin/work-shifts/${props.workShift.id}/unassign`, {
            employee_shift_id: assignment.id,
        });
    }
};

const deleteShift = () => {
    if (confirm(`Yakin ingin menghapus shift "${props.workShift.name}"? Aksi ini tidak dapat dibatalkan.`)) {
        router.delete(`/admin/work-shifts/${props.workShift.id}`);
    }
};
</script>
