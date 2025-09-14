<template>
    <Head title="Tambah Departemen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Tambah Departemen</h1>
                    <p class="text-muted-foreground">Buat departemen baru dalam struktur organisasi</p>
                </div>
                <Link
                    href="/admin/departments"
                    class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
            </div>

            <!-- Create Form -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-semibold mb-4">Informasi Departemen</h3>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="name">Nama Departemen *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="e.g. Human Resources"
                                        :error="form.errors.name"
                                    />
                                    <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="code">Kode Departemen *</Label>
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        placeholder="e.g. HR"
                                        maxlength="10"
                                        :error="form.errors.code"
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600 mt-1">
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
                                <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">
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
                                <p v-if="form.errors.manager_id" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.manager_id }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300"
                                />
                                <Label for="is_active" class="text-sm">Departemen Aktif</Label>
                            </div>

                            <div class="flex gap-4 pt-4">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Departemen' }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.visit('/admin/departments')"
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
                        <h3 class="text-lg font-semibold mb-4">Preview Departemen</h3>
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
                                        'text-red-600': !form.is_active
                                    }"
                                    class="font-medium"
                                >
                                    {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-950/20">
                        <h4 class="font-medium text-blue-900 dark:text-blue-400 mb-2">Tips:</h4>
                        <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                            <li>• Kode departemen sebaiknya singkat dan mudah diingat</li>
                            <li>• Deskripsi membantu karyawan memahami fungsi departemen</li>
                            <li>• Manajer dapat diubah setelah departemen dibuat</li>
                            <li>• Departemen nonaktif tidak akan muncul di pilihan karyawan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { ArrowLeft } from 'lucide-vue-next';

interface Manager {
    id: number;
    name: string;
    employee_id: string;
}

interface Props {
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
        title: 'Tambah Departemen',
        href: '/admin/departments/create',
    },
];

const form = useForm({
    name: '',
    code: '',
    description: '',
    manager_id: '',
    is_active: true,
});

const selectedManagerName = computed(() => {
    if (!form.manager_id) return null;
    const manager = props.managers.find(m => m.id == form.manager_id);
    return manager ? `${manager.name} (${manager.employee_id})` : null;
});

const submit = () => {
    form.post('/admin/departments');
};
</script>