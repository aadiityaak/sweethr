<template>
    <Head title="Kelola Penugasan Shift" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Kelola Penugasan Shift</h1>
                    <p class="text-muted-foreground">Kelola penugasan shift karyawan</p>
                </div>
                <Link
                    href="/admin/work-shifts"
                    class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali ke Shift
                </Link>
            </div>

            <!-- Filters -->
            <div class="rounded-lg border bg-card p-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <Input
                            v-model="search"
                            placeholder="Cari karyawan..."
                            class="w-full"
                            @input="debouncedSearch"
                        />
                    </div>
                    <div>
                        <select
                            v-model="selectedShift"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            @change="applyFilters"
                        >
                            <option value="">Semua Shift</option>
                            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                {{ shift.name }} ({{ shift.code }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <Button variant="outline" @click="clearFilters" class="w-full">
                            <FilterX class="mr-2 h-4 w-4" />
                            Reset Filter
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="rounded-lg border bg-card">
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Daftar Karyawan</h3>
                    <p class="text-sm text-muted-foreground">{{ employees.total }} karyawan ditemukan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="text-left">
                                <th class="p-4 font-semibold">Karyawan</th>
                                <th class="p-4 font-semibold">Shift Saat Ini</th>
                                <th class="p-4 font-semibold">Tipe Penugasan</th>
                                <th class="p-4 font-semibold">Tanggal Efektif</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="employee in employees.data" :key="employee.id" class="border-b hover:bg-muted/50">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-medium text-primary-foreground">
                                            {{ getInitials(employee.name) }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ employee.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ employee.employee_id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div v-if="getCurrentShift(employee)">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                            {{ getCurrentShift(employee).work_shift.name }}
                                        </span>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            {{ formatTime(getCurrentShift(employee).work_shift.start_time) }} -
                                            {{ formatTime(getCurrentShift(employee).work_shift.end_time) }}
                                        </p>
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground">Belum ada shift</span>
                                </td>
                                <td class="p-4">
                                    <span v-if="getCurrentShift(employee)" class="capitalize text-sm">
                                        {{ getCurrentShift(employee).assignment_type }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground">-</span>
                                </td>
                                <td class="p-4">
                                    <span v-if="getCurrentShift(employee)" class="text-sm">
                                        {{ formatDate(getCurrentShift(employee).effective_date) }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground">-</span>
                                </td>
                                <td class="p-4">
                                    <span
                                        v-if="getCurrentShift(employee)"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': getCurrentShift(employee).is_active,
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !getCurrentShift(employee).is_active
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ getCurrentShift(employee).is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground">-</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Button
                                            size="sm"
                                            @click="openAssignModal(employee)"
                                            variant="ghost"
                                        >
                                            <UserPlus class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            v-if="getCurrentShift(employee)"
                                            size="sm"
                                            @click="unassignEmployee(employee, getCurrentShift(employee))"
                                            variant="ghost"
                                            class="text-red-600 hover:bg-red-50"
                                        >
                                            <UserMinus class="h-4 w-4" />
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
                        Menampilkan {{ employees.from || 0 }}-{{ employees.to || 0 }} dari {{ employees.total }} hasil
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-for="link in employees.links"
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

        <!-- Assign Modal -->
        <Dialog v-model:open="showAssignModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Tugaskan Shift ke {{ selectedEmployee?.name }}</DialogTitle>
                    <DialogDescription>
                        Pilih shift dan atur penugasan untuk karyawan ini
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="assignEmployee" class="space-y-4">
                    <div>
                        <Label for="shift_id">Pilih Shift *</Label>
                        <select
                            id="shift_id"
                            v-model="assignForm.shift_id"
                            required
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="">Pilih shift...</option>
                            <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                {{ shift.name }} ({{ shift.code }}) - {{ formatTime(shift.start_time) }}-{{ formatTime(shift.end_time) }}
                            </option>
                        </select>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="effective_date">Tanggal Efektif *</Label>
                            <DatePicker
                                v-model="assignForm.effective_date"
                                placeholder="Pilih tanggal efektif"
                                required
                            />
                        </div>
                        <div>
                            <Label for="end_date">Tanggal Berakhir</Label>
                            <DatePicker
                                v-model="assignForm.end_date"
                                placeholder="Pilih tanggal berakhir (opsional)"
                            />
                        </div>
                    </div>

                    <div>
                        <Label for="assignment_type">Tipe Penugasan *</Label>
                        <select
                            id="assignment_type"
                            v-model="assignForm.assignment_type"
                            required
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="permanent">Permanen</option>
                            <option value="temporary">Sementara</option>
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                        </select>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAssignModal = false">
                            Batal
                        </Button>
                        <Button type="submit" :disabled="assignForm.processing">
                            {{ assignForm.processing ? 'Menugaskan...' : 'Tugaskan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import {
    ArrowLeft,
    FilterX,
    UserPlus,
    UserMinus,
} from 'lucide-vue-next';
import { debounce } from 'lodash';

interface Employee {
    id: number;
    name: string;
    employee_id: string;
    employee_shifts: EmployeeShift[];
}

interface EmployeeShift {
    id: number;
    work_shift_id: number;
    effective_date: string;
    end_date?: string;
    assignment_type: string;
    is_active: boolean;
    work_shift: {
        id: number;
        name: string;
        code: string;
        start_time: string;
        end_time: string;
    };
}

interface WorkShift {
    id: number;
    name: string;
    code: string;
    start_time: string;
    end_time: string;
    is_active: boolean;
}

interface Props {
    employees: {
        data: Employee[];
        total: number;
        from: number;
        to: number;
        links: any[];
    };
    shifts: WorkShift[];
    filters: {
        search?: string;
        shift?: string;
    };
}

const props = defineProps<Props>();

// Filter states
const search = ref(props.filters.search || '');
const selectedShift = ref(props.filters.shift || '');

// Modal states
const showAssignModal = ref(false);
const selectedEmployee = ref<Employee | null>(null);

// Forms
const assignForm = useForm({
    shift_id: '',
    effective_date: new Date().toISOString().split('T')[0],
    end_date: '',
    assignment_type: 'permanent',
});

// Methods
const formatTime = (time: string) => {
    return time.substring(0, 5); // HH:MM format
};

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

const getCurrentShift = (employee: Employee): EmployeeShift | null => {
    return employee.employee_shifts.find(shift => shift.is_active) || null;
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    router.get('/admin/work-shifts-employees', {
        search: search.value,
        shift: selectedShift.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedShift.value = '';
    router.get('/admin/work-shifts-employees');
};

const openAssignModal = (employee: Employee) => {
    selectedEmployee.value = employee;
    assignForm.reset();
    assignForm.effective_date = new Date().toISOString().split('T')[0];
    showAssignModal.value = true;
};

const assignEmployee = () => {
    if (!selectedEmployee.value) return;

    // Find the shift to get the correct route
    const shift = props.shifts.find(s => s.id == assignForm.shift_id);
    if (!shift) return;

    assignForm.transform(data => ({
        ...data,
        employee_ids: [selectedEmployee.value!.id],
    }));

    assignForm.post(`/admin/work-shifts/${shift.id}/assign`, {
        onSuccess: () => {
            showAssignModal.value = false;
            selectedEmployee.value = null;
        },
    });
};

const unassignEmployee = (employee: Employee, employeeShift: EmployeeShift) => {
    if (confirm(`Yakin ingin menghapus penugasan shift untuk ${employee.name}?`)) {
        router.post(`/admin/work-shifts/${employeeShift.work_shift.id}/unassign`, {
            employee_shift_id: employeeShift.id,
        });
    }
};
</script>

<style>
.pagination-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>