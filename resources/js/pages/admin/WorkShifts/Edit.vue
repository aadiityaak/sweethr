<template>
    <Head :title="`Edit Shift - ${workShift.name}`" />

    <AppLayout>
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Shift Kerja</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Edit informasi shift "{{ workShift.name }}"</p>
                    </div>
                    <Link
                        href="/admin/work-shifts"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Shift</h3>
                        </div>

                        <form @submit.prevent="submit" class="p-6">
                            <div class="space-y-6">
                                <!-- Nama & Kode -->
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="name" class="text-sm font-medium text-gray-900 dark:text-white">Nama Shift *</Label>
                                        <Input id="name" v-model="form.name" placeholder="e.g. Shift Pagi" :error="form.errors.name" />
                                        <p v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="code" class="text-sm font-medium text-gray-900 dark:text-white">Kode Shift *</Label>
                                        <Input id="code" v-model="form.code" placeholder="e.g. SP" maxlength="10" :error="form.errors.code" />
                                        <p v-if="form.errors.code" class="text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.code }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Waktu -->
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="start_time" class="text-sm font-medium text-gray-900 dark:text-white">Waktu Mulai *</Label>
                                        <Input id="start_time" v-model="form.start_time" type="time" :error="form.errors.start_time" />
                                        <p v-if="form.errors.start_time" class="text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.start_time }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="end_time" class="text-sm font-medium text-gray-900 dark:text-white">Waktu Selesai *</Label>
                                        <Input id="end_time" v-model="form.end_time" type="time" :error="form.errors.end_time" />
                                        <p v-if="form.errors.end_time" class="text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.end_time }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Istirahat & Lembur -->
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="break_duration" class="text-sm font-medium text-gray-900 dark:text-white"
                                            >Durasi Istirahat (menit) *</Label
                                        >
                                        <Input
                                            id="break_duration"
                                            v-model.number="form.break_duration"
                                            type="number"
                                            min="0"
                                            max="480"
                                            placeholder="60"
                                            :error="form.errors.break_duration"
                                        />
                                        <p v-if="form.errors.break_duration" class="text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.break_duration }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="overtime_multiplier" class="text-sm font-medium text-gray-900 dark:text-white">Multiplier Lembur *</Label>
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
                                        <p v-if="form.errors.overtime_multiplier" class="text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.overtime_multiplier }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Hari Kerja -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-gray-900 dark:text-white">Hari Kerja *</Label>
                                    <div class="grid grid-cols-7 gap-2">
                                        <div v-for="(day, index) in daysOfWeek" :key="index">
                                            <label
                                                :class="[
                                                    'flex h-16 w-full cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border transition-all',
                                                    form.workdays.includes(index)
                                                        ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                                        : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-500 dark:hover:bg-gray-700',
                                                ]"
                                            >
                                                <input type="checkbox" :value="index" v-model="form.workdays" class="sr-only" />
                                                <span class="text-xs font-semibold">{{ day.short }}</span>
                                                <span class="text-[10px] opacity-80">{{ day.initial }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.workdays" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.workdays }}
                                    </p>
                                </div>

                                <!-- Checkboxes -->
                                <div class="flex flex-wrap items-center gap-6">
                                    <div class="flex items-center gap-2">
                                        <input
                                            id="is_night_shift"
                                            v-model="form.is_night_shift"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                        />
                                        <Label for="is_night_shift" class="text-sm font-medium text-gray-900 dark:text-white">Shift Malam</Label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input
                                            id="is_active"
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                        />
                                        <Label for="is_active" class="text-sm font-medium text-gray-900 dark:text-white">Aktif</Label>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                                    <Button type="submit" :disabled="form.processing" class="px-6">
                                        {{ form.processing ? 'Menyimpan...' : 'Update Shift' }}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="router.visit('/admin/work-shifts')"
                                        class="border-gray-300 px-6 dark:border-gray-600"
                                    >
                                        Batal
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <!-- Preview Card -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preview Shift</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4 text-sm">
                                <div class="flex justify-between gap-4">
                                    <span class="text-gray-600 dark:text-gray-400">Nama:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ form.name || '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-gray-600 dark:text-gray-400">Kode:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ form.code || '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-gray-600 dark:text-gray-400">Waktu:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ form.start_time || '--:--' }} - {{ form.end_time || '--:--' }}
                                    </span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-gray-600 dark:text-gray-400">Durasi Kerja:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ calculatedWorkHours }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-gray-600 dark:text-gray-400">Istirahat:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ form.break_duration || 0 }} menit</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-gray-600 dark:text-gray-400">Hari Kerja:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ selectedDaysText }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning if has assignments -->
                    <div
                        v-if="workShift.employee_shifts_count > 0"
                        class="overflow-hidden rounded-xl bg-yellow-50 shadow-sm ring-1 ring-yellow-200 dark:bg-yellow-950/20 dark:ring-yellow-800/30"
                    >
                        <div class="p-6">
                            <h4 class="mb-2 flex items-center gap-2 font-semibold text-yellow-900 dark:text-yellow-400">
                                <AlertTriangle class="h-5 w-5" />
                                Peringatan
                            </h4>
                            <p class="text-sm leading-relaxed text-yellow-800 dark:text-yellow-300">
                                Shift ini memiliki <strong>{{ workShift.employee_shifts_count }}</strong> penugasan aktif. Perubahan akan mempengaruhi jadwal
                                karyawan yang sudah ditugaskan.
                            </p>
                        </div>
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
import { AlertTriangle, ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

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

interface Props {
    workShift: WorkShift;
}

const props = defineProps<Props>();

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
    name: props.workShift.name,
    code: props.workShift.code,
    start_time: props.workShift.start_time.substring(0, 5), // HH:MM format
    end_time: props.workShift.end_time.substring(0, 5),
    break_duration: props.workShift.break_duration,
    overtime_multiplier: props.workShift.overtime_multiplier,
    workdays: [...props.workShift.workdays],
    is_night_shift: props.workShift.is_night_shift,
    is_active: props.workShift.is_active,
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
    form.put(`/admin/work-shifts/${props.workShift.id}`);
};
</script>
