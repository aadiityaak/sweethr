<template>
    <Head :title="`Edit Departemen - ${department.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Departemen</h1>
                    <p class="text-muted-foreground">Edit informasi departemen "{{ department.name }}"</p>
                </div>
                <Link
                    :href="`/admin/departments?v=${Date.now()}`"
                    :preserve-state="false"
                    :preserve-scroll="false"
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
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Informasi Departemen</h3>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="name">Nama Departemen *</Label>
                                    <Input id="name" v-model="form.name" placeholder="e.g. Human Resources" :error="form.errors.name" />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="code">Kode Departemen *</Label>
                                    <Input id="code" v-model="form.code" placeholder="e.g. HR" maxlength="10" :error="form.errors.code" />
                                    <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <Label for="description">Deskripsi</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Jelaskan fungsi dan tanggung jawab departemen ini..."
                                    rows="3"
                                    :error="form.errors.description"
                                />
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <div>
                                <Label for="manager_id">Manajer Departemen</Label>
                                <select
                                    id="manager_id"
                                    v-model="form.manager_id"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    :class="form.errors.manager_id ? 'border-red-500' : ''"
                                >
                                    <option value="">Pilih manajer...</option>
                                    <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                        {{ manager.name }} ({{ manager.employee_id }})
                                    </option>
                                </select>
                                <p v-if="form.errors.manager_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.manager_id }}
                                </p>
                            </div>

                            <!-- Positions Management -->
                            <div class="border-t pt-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h4 class="text-md font-semibold">Posisi dalam Departemen</h4>
                                    <Button type="button" variant="outline" size="sm" @click="addPosition">
                                        <Plus class="mr-2 h-4 w-4" />
                                        Tambah Posisi
                                    </Button>
                                </div>

                                <div v-if="form.positions.length === 0" class="py-8 text-center text-muted-foreground">
                                    <Briefcase class="mx-auto mb-2 h-8 w-8 opacity-50" />
                                    <p>Belum ada posisi yang ditambahkan</p>
                                    <p class="text-sm">Klik "Tambah Posisi" untuk menambah posisi baru</p>
                                </div>

                                <div v-else class="space-y-4">
                                    <div v-for="(position, index) in form.positions" :key="index" class="rounded-lg border bg-muted/20 p-4">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <Label :for="`position_title_${index}`">Nama Posisi *</Label>
                                                <Input :id="`position_title_${index}`" v-model="position.title" placeholder="e.g. HR Manager" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label :for="`position_code_${index}`">Kode Posisi *</Label>
                                                <Input :id="`position_code_${index}`" v-model="position.code" placeholder="e.g. HRM001" />
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-4 md:grid-cols-3">
                                            <div class="space-y-2">
                                                <Label :for="`position_level_${index}`">Level *</Label>
                                                <select
                                                    :id="`position_level_${index}`"
                                                    v-model="position.level"
                                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                                >
                                                    <option value="">Pilih level</option>
                                                    <option value="1">1 - Junior</option>
                                                    <option value="2">2 - Staff</option>
                                                    <option value="3">3 - Senior</option>
                                                    <option value="4">4 - Lead</option>
                                                    <option value="5">5 - Manager</option>
                                                    <option value="6">6 - Director</option>
                                                </select>
                                            </div>
                                            <div class="space-y-2">
                                                <Label :for="`position_salary_${index}`">Gaji Pokok</Label>
                                                <Input
                                                    :id="`position_salary_${index}`"
                                                    v-model="position.base_salary"
                                                    type="number"
                                                    placeholder="5000000"
                                                />
                                            </div>
                                            <div class="flex items-end">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    @click="removePosition(index)"
                                                    class="text-red-600 hover:bg-red-50 hover:text-red-700"
                                                >
                                                    <Trash class="mr-2 h-4 w-4" />
                                                    Hapus
                                                </Button>
                                            </div>
                                        </div>

                                        <div class="mt-4 space-y-2">
                                            <Label :for="`position_description_${index}`">Deskripsi</Label>
                                            <Textarea
                                                :id="`position_description_${index}`"
                                                v-model="position.description"
                                                placeholder="Jelaskan tanggung jawab dan tugas posisi ini..."
                                                rows="2"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 border-t pt-6">
                                <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                                <Label for="is_active" class="text-sm">Departemen Aktif</Label>
                            </div>

                            <div class="flex gap-4 pt-4">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan...' : 'Update Departemen' }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.visit(`/admin/departments?v=${Date.now()}`, { preserveState: false, preserveScroll: false })"
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
                        <h3 class="mb-4 text-lg font-semibold">Preview Departemen</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Nama:</span>
                                <span class="font-medium">{{ form.name || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Kode:</span>
                                <span class="font-medium">{{ form.code || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Manajer:</span>
                                <span class="font-medium">{{ selectedManagerName || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Status:</span>
                                <span
                                    :class="{
                                        'text-green-600': form.is_active,
                                        'text-red-600': !form.is_active,
                                    }"
                                    class="font-medium"
                                >
                                    {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Count Warning -->
                    <div v-if="department.employees_count > 0" class="rounded-lg border bg-yellow-50 p-4 dark:bg-yellow-950/20">
                        <h4 class="mb-2 font-medium text-yellow-900 dark:text-yellow-400">
                            <AlertTriangle class="mr-1 inline h-4 w-4" />
                            Perhatian
                        </h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Departemen ini memiliki {{ department.employees_count }} karyawan aktif. Perubahan status atau manajer akan mempengaruhi
                            karyawan tersebut.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Briefcase, Plus, Trash } from 'lucide-vue-next';
import { computed } from 'vue';

interface Department {
    id: number;
    name: string;
    code: string;
    description?: string;
    manager_id?: number;
    is_active: boolean;
    employees_count: number;
    positions: Position[];
}

interface Position {
    id: number;
    title: string;
    code: string;
    level: number;
    base_salary: number;
    description?: string;
}

interface Manager {
    id: number;
    name: string;
    employee_id: string;
}

interface Props {
    department: Department;
    managers: Manager[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Departemen',
        href: '/admin/departments',
    },
    {
        title: 'Edit Departemen',
        href: `/admin/departments/${props.department.id}/edit`,
    },
];

const form = useForm({
    name: props.department.name,
    code: props.department.code,
    description: props.department.description || '',
    manager_id: props.department.manager_id || '',
    is_active: props.department.is_active,
    positions: props.department.positions.map((pos) => ({
        title: pos.title,
        code: pos.code,
        level: pos.level.toString(),
        base_salary: pos.base_salary?.toString() || '',
        description: pos.description || '',
    })),
});

const selectedManagerName = computed(() => {
    if (!form.manager_id) return null;
    const manager = props.managers.find((m) => m.id == form.manager_id);
    return manager ? `${manager.name} (${manager.employee_id})` : null;
});

const addPosition = () => {
    form.positions.push({
        title: '',
        code: '',
        level: '',
        base_salary: '',
        description: '',
    });
};

const removePosition = (index: number) => {
    form.positions.splice(index, 1);
};

const submit = () => {
    form.put(`/admin/departments/${props.department.id}`);
};
</script>
