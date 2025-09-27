<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { Calendar, Clock, AlertCircle } from 'lucide-vue-next';

interface Props {
    monthlyCount: number;
    monthlyLimit: number;
}

const { monthlyCount, monthlyLimit } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Request Tukar Libur',
        href: '/shift-change-requests',
    },
    {
        title: 'Buat Request',
        href: '/shift-change-requests/create',
    },
];

const isLimitReached = monthlyCount >= monthlyLimit;
</script>

<template>
    <Head title="Buat Request Tukar Libur" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                        <Calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Buat Request Tukar Libur
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            Ajukan permintaan untuk menukar jadwal libur Anda
                        </p>
                    </div>
                </div>
            </div>

            <!-- Monthly Limit Info -->
            <div class="mb-6 rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg"
                         :class="isLimitReached ? 'bg-red-500/10 ring-1 ring-red-500/20' : 'bg-green-500/10 ring-1 ring-green-500/20'">
                        <AlertCircle class="h-4 w-4"
                                    :class="isLimitReached ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            Request Bulan Ini: {{ monthlyCount }}/{{ monthlyLimit }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ isLimitReached ? 'Anda telah mencapai batas maksimal request per bulan' : `Sisa ${monthlyLimit - monthlyCount} request tersedia` }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 p-6 shadow-sm dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30">
                <Form
                    action="/shift-change-requests"
                    method="post"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Original Date -->
                        <div class="space-y-3">
                            <Label for="original_date" class="text-sm font-medium text-gray-900 dark:text-white">
                                Tanggal Libur Saat Ini *
                            </Label>
                            <div class="relative">
                                <Input
                                    id="original_date"
                                    type="date"
                                    name="original_date"
                                    required
                                    :disabled="isLimitReached"
                                    class="h-12 rounded-lg border-2 bg-background/50 backdrop-blur-sm transition-all duration-200 focus:border-primary focus:bg-background focus:ring-2 focus:ring-primary/20"
                                />
                                <Calendar class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            </div>
                            <InputError :message="errors.original_date" class="text-xs" />
                        </div>

                        <!-- Requested Date -->
                        <div class="space-y-3">
                            <Label for="requested_date" class="text-sm font-medium text-gray-900 dark:text-white">
                                Tanggal Ganti Kerja *
                            </Label>
                            <div class="relative">
                                <Input
                                    id="requested_date"
                                    type="date"
                                    name="requested_date"
                                    required
                                    :disabled="isLimitReached"
                                    class="h-12 rounded-lg border-2 bg-background/50 backdrop-blur-sm transition-all duration-200 focus:border-primary focus:bg-background focus:ring-2 focus:ring-primary/20"
                                />
                                <Clock class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 pointer-events-none" />
                            </div>
                            <InputError :message="errors.requested_date" class="text-xs" />
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="space-y-3">
                        <Label for="reason" class="text-sm font-medium text-gray-900 dark:text-white">
                            Alasan (Opsional)
                        </Label>
                        <Textarea
                            id="reason"
                            name="reason"
                            rows="4"
                            :disabled="isLimitReached"
                            placeholder="Jelaskan alasan Anda ingin menukar jadwal libur..."
                            class="rounded-lg border-2 bg-background/50 backdrop-blur-sm transition-all duration-200 placeholder:text-muted-foreground/70 focus:border-primary focus:bg-background focus:ring-2 focus:ring-primary/20"
                        />
                        <InputError :message="errors.reason" class="text-xs" />
                    </div>

                    <!-- Error Messages -->
                    <div v-if="errors.limit" class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/30">
                        <div class="flex">
                            <AlertCircle class="h-5 w-5 text-red-400" />
                            <div class="ml-3">
                                <p class="text-sm text-red-800 dark:text-red-200">{{ errors.limit }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="errors.conflict" class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                        <div class="flex">
                            <AlertCircle class="h-5 w-5 text-amber-400" />
                            <div class="ml-3">
                                <p class="text-sm text-amber-800 dark:text-amber-200">{{ errors.conflict }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 pt-6">
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/shift-change-requests')"
                            class="h-12 px-6"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="processing || isLimitReached"
                            class="h-12 px-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800"
                        >
                            <Calendar v-if="!processing" class="mr-2 h-4 w-4" />
                            <div v-if="processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            <span v-if="processing">Menyimpan...</span>
                            <span v-else-if="isLimitReached">Batas Tercapai</span>
                            <span v-else>Ajukan Request</span>
                        </Button>
                    </div>
                </Form>
            </div>

            <!-- Help Text -->
            <div class="mt-6 rounded-lg border border-blue-200/50 bg-blue-50/50 p-4 dark:border-blue-800/50 dark:bg-blue-950/30">
                <div class="flex">
                    <AlertCircle class="h-5 w-5 text-blue-500 dark:text-blue-400 shrink-0" />
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Informasi Penting</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Pastikan tanggal yang dipilih sudah benar sebelum submit</li>
                                <li>Request akan ditinjau oleh admin dan memerlukan persetujuan</li>
                                <li>Maksimal 5 request per bulan per karyawan</li>
                                <li>Request yang sudah disetujui tidak dapat dibatalkan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>