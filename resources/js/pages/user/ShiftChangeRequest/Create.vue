<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { DatePicker } from '@/components/ui/date-picker';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Form, Head } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, RefreshCw } from 'lucide-vue-next';
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

    <div class="mx-auto min-h-screen max-w-[480px] bg-background">
        <!-- Header -->
        <div class="sticky top-0 z-50 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center gap-3">
                    <button @click="$inertia.visit('/shift-change-requests')" class="-ml-2 rounded-lg p-2 transition-colors hover:bg-muted">
                        <ArrowLeft class="h-5 w-5" />
                    </button>
                    <h1 class="text-lg font-semibold">Buat Request Tukar Libur</h1>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="space-y-6 p-4">
            <!-- Monthly Limit Info -->
            <div class="rounded-lg border bg-card p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-md"
                        :class="isLimitReached ? 'bg-red-100 dark:bg-red-900/20' : 'bg-green-100 dark:bg-green-900/20'"
                    >
                        <RefreshCw
                            class="h-5 w-5"
                            :class="isLimitReached ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                        />
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
            <Form action="/shift-change-requests" method="post" v-slot="{ errors, processing }" class="space-y-4">
                <!-- Original Date -->
                <div class="space-y-2">
                    <Label for="original_date">Tanggal Libur Saat Ini</Label>
                    <DatePicker
                        v-model="originalDate"
                        placeholder="Pilih tanggal libur saat ini"
                        required
                        :disabled="isLimitReached"
                        class="w-full"
                    />
                    <input type="hidden" name="original_date" :value="originalDate" />
                    <div v-if="errors.original_date" class="text-sm text-destructive">
                        {{ errors.original_date }}
                    </div>
                </div>

                <!-- Requested Date -->
                <div class="space-y-2">
                    <Label for="requested_date">Tanggal Ganti Kerja</Label>
                    <DatePicker v-model="requestedDate" placeholder="Pilih tanggal ganti kerja" required :disabled="isLimitReached" class="w-full" />
                    <input type="hidden" name="requested_date" :value="requestedDate" />
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
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:border-transparent focus:ring-2 focus:ring-ring focus:outline-none"
                    />
                    <div v-if="errors.reason" class="text-sm text-destructive">
                        {{ errors.reason }}
                    </div>
                </div>

                <!-- Error Messages -->
                <div v-if="errors.limit" class="rounded-md bg-destructive/10 p-3">
                    <div class="flex">
                        <AlertCircle class="h-5 w-5 shrink-0 text-destructive" />
                        <div class="ml-3">
                            <p class="text-sm text-destructive">{{ errors.limit }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="errors.conflict" class="rounded-md bg-orange-50 p-3 dark:bg-orange-950/30">
                    <div class="flex">
                        <AlertCircle class="h-5 w-5 shrink-0 text-orange-500" />
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
                        :class="isLimitReached ? 'cursor-not-allowed bg-muted' : 'bg-primary hover:bg-primary/90'"
                    >
                        <span v-if="processing">Menyimpan...</span>
                        <span v-else-if="isLimitReached">Batas Tercapai</span>
                        <span v-else>Ajukan Request</span>
                    </button>

                    <button
                        type="button"
                        @click="$inertia.visit('/shift-change-requests')"
                        class="w-full rounded-md bg-secondary px-4 py-3 text-sm font-medium text-secondary-foreground transition-colors hover:bg-secondary/80"
                    >
                        Batal
                    </button>
                </div>
            </Form>
        </div>

        <BottomNavigation current-route="/shift-change-requests" />

        <!-- Bottom padding to prevent content hiding behind fixed nav -->
        <div class="h-16"></div>
    </div>
</template>
