<template>
    <div class="mx-auto max-w-[480px] bg-background min-h-screen">
        <!-- Header -->
        <div class="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b border-border">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center gap-3">
                    <button @click="$inertia.visit('/leave-requests')" class="p-2 -ml-2 rounded-lg hover:bg-muted transition-colors">
                        <ArrowLeft class="h-5 w-5" />
                    </button>
                    <h1 class="text-lg font-semibold">Ajukan Cuti</h1>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="p-4 space-y-6">
            <Form
                action="/leave-requests"
                method="post"
                enctype="multipart/form-data"
                #default="{ errors, processing, wasSuccessful }"
            >
                <div class="space-y-4">
                    <!-- Leave Type -->
                    <div class="space-y-2">
                        <Label for="leave_type_id">Jenis Cuti</Label>
                        <select
                            id="leave_type_id"
                            name="leave_type_id"
                            required
                            class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        >
                            <option value="">Pilih jenis cuti</option>
                            <option v-for="leaveType in leaveTypes" :key="leaveType.id" :value="leaveType.id">
                                {{ leaveType.name }}
                            </option>
                        </select>
                        <div v-if="errors.leave_type_id" class="text-sm text-destructive">
                            {{ errors.leave_type_id }}
                        </div>
                    </div>

                    <!-- Start Date -->
                    <div class="space-y-2">
                        <Label for="start_date">Tanggal Mulai</Label>
                        <DatePicker
                            v-model="startDate"
                            placeholder="Pilih tanggal mulai"
                            required
                            class="w-full"
                        />
                        <input type="hidden" name="start_date" :value="startDate" />
                        <div v-if="errors.start_date" class="text-sm text-destructive">
                            {{ errors.start_date }}
                        </div>
                    </div>

                    <!-- End Date -->
                    <div class="space-y-2">
                        <Label for="end_date">Tanggal Selesai</Label>
                        <DatePicker
                            v-model="endDate"
                            placeholder="Pilih tanggal selesai"
                            required
                            class="w-full"
                        />
                        <input type="hidden" name="end_date" :value="endDate" />
                        <div v-if="errors.end_date" class="text-sm text-destructive">
                            {{ errors.end_date }}
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="space-y-2">
                        <Label for="reason">Alasan Cuti</Label>
                        <textarea
                            id="reason"
                            name="reason"
                            rows="4"
                            required
                            placeholder="Jelaskan alasan pengajuan cuti..."
                            class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent resize-none"
                        ></textarea>
                        <div v-if="errors.reason" class="text-sm text-destructive">
                            {{ errors.reason }}
                        </div>
                    </div>

                    <!-- Attachment -->
                    <div class="space-y-2">
                        <Label for="attachment">Lampiran Foto (Opsional)</Label>
                        <div class="space-y-2">
                            <input
                                id="attachment"
                                name="attachment"
                                type="file"
                                accept="image/*"
                                class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:bg-muted file:text-muted-foreground hover:file:bg-muted/80"
                            />
                            <p class="text-xs text-muted-foreground">
                                Upload foto surat ijin, surat dokter, atau dokumen pendukung lainnya (maks. 5MB)
                            </p>
                        </div>
                        <div v-if="errors.attachment" class="text-sm text-destructive">
                            {{ errors.attachment }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:pointer-events-none rounded-md font-medium transition-colors"
                        >
                            <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                            {{ processing ? 'Mengajukan...' : 'Ajukan Cuti' }}
                        </button>
                    </div>
                </div>
            </Form>
        </div>

        <!-- Fixed Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 bg-background border-t border-border">
            <div class="mx-auto max-w-[480px]">
                <nav class="flex items-center justify-around py-2">
                    <button @click="$inertia.visit('/home')" class="flex flex-col items-center gap-1 p-2 text-muted-foreground hover:text-foreground transition-colors">
                        <House class="h-5 w-5" />
                        <span class="text-xs">Beranda</span>
                    </button>
                    <button @click="$inertia.visit('/attendance')" class="flex flex-col items-center gap-1 p-2 text-muted-foreground hover:text-foreground transition-colors">
                        <Target class="h-5 w-5" />
                        <span class="text-xs">Absensi</span>
                    </button>
                    <button @click="$inertia.visit('/leave-requests')" class="flex flex-col items-center gap-1 p-2 text-foreground">
                        <CircleCheckBig class="h-5 w-5" />
                        <span class="text-xs">Cuti</span>
                    </button>
                    <button @click="$inertia.visit('/profile')" class="flex flex-col items-center gap-1 p-2 text-muted-foreground hover:text-foreground transition-colors">
                        <Settings class="h-5 w-5" />
                        <span class="text-xs">Profil</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Bottom padding to prevent content hiding behind fixed nav -->
        <div class="h-20"></div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Form } from '@inertiajs/vue3'
import { ArrowLeft, House, Target, CircleCheckBig, Settings, LoaderCircle } from 'lucide-vue-next'
import { Label } from '@/components/ui/label'
import { DatePicker } from '@/components/ui/date-picker'

interface LeaveType {
    id: number
    name: string
}

interface Props {
    leaveTypes: LeaveType[]
}

defineProps<Props>()

const startDate = ref('')
const endDate = ref('')
</script>