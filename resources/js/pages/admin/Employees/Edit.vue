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
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-semibold mb-4">Informasi Personal</h3>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="name">Nama Lengkap *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="e.g. John Doe"
                                        :error="form.errors.name"
                                    />
                                    <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="employee_id">ID Karyawan *</Label>
                                    <Input
                                        id="employee_id"
                                        v-model="form.employee_id"
                                        placeholder="e.g. EMP001"
                                        :error="form.errors.employee_id"
                                    />
                                    <p v-if="form.errors.employee_id" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.employee_id }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="email">Email *</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        placeholder="e.g. john@example.com"
                                        :error="form.errors.email"
                                    />
                                    <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.email }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="phone">Nomor Telepon</Label>
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        placeholder="e.g. 08123456789"
                                        :error="form.errors.phone"
                                    />
                                    <p v-if="form.errors.phone" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="date_of_birth">Tanggal Lahir</Label>
                                    <DatePicker
                                        v-model="form.date_of_birth"
                                        placeholder="Pilih tanggal lahir"
                                    />
                                    <p v-if="form.errors.date_of_birth" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.date_of_birth }}
                                    </p>
                                </div>
                                <div>
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
                                    <p v-if="form.errors.gender" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.gender }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <Label for="address">Alamat</Label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Alamat lengkap karyawan"
                                    rows="3"
                                    :error="form.errors.address"
                                />
                                <p v-if="form.errors.address" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.address }}
                                </p>
                            </div>

                            <!-- Employment Information -->
                            <div class="pt-6 border-t">
                                <h4 class="text-md font-semibold mb-4">Informasi Pekerjaan</h4>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <Label for="hire_date">Tanggal Masuk *</Label>
                                        <DatePicker
                                            v-model="form.hire_date"
                                            placeholder="Pilih tanggal masuk"
                                        />
                                        <p v-if="form.errors.hire_date" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.hire_date }}
                                        </p>
                                    </div>
                                    <div>
                                        <Label for="employment_status">Status Karyawan</Label>
                                        <select
                                            id="employment_status"
                                            v-model="form.employment_status"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        >
                                            <option value="active">Aktif</option>
                                            <option value="inactive">Tidak Aktif</option>
                                        </select>
                                        <p v-if="form.errors.employment_status" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.employment_status }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
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
                                        <p v-if="form.errors.department_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.department_id }}
                                        </p>
                                    </div>
                                    <div>
                                        <Label for="position_id">Posisi</Label>
                                        <select
                                            id="position_id"
                                            v-model="form.position_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        >
                                            <option value="">Pilih posisi</option>
                                            <option v-for="pos in filteredPositions" :key="pos.id" :value="pos.id">
                                                {{ pos.title }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.position_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.position_id }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-1">
                                    <div>
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
                                        <p v-if="form.errors.manager_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.manager_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact -->
                            <div class="pt-6 border-t">
                                <h4 class="text-md font-semibold mb-4">Kontak Darurat</h4>

                                <div class="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <Label for="emergency_name">Nama Kontak</Label>
                                        <Input
                                            id="emergency_name"
                                            v-model="form.emergency_contact.name"
                                            placeholder="Nama kontak darurat"
                                        />
                                    </div>
                                    <div>
                                        <Label for="emergency_phone">Nomor Telepon</Label>
                                        <Input
                                            id="emergency_phone"
                                            v-model="form.emergency_contact.phone"
                                            placeholder="08123456789"
                                        />
                                    </div>
                                    <div>
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
                            <div class="pt-6 border-t">
                                <h4 class="text-md font-semibold mb-4">Status Admin</h4>
                                <div class="flex items-center space-x-2">
                                    <input
                                        id="is_admin"
                                        type="checkbox"
                                        v-model="form.is_admin"
                                        class="rounded border-input"
                                    />
                                    <Label for="is_admin">Berikan akses admin</Label>
                                </div>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Admin memiliki akses penuh ke semua fitur sistem
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex gap-2 pt-6 border-t">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.visit('/employees')"
                                >
                                    Batal
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <!-- Preview Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-semibold mb-4">Preview Karyawan</h3>
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
                                        'text-red-600': form.employment_status === 'inactive'
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
                        <h3 class="text-lg font-semibold mb-4">Info Karyawan</h3>
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
                                        'text-red-600': employee.employment_status === 'inactive'
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
                        <h4 class="font-medium text-blue-900 dark:text-blue-400 mb-2">Tips:</h4>
                        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <li>• Pastikan email tetap unik dalam sistem</li>
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
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { ArrowLeft } from 'lucide-vue-next';

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
    emergency_contact: {
        name: props.employee.emergency_contact?.name || '',
        phone: props.employee.emergency_contact?.phone || '',
        relationship: props.employee.emergency_contact?.relationship || '',
    },
    is_admin: props.employee.is_admin,
});

const filteredPositions = computed(() => {
    if (!form.department_id) return props.positions;
    return props.positions.filter(pos => pos.department_id == form.department_id);
});

// Watch for department changes and reset position if it's no longer valid
watch(() => form.department_id, (newDepartmentId, oldDepartmentId) => {
    if (newDepartmentId !== oldDepartmentId && form.position_id) {
        // Check if current position belongs to the new department
        const currentPosition = props.positions.find(pos => pos.id == form.position_id);
        if (!currentPosition || currentPosition.department_id != newDepartmentId) {
            form.position_id = '';
        }
    }
});

const selectedDepartmentName = computed(() => {
    if (!form.department_id) return null;
    const dept = props.departments.find(d => d.id == form.department_id);
    return dept ? dept.name : null;
});

const selectedPositionName = computed(() => {
    if (!form.position_id) return null;
    const pos = props.positions.find(p => p.id == form.position_id);
    return pos ? pos.title : null;
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const submit = () => {
    form.put(`/employees/${props.employee.id}`);
};
</script>