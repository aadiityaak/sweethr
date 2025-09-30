<template>
    <Head title="Tambah Shift Kerja" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Tambah Shift Kerja</h1>
                    <p class="text-muted-foreground">Buat shift kerja baru untuk karyawan</p>
                </div>
                <Link
                    href="/admin/work-shifts"
                    class="inline-flex items-center gap-2 rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
            </div>

            <!-- Create Form -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Informasi Shift</h3>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="name">Nama Shift *</Label>
                                    <Input id="name" v-model="form.name" placeholder="e.g. Shift Pagi" :error="form.errors.name" />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="code">Kode Shift *</Label>
                                    <Input id="code" v-model="form.code" placeholder="e.g. SP" maxlength="10" :error="form.errors.code" />
                                    <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="start_time">Waktu Mulai *</Label>
                                    <Input id="start_time" v-model="form.start_time" type="time" :error="form.errors.start_time" />
                                    <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.start_time }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="end_time">Waktu Selesai *</Label>
                                    <Input id="end_time" v-model="form.end_time" type="time" :error="form.errors.end_time" />
                                    <p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.end_time }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <Label for="break_duration">Durasi Istirahat (menit) *</Label>
                                    <Input
                                        id="break_duration"
                                        v-model.number="form.break_duration"
                                        type="number"
                                        min="0"
                                        max="480"
                                        placeholder="60"
                                        :error="form.errors.break_duration"
                                    />
                                    <p v-if="form.errors.break_duration" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.break_duration }}
                                    </p>
                                </div>
                                <div>
                                    <Label for="overtime_multiplier">Multiplier Lembur *</Label>
                                    <Input
                                        id="overtime_multiplier"
                                        v-model.number="form.overtime_multiplier"
                                        type="number"
                                        step="0.1"
                                        min="1"
                                        max="3"
                                        placeholder="1.5"
                                        :error="form.errors.overtime_multiplier"
                                    />
                                    <p v-if="form.errors.overtime_multiplier" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.overtime_multiplier }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <Label>Hari Kerja *</Label>
                                <div class="mt-2 grid grid-cols-7 gap-2">
                                    <div v-for="(day, index) in daysOfWeek" :key="index" class="flex flex-col items-center">
                                        <label
                                            :class="[
                                                'flex cursor-pointer flex-col items-center gap-1 rounded-md p-2 transition-colors',
                                                form.workdays.includes(index) ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80',
                                            ]"
                                        >
                                            <input type="checkbox" :value="index" v-model="form.workdays" class="sr-only" />
                                            <span class="text-xs font-medium">{{ day.short }}</span>
                                            <span class="text-[10px]">{{ day.initial }}</span>
                                        </label>
                                    </div>
                                </div>
                                <p v-if="form.errors.workdays" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.workdays }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-6">
                                <div class="flex items-center space-x-2">
                                    <input
                                        id="is_night_shift"
                                        v-model="form.is_night_shift"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300"
                                    />
                                    <Label for="is_night_shift" class="text-sm">Shift Malam</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
                                    <Label for="is_active" class="text-sm">Aktif</Label>
                                </div>
                            </div>

                            <div class="flex gap-4 pt-4">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Shift' }}
                                </Button>
                                <Button type="button" variant="outline" @click="router.visit('/admin/work-shifts')"> Batal </Button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <!-- Preview Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="mb-4 text-lg font-semibold">Preview Shift</h3>
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
                                <span class="text-muted-foreground">Waktu:</span>
                                <span class="font-medium"> {{ form.start_time || '--:--' }} - {{ form.end_time || '--:--' }} </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Durasi Kerja:</span>
                                <span class="font-medium">{{ calculatedWorkHours }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Istirahat:</span>
                                <span class="font-medium">{{ form.break_duration || 0 }} menit</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Hari Kerja:</span>
                                <span class="font-medium">{{ selectedDaysText }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="rounded-lg border bg-blue-50 p-4 dark:bg-blue-950/20">
                        <h4 class="mb-2 font-medium text-blue-900 dark:text-blue-400">Tips:</h4>
                        <ul class="space-y-1 text-sm text-blue-700 dark:text-blue-300">
                            <li>• Kode shift sebaiknya singkat dan mudah diingat</li>
                            <li>• Durasi istirahat dihitung dari total jam kerja</li>
                            <li>• Multiplier lembur untuk perhitungan upah overtime</li>
                            <li>• Shift malam biasanya mendapat tunjangan khusus</li>
                        </ul>
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

const daysOfWeek = [
    { name: 'Minggu', short: 'Min', initial: 'M' },
    { name: 'Senin', short: 'Sen', initial: 'S' },
    { name: 'Selasa', short: 'Sel', initial: 'S' },
    { name: 'Rabu', short: 'Rab', initial: 'R' },
    { name: 'Kamis', short: 'Kam', initial: 'K' },
    { name: 'Jumat', short: 'Jum', initial: 'J' },
    { name: 'Sabtu', short: 'Sab', initial: 'S' },
];

const form = useForm({
    name: '',
    code: '',
    start_time: '09:00',
    end_time: '17:00',
    break_duration: 60,
    overtime_multiplier: 1.5,
    workdays: [1, 2, 3, 4, 5], // Monday to Friday
    is_night_shift: false,
    is_active: true,
});

const calculatedWorkHours = computed(() => {
    if (!form.start_time || !form.end_time) return '--';

    const start = new Date(`2000-01-01 ${form.start_time}`);
    let end = new Date(`2000-01-01 ${form.end_time}`);

    // Handle overnight shifts
    if (end < start) {
        end = new Date(end.getTime() + 24 * 60 * 60 * 1000);
    }

    const totalMinutes = (end.getTime() - start.getTime()) / (1000 * 60);
    const workMinutes = totalMinutes - (form.break_duration || 0);

    const hours = Math.floor(workMinutes / 60);
    const minutes = Math.round(workMinutes % 60);

    return `${hours}j ${minutes}m`;
});

const selectedDaysText = computed(() => {
    if (form.workdays.length === 0) return '-';

    const selectedDays = form.workdays.sort((a, b) => a - b).map((index) => daysOfWeek[index].short);

    return selectedDays.join(', ');
});

const submit = () => {
    form.post('/admin/work-shifts');
};
</script>
