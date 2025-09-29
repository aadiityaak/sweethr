<template>
    <Head :title="`Edit Karyawan - ${employee.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Karyawan</h1>
                    <p class="text-muted-foreground">Edit informasi karyawan "{{ employee.name }}"</p>
                </div>
                <Link
                    href="/employees"
                    class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
            </div>

            <!-- Edit Form -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Personal Information -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Informasi Personal</h3>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name">Nama Lengkap *</Label>
                                    <Input id="name" v-model="form.name" placeholder="e.g. John Doe" :error="form.errors.name" />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="employee_id">ID Karyawan *</Label>
                                    <div class="relative">
                                        <Input
                                            id="employee_id"
                                            v-model="form.employee_id"
                                            placeholder="e.g. EMP001"
                                            :error="form.errors.employee_id || employeeIdValidation.status === 'taken'"
                                            :class="{
                                                'border-green-500 focus:border-green-500': employeeIdValidation.status === 'available',
                                                'border-red-500 focus:border-red-500': employeeIdValidation.status === 'taken',
                                            }"
                                        />
                                        <!-- Icon Status -->
                                        <div
                                            v-if="employeeIdValidation.status !== 'idle'"
                                            class="absolute top-1/2 right-3 -translate-y-1/2 transform"
                                        >
                                            <!-- Loading -->
                                            <div
                                                v-if="employeeIdValidation.status === 'checking'"
                                                class="h-4 w-4 animate-spin rounded-full border-2 border-blue-500 border-t-transparent"
                                            ></div>
                                            <!-- Available -->
                                            <svg
                                                v-else-if="employeeIdValidation.status === 'available'"
                                                class="h-4 w-4 text-green-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <!-- Taken -->
                                            <svg
                                                v-else-if="employeeIdValidation.status === 'taken'"
                                                class="h-4 w-4 text-red-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Validation Messages -->
                                    <p v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.employee_id }}
                                    </p>
                                    <p
                                        v-else-if="employeeIdValidation.message"
                                        :class="{
                                            'text-green-600': employeeIdValidation.status === 'available',
                                            'text-red-600': employeeIdValidation.status === 'taken',
                                            'text-blue-600': employeeIdValidation.status === 'checking',
                                        }"
                                        class="mt-1 text-sm"
                                    >
                                        {{ employeeIdValidation.message }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="email">Email *</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        placeholder="e.g. john@example.com"
                                        :error="form.errors.email"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="phone">Nomor Telepon</Label>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        v-model="form.phone"
                                        placeholder="e.g. 08123456789"
                                        :error="form.errors.phone"
                                    />
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>


                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="date_of_birth">Tanggal Lahir</Label>
                                    <DatePicker
                                        v-model="form.date_of_birth"
                                        placeholder="Pilih tanggal lahir"
                                        :birth-date="true"
                                        :default-year="1993"
                                    />
                                    <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.date_of_birth }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="gender">Jenis Kelamin</Label>
                                    <select
                                        id="gender"
                                        v-model="form.gender"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    >
                                        <option value="">Pilih jenis kelamin</option>
                                        <option value="male">Laki-laki</option>
                                        <option value="female">Perempuan</option>
                                    </select>
                                    <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.gender }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Alamat</Label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Alamat lengkap karyawan"
                                    rows="3"
                                    :error="form.errors.address"
                                />
                                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.address }}
                                </p>
                            </div>

                            <!-- Employment Information -->
                            <div class="border-t pt-6">
                                <h4 class="text-md mb-6 font-semibold">Informasi Pekerjaan</h4>

                                <div class="space-y-6">
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="hire_date">Tanggal Masuk *</Label>
                                            <DatePicker v-model="form.hire_date" placeholder="Pilih tanggal masuk" />
                                            <p v-if="form.errors.hire_date" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.hire_date }}
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="employment_status">Status Karyawan</Label>
                                            <select
                                                id="employment_status"
                                                v-model="form.employment_status"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                            >
                                                <option value="active">Aktif</option>
                                                <option value="inactive">Tidak Aktif</option>
                                            </select>
                                            <p v-if="form.errors.employment_status" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.employment_status }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="department_id">Departemen</Label>
                                            <select
                                                id="department_id"
                                                v-model="form.department_id"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                            >
                                                <option value="">Pilih departemen</option>
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                                    {{ dept.name }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.department_id" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.department_id }}
                                            </p>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="position_id">Posisi (Opsional)</Label>
                                            <select
                                                id="position_id"
                                                v-model="form.position_id"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                            >
                                                <option value="">Pilih posisi (opsional)</option>
                                                <option v-for="pos in filteredPositions" :key="pos.id" :value="pos.id">
                                                    {{ pos.title }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.position_id" class="mt-1 text-sm text-red-600">
                                                {{ form.errors.position_id }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="manager_id">Manajer</Label>
                                        <select
                                            id="manager_id"
                                            v-model="form.manager_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        >
                                            <option value="">Tidak ada manajer</option>
                                            <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                                {{ manager.name }} ({{ manager.employee_id }})
                                            </option>
                                        </select>
                                        <p v-if="form.errors.manager_id" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.manager_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Reset -->
                            <div class="border-t pt-6">
                                <h4 class="text-md mb-4 font-semibold">Reset Password</h4>
                                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                                    Kosongkan field password jika tidak ingin mengubah password
                                </p>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="new_password">Password Baru</Label>
                                        <Input
                                            id="new_password"
                                            v-model="form.password"
                                            type="password"
                                            placeholder="Kosongkan jika tidak ingin mengubah"
                                            :error="form.errors.password"
                                        />
                                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.password }}
                                        </p>
                                        <p class="mt-1 text-xs text-muted-foreground">
                                            Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="new_password_confirmation">Konfirmasi Password Baru</Label>
                                        <Input
                                            id="new_password_confirmation"
                                            v-model="form.password_confirmation"
                                            type="password"
                                            placeholder="Konfirmasi password baru"
                                            :error="form.errors.password_confirmation"
                                        />
                                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.password_confirmation }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact -->
                            <div class="border-t pt-6">
                                <h4 class="text-md mb-4 font-semibold">Kontak Darurat</h4>

                                <div class="grid gap-6 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label for="emergency_name">Nama Kontak</Label>
                                        <Input id="emergency_name" v-model="form.emergency_contact.name" placeholder="Nama kontak darurat" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="emergency_phone">Nomor Telepon</Label>
                                        <Input id="emergency_phone" type="tel" v-model="form.emergency_contact.phone" placeholder="08123456789" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="emergency_relationship">Hubungan</Label>
                                        <select
                                            id="emergency_relationship"
                                            v-model="form.emergency_contact.relationship"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        >
                                            <option value="">Pilih hubungan</option>
                                            <option value="spouse">Pasangan</option>
                                            <option value="parent">Orang Tua</option>
                                            <option value="sibling">Saudara</option>
                                            <option value="child">Anak</option>
                                            <option value="friend">Teman</option>
                                            <option value="other">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Status -->
                            <div class="border-t pt-6">
                                <h4 class="text-md mb-4 font-semibold">Status Admin</h4>
                                <div class="flex items-center space-x-2">
                                    <input id="is_admin" type="checkbox" v-model="form.is_admin" class="rounded border-input" />
                                    <Label for="is_admin">Berikan akses admin</Label>
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">Admin memiliki akses penuh ke semua fitur sistem</p>
                            </div>

                            <!-- Attendance Settings -->
                            <div class="border-t pt-6">
                                <h4 class="text-md mb-4 font-semibold">Pengaturan Absensi</h4>
                                <div class="flex items-center space-x-2">
                                    <input
                                        id="allow_outside_radius"
                                        type="checkbox"
                                        v-model="form.allow_outside_radius"
                                        class="rounded border-input"
                                    />
                                    <Label for="allow_outside_radius">Ijinkan absen di luar radius kantor</Label>
                                </div>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Karyawan dapat melakukan check-in/out dari lokasi manapun (untuk tugas luar, dll)
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex gap-2 border-t pt-6">
                                <Button
                                    type="submit"
                                    :disabled="
                                        form.processing || employeeIdValidation.status === 'taken' || employeeIdValidation.status === 'checking'
                                    "
                                    :class="{
                                        'cursor-not-allowed opacity-50': employeeIdValidation.status === 'taken',
                                    }"
                                >
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </Button>
                                <Button type="button" variant="outline" @click="router.visit('/employees')"> Batal </Button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <!-- Preview Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Preview Karyawan</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Nama:</span>
                                <span class="font-medium">{{ form.name || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">ID:</span>
                                <span class="font-medium">{{ form.employee_id || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Email:</span>
                                <span class="font-medium">{{ form.email || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Departemen:</span>
                                <span class="font-medium">{{ selectedDepartmentName || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Posisi:</span>
                                <span class="font-medium">{{ selectedPositionName || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Status:</span>
                                <span
                                    :class="{
                                        'text-green-600': form.employment_status === 'active',
                                        'text-red-600': form.employment_status === 'inactive',
                                    }"
                                    class="font-medium"
                                >
                                    {{ form.employment_status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Info -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Info Karyawan</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Tanggal Masuk:</span>
                                <span class="font-medium">{{ formatDate(employee.hire_date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Status Saat Ini:</span>
                                <span
                                    :class="{
                                        'text-green-600': employee.employment_status === 'active',
                                        'text-red-600': employee.employment_status === 'inactive',
                                    }"
                                    class="font-medium"
                                >
                                    {{ employee.employment_status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Terakhir Update:</span>
                                <span class="font-medium">{{ formatDate(employee.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-950/20">
                        <h4 class="mb-2 font-medium text-blue-900 dark:text-blue-400">Tips:</h4>
                        <ul class="space-y-1 text-sm text-blue-700 dark:text-blue-300">
                            <li>• ID karyawan akan divalidasi secara real-time</li>
                            <li>• Email harus tetap unik dalam sistem</li>
                            <li>• Perubahan departemen akan mempengaruhi akses karyawan</li>
                            <li>• Status admin memberikan akses penuh ke sistem</li>
                            <li>• Kontak darurat penting untuk keadaan mendesak</li>
                            <li>• Data yang diubah akan tersimpan secara otomatis</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ArrowLeft } from 'lucide-vue-next';
import { computed, watch, ref, onMounted } from 'vue';

interface Employee {
    id: number;
    name: string;
    email: string;
    employee_id: string;
    phone?: string;
    date_of_birth?: string;
    gender?: string;
    address?: string;
    hire_date: string;
    department_id?: number;
    position_id?: number;
    manager_id?: number;
    employment_status: string;
    emergency_contact?: {
        name?: string;
        phone?: string;
        relationship?: string;
    };
    is_admin: boolean;
    allow_outside_radius: boolean;
    updated_at: string;
}

interface Department {
    id: number;
    name: string;
    code: string;
}

interface Position {
    id: number;
    title: string;
    department_id: number;
    level: number;
}

interface Manager {
    id: number;
    name: string;
    employee_id: string;
}

interface Props {
    employee: Employee;
    departments: Department[];
    positions: Position[];
    managers: Manager[];
}

const props = defineProps<Props>();

const { toast } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Karyawan',
        href: '/employees',
    },
    {
        title: 'Edit Karyawan',
        href: `/employees/${props.employee.id}/edit`,
    },
];

const form = useForm({
    name: props.employee.name,
    email: props.employee.email,
    employee_id: props.employee.employee_id,
    phone: props.employee.phone || '',
    date_of_birth: props.employee.date_of_birth || '',
    gender: props.employee.gender || '',
    address: props.employee.address || '',
    hire_date: props.employee.hire_date,
    department_id: props.employee.department_id || '',
    position_id: props.employee.position_id || '',
    manager_id: props.employee.manager_id || '',
    employment_status: props.employee.employment_status,
    password: '',
    password_confirmation: '',
    emergency_contact: {
        name: props.employee.emergency_contact?.name || '',
        phone: props.employee.emergency_contact?.phone || '',
        relationship: props.employee.emergency_contact?.relationship || '',
    },
    is_admin: props.employee.is_admin,
    allow_outside_radius: props.employee.allow_outside_radius,
});

// State untuk validasi employee_id
const employeeIdValidation = ref<{
    status: 'idle' | 'checking' | 'available' | 'taken';
    message: string;
}>({
    status: 'idle',
    message: '',
});

// Fungsi untuk cek employee_id ke API (exclude current employee)
const checkEmployeeId = async (employeeId: string) => {
    if (!employeeId || employeeId.length < 2) {
        employeeIdValidation.value = {
            status: 'idle',
            message: '',
        };
        return;
    }

    // Skip validation if it's the same as original employee_id
    if (employeeId === props.employee.employee_id) {
        employeeIdValidation.value = {
            status: 'available',
            message: 'ID karyawan saat ini',
        };
        return;
    }

    employeeIdValidation.value = {
        status: 'checking',
        message: 'Mengecek ketersediaan...',
    };

    try {
        const response = await fetch('/api/employees/validate/employee-id', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ employee_id: employeeId }),
        });

        const data = await response.json();

        employeeIdValidation.value = {
            status: data.available ? 'available' : 'taken',
            message: data.message,
        };
    } catch (error) {
        employeeIdValidation.value = {
            status: 'idle',
            message: 'Gagal mengecek ID karyawan',
        };
    }
};

// Debounced function untuk mencegah terlalu banyak request
const debouncedCheckEmployeeId = useDebounceFn(checkEmployeeId, 500);

const filteredPositions = computed(() => {
    if (!form.department_id) return props.positions;
    return props.positions.filter((pos) => pos.department_id == form.department_id);
});

// Watch for department changes and reset position if it's no longer valid
watch(
    () => form.department_id,
    (newDepartmentId, oldDepartmentId) => {
        if (newDepartmentId !== oldDepartmentId && form.position_id) {
            // Check if current position belongs to the new department
            const currentPosition = props.positions.find((pos) => pos.id == form.position_id);
            if (!currentPosition || currentPosition.department_id != newDepartmentId) {
                form.position_id = '';
            }
        }
    },
);

// Watch for employee_id changes and validate
watch(
    () => form.employee_id,
    (newEmployeeId) => {
        debouncedCheckEmployeeId(newEmployeeId);
    },
);

// Set initial validation status
onMounted(() => {
    employeeIdValidation.value = {
        status: 'available',
        message: 'ID karyawan saat ini',
    };
});

const selectedDepartmentName = computed(() => {
    if (!form.department_id) return null;
    const dept = props.departments.find((d) => d.id == form.department_id);
    return dept ? dept.name : null;
});

const selectedPositionName = computed(() => {
    if (!form.position_id) return null;
    const pos = props.positions.find((p) => p.id == form.position_id);
    return pos ? pos.title : null;
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

// Validasi field wajib
const validateRequiredFields = () => {
    const requiredFields = [
        { field: 'name', label: 'Nama Lengkap' },
        { field: 'email', label: 'Email' },
        { field: 'employee_id', label: 'ID Karyawan' },
        { field: 'hire_date', label: 'Tanggal Masuk' },
    ];

    const missingFields = requiredFields.filter(({ field }) => {
        const value = form[field as keyof typeof form];
        return !value || String(value).trim() === '';
    });

    return missingFields;
};

const submit = () => {
    // Cek field wajib yang kosong
    const missingFields = validateRequiredFields();
    if (missingFields.length > 0) {
        const fieldList = missingFields.map((f) => f.label).join(', ');

        toast({
            title: 'Field Wajib Belum Lengkap',
            description: `Mohon lengkapi field berikut: ${fieldList}`,
            variant: 'destructive',
            duration: 5000,
        });
        return;
    }

    // Validasi password jika diisi
    if (form.password || form.password_confirmation) {
        // Cek jika password tidak sama
        if (form.password !== form.password_confirmation) {
            toast({
                title: 'Password Tidak Sama',
                description: 'Password baru dan konfirmasi password harus sama.',
                variant: 'destructive',
                duration: 5000,
            });
            return;
        }

        // Cek panjang password minimum
        if (form.password.length < 8) {
            toast({
                title: 'Password Terlalu Pendek',
                description: 'Password baru minimal harus 8 karakter.',
                variant: 'destructive',
                duration: 5000,
            });
            return;
        }
    }

    // Cek jika employee_id sudah digunakan, jangan submit
    if (employeeIdValidation.value.status === 'taken') {
        toast({
            title: 'ID Karyawan Sudah Digunakan',
            description: 'Silakan gunakan ID karyawan yang lain.',
            variant: 'destructive',
            duration: 5000,
        });
        return;
    }

    // Cek jika masih dalam proses validasi
    if (employeeIdValidation.value.status === 'checking') {
        toast({
            title: 'Mohon Tunggu',
            description: 'Proses validasi ID karyawan masih berlangsung.',
            variant: 'warning',
            duration: 3000,
        });
        return;
    }

    form.put(`/employees/${props.employee.id}`, {
        onSuccess: () => {
            toast({
                title: 'Data Karyawan Berhasil Diperbarui',
                description: 'Perubahan data karyawan telah berhasil disimpan.',
                variant: 'success',
                duration: 4000,
            });
        },
        onError: (errors) => {
            // Toast untuk error dari server
            const errorMessages = Object.values(errors).flat();
            if (errorMessages.length > 0) {
                toast({
                    title: 'Gagal Menyimpan Data',
                    description: (errorMessages[0] as string) || 'Terjadi kesalahan saat menyimpan data karyawan.',
                    variant: 'destructive',
                    duration: 5000,
                });
            }
        },
    });
};
</script>
