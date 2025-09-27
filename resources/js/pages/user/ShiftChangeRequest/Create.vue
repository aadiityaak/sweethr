<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Form, Head } from '@inertiajs/vue3';
import { Calendar, ArrowLeft, AlertCircle, RefreshCw } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    monthlyCount: number;
    monthlyLimit: number;
}

const { monthlyCount, monthlyLimit } = defineProps<Props>();

const isLimitReached = monthlyCount >= monthlyLimit;
const originalDate = ref('');
const requestedDate = ref('');
</script>

<template>
    <Head title="Buat Request Tukar Libur" />

    <div class="mx-auto max-w-[480px] bg-background min-h-screen">
        <!-- Header -->
        <div class="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b border-border">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center gap-3">
                    <button @click="$inertia.visit('/shift-change-requests')" class="p-2 -ml-2 rounded-lg hover:bg-muted transition-colors">
                        <ArrowLeft class="h-5 w-5" />
                    </button>
                    <h1 class="text-lg font-semibold">Buat Request Tukar Libur</h1>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="p-4 space-y-6">
            <!-- Monthly Limit Info -->
            <div class="rounded-lg border bg-card p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md"
                         :class="isLimitReached ? 'bg-red-100 dark:bg-red-900/20' : 'bg-green-100 dark:bg-green-900/20'">
                        <RefreshCw class="h-5 w-5"
                                    :class="isLimitReached ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium">Request Bulan Ini</p>
                        <p class="text-lg font-bold">{{ monthlyCount }}/{{ monthlyLimit }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ isLimitReached ? 'Batas maksimal tercapai' : `Sisa ${monthlyLimit - monthlyCount} request tersedia` }}
                        </p>
                    </div>
                </div>
            </div>
            <Form
                action="/shift-change-requests"
                method="post"
                v-slot="{ errors, processing }"
                class="space-y-4"
            >
                <!-- Original Date -->
                <div class="space-y-2">
                    <Label for="original_date">Tanggal Libur Saat Ini</Label>
                    <input
                        id="original_date"
                        type="date"
                        name="original_date"
                        v-model="originalDate"
                        required
                        :disabled="isLimitReached"
                        class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                    />
                    <div v-if="errors.original_date" class="text-sm text-destructive">
                        {{ errors.original_date }}
                    </div>
                </div>

                <!-- Requested Date -->
                <div class="space-y-2">
                    <Label for="requested_date">Tanggal Ganti Kerja</Label>
                    <input
                        id="requested_date"
                        type="date"
                        name="requested_date"
                        v-model="requestedDate"
                        required
                        :disabled="isLimitReached"
                        class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                    />
                    <div v-if="errors.requested_date" class="text-sm text-destructive">
                        {{ errors.requested_date }}
                    </div>
                </div>

                <!-- Reason -->
                <div class="space-y-2">
                    <Label for="reason">Alasan (Opsional)</Label>
                    <Textarea
                        id="reason"
                        name="reason"
                        rows="3"
                        :disabled="isLimitReached"
                        placeholder="Jelaskan alasan Anda ingin menukar jadwal libur..."
                        class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                    />
                    <div v-if="errors.reason" class="text-sm text-destructive">
                        {{ errors.reason }}
                    </div>
                </div>

                <!-- Error Messages -->
                <div v-if="errors.limit" class="rounded-md bg-destructive/10 p-3">
                    <div class="flex">
                        <AlertCircle class="h-5 w-5 text-destructive shrink-0" />
                        <div class="ml-3">
                            <p class="text-sm text-destructive">{{ errors.limit }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="errors.conflict" class="rounded-md bg-orange-50 dark:bg-orange-950/30 p-3">
                    <div class="flex">
                        <AlertCircle class="h-5 w-5 text-orange-500 shrink-0" />
                        <div class="ml-3">
                            <p class="text-sm text-orange-700 dark:text-orange-300">{{ errors.conflict }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="space-y-3 pt-6">
                    <button
                        type="submit"
                        :disabled="processing || isLimitReached"
                        class="w-full rounded-md px-4 py-3 text-sm font-medium text-primary-foreground transition-colors disabled:opacity-50"
                        :class="isLimitReached ? 'bg-muted cursor-not-allowed' : 'bg-primary hover:bg-primary/90'"
                    >
                        <span v-if="processing">Menyimpan...</span>
                        <span v-else-if="isLimitReached">Batas Tercapai</span>
                        <span v-else>Ajukan Request</span>
                    </button>

                    <button
                        type="button"
                        @click="$inertia.visit('/shift-change-requests')"
                        class="w-full rounded-md bg-secondary px-4 py-3 text-sm font-medium text-secondary-foreground hover:bg-secondary/80 transition-colors"
                    >
                        Batal
                    </button>
                </div>
            </Form>
        </div>
    </div>
</template>