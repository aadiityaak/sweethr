<template>
    <Head title="Profil Saya" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Header -->
        <div class="bg-background/95 backdrop-blur-sm border-b sticky top-0 z-40 w-full">
            <div class="mx-auto max-w-[480px] px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-primary p-2">
                            <User class="h-4 w-4 text-primary-foreground" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold">Profil Saya</h1>
                            <p class="text-sm text-muted-foreground">Pengaturan Akun</p>
                        </div>
                    </div>
                    <Link
                        href="/user/welcome"
                        class="rounded-md bg-secondary p-2 text-secondary-foreground hover:bg-secondary/80 transition-colors"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile Container -->
        <div class="mx-auto max-w-[480px] bg-background min-h-screen">

            <!-- Main Content -->
            <div class="px-4 py-6 pb-24">

                <!-- Profile Header Card -->
                <div class="mb-6 rounded-lg border bg-card p-6">
                    <div class="flex flex-col items-center text-center">
                        <!-- Avatar -->
                        <div class="relative mb-4">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground">
                                {{ getInitials(user.name) }}
                            </div>
                            <button class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                <Camera class="h-3 w-3" />
                            </button>
                        </div>

                        <!-- User Info -->
                        <h2 class="text-lg font-semibold">{{ user.name }}</h2>
                        <div class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                            <span>{{ user.employee_id }}</span>
                            <span>•</span>
                            <span>{{ user.position?.title || 'Staff' }}</span>
                        </div>
                        <div class="mt-1 text-sm text-muted-foreground">
                            {{ user.department?.name || 'General' }}
                        </div>

                        <span v-if="user.is_admin" class="mt-2 inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                            <Shield class="mr-1 h-3 w-3" />
                            Admin
                        </span>
                    </div>
                </div>

            <!-- Profile Content Grid -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Basic Information Card -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                                <User class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Dasar</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Data pribadi dan kontak</p>
                            </div>
                        </div>
                        <button
                            v-if="!isEditingBasic"
                            @click="isEditingBasic = true"
                            class="inline-flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </button>
                    </div>

                    <form v-if="isEditingBasic" @submit.prevent="updateBasicInfo" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                            <input
                                v-model="basicInfoForm.name"
                                type="text"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': basicInfoForm.errors.name }"
                            />
                            <p v-if="basicInfoForm.errors.name" class="mt-1 text-xs text-red-600">{{ basicInfoForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input
                                v-model="basicInfoForm.email"
                                type="email"
                                readonly
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                            />
                            <p class="mt-1 text-xs text-gray-500">Email tidak dapat diubah. Hubungi admin untuk perubahan.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon</label>
                            <input
                                v-model="basicInfoForm.phone"
                                type="tel"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                            <div class="mt-1">
                                <DatePicker
                                    v-model="basicInfoForm.date_of_birth"
                                    placeholder="Pilih tanggal lahir"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                            <textarea
                                v-model="basicInfoForm.address"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            ></textarea>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="submit"
                                :disabled="basicInfoForm.processing"
                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                <Save class="mr-2 h-4 w-4" />
                                Simpan
                            </button>
                            <button
                                type="button"
                                @click="cancelBasicEdit"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                <X class="mr-2 h-4 w-4" />
                                Batal
                            </button>
                        </div>
                    </form>

                    <div v-else class="space-y-4">
                        <div class="flex items-center gap-3 rounded-lg bg-gray-50/50 p-3 dark:bg-gray-800/50">
                            <Mail class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Email</p>
                                <p class="text-sm text-gray-900 dark:text-white">{{ user.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-lg bg-gray-50/50 p-3 dark:bg-gray-800/50">
                            <Phone class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Telepon</p>
                                <p class="text-sm text-gray-900 dark:text-white">{{ user.phone || '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-lg bg-gray-50/50 p-3 dark:bg-gray-800/50">
                            <Calendar class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Lahir</p>
                                <p class="text-sm text-gray-900 dark:text-white">{{ formattedDateOfBirth }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-lg bg-gray-50/50 p-3 dark:bg-gray-800/50">
                            <MapPin class="mt-0.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Alamat</p>
                                <p class="text-sm text-gray-900 dark:text-white">{{ user.address || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                            <Key class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ubah Password</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Perbarui password untuk keamanan akun</p>
                        </div>
                    </div>

                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Saat Ini</label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': passwordForm.errors.current_password }"
                            />
                            <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-600">{{ passwordForm.errors.current_password }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': passwordForm.errors.password }"
                            />
                            <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-600">{{ passwordForm.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            />
                        </div>

                        <div class="pt-4">
                            <button
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                <Key class="mr-2 h-4 w-4" />
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </div>

        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 z-50 w-full border-t bg-background/95 backdrop-blur-sm">
            <div class="mx-auto max-w-[480px]">
                <div class="grid grid-cols-4 divide-x">
                    <Link href="/user/welcome" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Home class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Beranda</span>
                    </Link>

                    <Link href="/user/attendance" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Clock class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Absensi</span>
                    </Link>

                    <Link href="/user/leave-requests" class="flex flex-col items-center py-3 px-2 text-muted-foreground hover:text-foreground">
                        <div class="rounded-md p-2 mb-1">
                            <Calendar class="h-4 w-4" />
                        </div>
                        <span class="text-xs font-medium">Cuti</span>
                    </Link>

                    <Link href="/user/profile" class="flex flex-col items-center py-3 px-2 text-foreground">
                        <div class="rounded-md bg-primary/10 p-2 mb-1">
                            <Settings class="h-4 w-4 text-primary" />
                        </div>
                        <span class="text-xs font-medium text-primary">Profil</span>
                    </Link>
                </div>
            </div>
        </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import {
    User,
    Mail,
    Phone,
    MapPin,
    Calendar,
    Building,
    UserCheck,
    Edit,
    Save,
    X,
    Camera,
    Shield,
    Clock,
    Key,
    ArrowLeft,
    Home,
    Settings
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface UserProfile {
    id: number;
    name: string;
    email: string;
    employee_id: string;
    phone?: string;
    address?: string;
    date_of_birth?: string;
    hire_date?: string;
    is_admin?: boolean;
    avatar?: string;
    department?: {
        id: number;
        name: string;
    };
    position?: {
        id: number;
        title: string;
    };
}

interface Props {
    user: UserProfile;
}

const { user } = defineProps<Props>();

// Edit states
const isEditingBasic = ref(false);

// Forms
const basicInfoForm = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    address: user.address || '',
    date_of_birth: user.date_of_birth || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Computed properties
const formattedHireDate = computed(() => {
    if (!user.hire_date) return 'Belum tersedia';
    return new Date(user.hire_date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const formattedDateOfBirth = computed(() => {
    if (!user.date_of_birth) return '-';
    return new Date(user.date_of_birth).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

// Methods
const updateBasicInfo = () => {
    basicInfoForm.put('/user/profile/basic', {
        onSuccess: () => {
            isEditingBasic.value = false;
        },
    });
};

const updatePassword = () => {
    passwordForm.put('/user/profile/password', {
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

const cancelBasicEdit = () => {
    basicInfoForm.reset();
    isEditingBasic.value = false;
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};
</script>