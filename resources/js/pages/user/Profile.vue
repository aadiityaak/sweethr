<template>
    <div class="mx-auto max-w-[480px] bg-background min-h-screen">
        <!-- Header -->
        <div class="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b border-border">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center gap-3">
                    <button @click="$inertia.visit('/home')" class="p-2 -ml-2 rounded-lg hover:bg-muted transition-colors">
                        <ArrowLeft class="h-5 w-5" />
                    </button>
                    <h1 class="text-lg font-semibold">Profil Saya</h1>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="p-4 space-y-6">
            <Form
                action="/user/profile"
                method="post"
                #default="{ errors, processing, wasSuccessful }"
            >
                <div class="space-y-4">
                    <!-- Profile Photo Section -->
                    <div class="flex items-center space-x-4 p-4 bg-muted/50 rounded-lg">
                        <div class="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-xl font-semibold text-primary">{{ user.name?.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium">{{ user.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                            <p class="text-xs text-muted-foreground">ID: {{ user.employee_id }}</p>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="name">Nama Lengkap</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="user.name"
                            required
                            placeholder="Nama lengkap"
                            class="w-full"
                        />
                        <div v-if="errors.name" class="text-sm text-destructive">
                            {{ errors.name }}
                        </div>
                    </div>

                    <!-- Email (Read Only) -->
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            :default-value="user.email"
                            readonly
                            class="w-full bg-muted/50"
                        />
                        <p class="text-xs text-muted-foreground">Email tidak dapat diubah. Hubungi admin untuk perubahan.</p>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <Label for="phone">Nomor Telepon</Label>
                        <Input
                            id="phone"
                            name="phone"
                            type="tel"
                            :default-value="user.phone"
                            placeholder="08xxxxxxxxxx"
                            class="w-full"
                        />
                        <div v-if="errors.phone" class="text-sm text-destructive">
                            {{ errors.phone }}
                        </div>
                    </div>

                    <!-- Date of Birth -->
                    <div class="space-y-2">
                        <Label for="date_of_birth">Tanggal Lahir</Label>
                        <DatePicker
                            v-model="dateOfBirth"
                            placeholder="Pilih tanggal lahir"
                            class="w-full"
                        />
                        <input type="hidden" name="date_of_birth" :value="dateOfBirth" />
                        <div v-if="errors.date_of_birth" class="text-sm text-destructive">
                            {{ errors.date_of_birth }}
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="space-y-2">
                        <Label for="gender">Jenis Kelamin</Label>
                        <select
                            id="gender"
                            name="gender"
                            class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        >
                            <option value="">Pilih jenis kelamin</option>
                            <option value="male" :selected="user.gender === 'male'">Laki-laki</option>
                            <option value="female" :selected="user.gender === 'female'">Perempuan</option>
                        </select>
                        <div v-if="errors.gender" class="text-sm text-destructive">
                            {{ errors.gender }}
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="space-y-2">
                        <Label for="address">Alamat</Label>
                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent resize-none"
                            placeholder="Alamat lengkap"
                        >{{ user.address || '' }}</textarea>
                        <div v-if="errors.address" class="text-sm text-destructive">
                            {{ errors.address }}
                        </div>
                    </div>

                    <!-- Employment Info (Read Only) -->
                    <div class="space-y-4 p-4 bg-muted/30 rounded-lg">
                        <h4 class="font-medium text-sm">Informasi Kepegawaian</h4>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-muted-foreground">Tanggal Mulai Kerja</p>
                                <p class="font-medium">{{ user.hire_date ? formatDate(user.hire_date) : 'Belum diisi' }}</p>
                            </div>
                            <div>
                                <p class="text-muted-foreground">Status</p>
                                <p class="font-medium capitalize">{{ user.employment_status || 'Aktif' }}</p>
                            </div>
                        </div>

                        <p class="text-xs text-muted-foreground">Informasi kepegawaian hanya dapat diubah oleh admin.</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:pointer-events-none rounded-md font-medium transition-colors"
                        >
                            <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                            {{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>

                    <!-- Success Message -->
                    <div v-if="wasSuccessful" class="p-3 bg-green-50 border border-green-200 rounded-md">
                        <p class="text-sm text-green-800">Profil berhasil diperbarui!</p>
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
                    <button @click="$inertia.visit('/leave-requests')" class="flex flex-col items-center gap-1 p-2 text-muted-foreground hover:text-foreground transition-colors">
                        <CircleCheckBig class="h-5 w-5" />
                        <span class="text-xs">Cuti</span>
                    </button>
                    <button @click="$inertia.visit('/profile')" class="flex flex-col items-center gap-1 p-2 text-foreground">
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
import { Input } from '@/components/ui/input'
import { DatePicker } from '@/components/ui/date-picker'

interface User {
    id: number
    name: string
    email: string
    employee_id: string
    phone?: string
    date_of_birth?: string
    gender?: string
    address?: string
    hire_date?: string
    employment_status?: string
}

interface Props {
    user: User
}

const props = defineProps<Props>()

const dateOfBirth = ref(props.user.date_of_birth || '')

// Format date for display
const formatDate = (dateString: string) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    })
}
</script>