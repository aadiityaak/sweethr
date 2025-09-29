<template>
    <Head title="Profil Saya" />

    <div class="min-h-screen bg-background">
        <!-- Mobile Header -->
        <div class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur-sm">
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
                    <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile Container -->
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <!-- Main Content -->
            <div class="px-4 py-6 pb-24">
                <!-- Profile Header Card -->
                <div class="mb-6 rounded-lg border bg-card p-6">
                    <div class="flex flex-col items-center text-center">
                        <!-- Avatar -->
                        <div class="relative mb-4">
                            <div
                                class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-primary text-xl font-bold text-primary-foreground"
                            >
                                <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="h-full w-full object-cover" />
                                <span v-else>{{ getInitials(user.name) }}</span>
                            </div>
                            <button
                                @click="showAvatarUpload = true"
                                class="absolute -right-1 -bottom-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-primary-foreground transition-colors hover:bg-primary/90"
                            >
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

                        <span
                            v-if="user.is_admin"
                            class="mt-2 inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                        >
                            <Shield class="mr-1 h-3 w-3" />
                            Admin
                        </span>
                    </div>
                </div>

                <!-- Profile Content Grid -->
                <div class="grid gap-6">
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
                                class="inline-flex items-center rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
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
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Lahir</label>
                                <div class="mt-1">
                                    <DatePicker v-model="basicInfoForm.date_of_birth" placeholder="Pilih tanggal lahir" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                <textarea
                                    v-model="basicInfoForm.address"
                                    rows="3"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                ></textarea>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <button
                                    type="submit"
                                    :disabled="basicInfoForm.processing"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                                >
                                    <Save class="mr-2 h-4 w-4" />
                                    Simpan
                                </button>
                                <button
                                    type="button"
                                    @click="cancelBasicEdit"
                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
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
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': passwordForm.errors.current_password }"
                                />
                                <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-600">
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password Baru</label>
                                <input
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': passwordForm.errors.password }"
                                />
                                <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-600">{{ passwordForm.errors.password }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password Baru</label>
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                />
                            </div>

                            <div class="pt-4">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                                >
                                    <Key class="mr-2 h-4 w-4" />
                                    Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Face Recognition Card -->
                    <div class="rounded-xl border border-gray-200/50 bg-white p-6 shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/10 ring-1 ring-green-500/20">
                                <Scan class="h-4 w-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pengenalan Wajah</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Setup dan kelola verifikasi wajah untuk absensi</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Face Recognition Status -->
                            <div
                                v-if="faceRecognitionStatus.enabled"
                                class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950/20"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                                        <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-green-900 dark:text-green-100">Face Recognition Aktif</h4>
                                        <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                                            Pengenalan wajah sudah diaktifkan untuk akun Anda.
                                        </p>
                                        <p v-if="faceRecognitionStatus.setup_at" class="mt-1 text-xs text-green-600 dark:text-green-400">
                                            Setup pada: {{ new Date(faceRecognitionStatus.setup_at).toLocaleDateString('id-ID') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-950/20">
                                <div class="flex items-start gap-3">
                                    <div class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900/50">
                                        <AlertCircle class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-yellow-900 dark:text-yellow-100">Face Recognition Belum Setup</h4>
                                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                            Setup pengenalan wajah untuk keamanan tambahan saat absensi.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 pt-2">
                                <button
                                    v-if="!faceRecognitionStatus.enabled"
                                    @click="openFaceSetup"
                                    :disabled="faceLoading"
                                    class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                                >
                                    <Scan class="mr-2 h-4 w-4" />
                                    Setup Face Recognition
                                </button>

                                <button
                                    v-if="faceRecognitionStatus.enabled"
                                    @click="openFaceSetup"
                                    :disabled="faceLoading"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                                >
                                    <Camera class="mr-2 h-4 w-4" />
                                    Setup Ulang
                                </button>

                                <button
                                    v-if="faceRecognitionStatus.enabled"
                                    @click="handleDeleteFaceData"
                                    :disabled="faceLoading"
                                    class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Hapus Data
                                </button>
                            </div>

                            <!-- Warning for mandatory users -->
                            <div
                                v-if="faceRecognitionStatus.mandatory && !faceRecognitionStatus.enabled"
                                class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-950/20"
                            >
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    ⚠️ <strong>Wajib:</strong> Face recognition diperlukan untuk akun Anda. Silakan setup sekarang.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- PWA Install Section -->
                    <div class="mb-6 rounded-lg border bg-card p-6">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                                <Smartphone class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold">Install Aplikasi</h2>
                                <p class="text-sm text-muted-foreground">Install {{ companyName }} ke perangkat Anda</p>
                            </div>
                        </div>

                        <PWAInstallButton />
                    </div>

                    <!-- Theme/Dark Mode Settings -->
                    <div class="mb-6 rounded-lg border bg-card p-6">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/50">
                                <Monitor class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold">Tampilan</h2>
                                <p class="text-sm text-muted-foreground">Pengaturan tema dan dark mode</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-3 block text-sm font-medium">Tema</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button
                                        v-for="theme in themes"
                                        :key="theme.value"
                                        @click="setTheme(theme.value)"
                                        :class="[
                                            'flex flex-col items-center gap-2 rounded-lg border-2 p-4 transition-all hover:bg-accent',
                                            currentTheme === theme.value
                                                ? 'border-primary bg-primary/5 text-primary'
                                                : 'border-border bg-background hover:border-border/60',
                                        ]"
                                    >
                                        <component :is="theme.icon" class="h-5 w-5" />
                                        <span class="text-xs font-medium">{{ theme.label }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-lg bg-muted/50 p-3">
                                <component :is="isDark ? Moon : Sun" class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Mode Saat Ini</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ isDark ? 'Dark Mode' : 'Light Mode' }}
                                        {{ currentTheme === 'system' ? ' (Mengikuti sistem)' : '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logout Section -->
                    <div class="mt-8 mb-6">
                        <div class="rounded-lg border bg-card p-4">
                            <button
                                @click="logout"
                                class="flex w-full items-center justify-center gap-3 rounded-md bg-destructive px-4 py-3 text-sm font-medium text-destructive-foreground transition-colors hover:bg-destructive/90"
                            >
                                <LogOut class="h-4 w-4" />
                                Keluar dari Akun
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/user/profile" />
        </div>

        <!-- Face Recognition Modal -->
        <FaceCapture v-if="showFaceCapture" mode="setup" @capture="handleFaceSetup" @close="closeFaceCapture" @error="handleFaceCaptureError" />

        <!-- Avatar Upload Modal -->
        <Dialog v-model:open="showAvatarUpload">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Camera class="h-5 w-5" />
                        Ubah Foto Profil
                    </DialogTitle>
                    <DialogDescription> Upload foto profil baru. File harus berformat gambar dan maksimal 2MB. </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <!-- Preview -->
                    <div class="flex justify-center">
                        <div
                            class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-full bg-primary text-xl font-bold text-primary-foreground"
                        >
                            <img
                                v-if="avatarPreview || user.avatar"
                                :src="avatarPreview || user.avatar"
                                :alt="user.name"
                                class="h-full w-full object-cover"
                            />
                            <span v-else>{{ getInitials(user.name) }}</span>
                        </div>
                    </div>

                    <!-- File Input -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Pilih Foto </label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleAvatarUpload"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                        />
                        <p class="text-xs text-gray-500">Format: JPG, PNG, GIF. Maksimal 2MB.</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <Button @click="uploadAvatar" :disabled="!avatarForm.avatar || isUploadingAvatar" class="flex-1">
                            <Loader2 v-if="isUploadingAvatar" class="mr-2 h-4 w-4 animate-spin" />
                            <Upload v-else class="mr-2 h-4 w-4" />
                            {{ isUploadingAvatar ? 'Mengupload...' : 'Upload' }}
                        </Button>

                        <Button v-if="user.avatar && !avatarPreview" @click="removeAvatar" variant="destructive" size="sm">
                            <Trash2 class="h-4 w-4" />
                        </Button>

                        <Button @click="cancelAvatarUpload" variant="outline"> Batal </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import FaceCapture from '@/components/FaceCapture.vue';
import PWAInstallButton from '@/components/PWAInstallButton.vue';
import { Button } from '@/components/ui/button';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useCompanySettings } from '@/composables/useCompanySettings';
import { useFaceRecognition } from '@/composables/useFaceRecognition';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    Calendar,
    Camera,
    CheckCircle,
    Edit,
    Key,
    Loader2,
    LogOut,
    Mail,
    MapPin,
    Monitor,
    Moon,
    Phone,
    Save,
    Scan,
    Shield,
    Smartphone,
    Sun,
    Trash2,
    Upload,
    User,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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
    face_recognition_enabled?: boolean;
    face_recognition_mandatory?: boolean;
    face_descriptors?: any;
    face_setup_at?: string;
}

interface Props {
    user: UserProfile;
}

const { user } = defineProps<Props>();

// Debug user data
console.log('Profile component user data:', {
    face_recognition_enabled: user.face_recognition_enabled,
    face_recognition_mandatory: user.face_recognition_mandatory,
    face_setup_at: user.face_setup_at,
    has_face_descriptors: !!user.face_descriptors,
});

// Face Recognition
const {
    isLoading: faceLoading,
    showFaceCapture,
    isSetupMode,
    setupFaceRecognition,
    deleteFaceData,
    openFaceSetup,
    closeFaceCapture,
    handleFaceCaptureError,
    faceRecognitionStatus,
    initializeFaceRecognitionStatus,
    refreshStatus,
} = useFaceRecognition();

// Edit states
const isEditingBasic = ref(false);
const showAvatarUpload = ref(false);
const avatarPreview = ref<string | null>(null);
const isUploadingAvatar = ref(false);

const { companyName } = useCompanySettings();

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

const avatarForm = useForm({
    avatar: null as File | null,
});

// Computed properties
const formattedHireDate = computed(() => {
    if (!user.hire_date) return 'Belum tersedia';
    return new Date(user.hire_date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const formattedDateOfBirth = computed(() => {
    if (!user.date_of_birth) return '-';
    return new Date(user.date_of_birth).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

// Methods
const logout = () => {
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        router.post('/logout');
    }
};

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
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase();
};

// Dark mode functionality
type Theme = 'light' | 'dark' | 'system';

const currentTheme = ref<Theme>('light');
const isDark = ref(false);

const themes = [
    { value: 'light', label: 'Light', icon: Sun },
    { value: 'dark', label: 'Dark', icon: Moon },
    { value: 'system', label: 'System', icon: Monitor },
] as const;

const applyTheme = (theme: Theme) => {
    if (theme === 'system') {
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        isDark.value = systemPrefersDark;
        if (systemPrefersDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    } else if (theme === 'dark') {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
};

const setTheme = (theme: Theme) => {
    currentTheme.value = theme;
    localStorage.setItem('theme', theme);
    applyTheme(theme);
};

onMounted(async () => {
    const savedTheme = (localStorage.getItem('theme') as Theme) || 'light';
    currentTheme.value = savedTheme;
    applyTheme(savedTheme);

    // Initialize face recognition status from props
    console.log('Profile.vue onMounted - initializing with user data:', {
        face_recognition_enabled: user.face_recognition_enabled,
        face_recognition_mandatory: user.face_recognition_mandatory,
        face_setup_at: user.face_setup_at,
        has_face_descriptors: !!user.face_descriptors,
    });

    await initializeFaceRecognitionStatus({
        face_recognition_enabled: user.face_recognition_enabled,
        face_recognition_mandatory: user.face_recognition_mandatory,
        face_setup_at: user.face_setup_at,
        face_descriptors: user.face_descriptors,
    });

    console.log('Profile.vue onMounted - after init, reactive status:', {
        enabled: faceRecognitionStatus.value.enabled,
        mandatory: faceRecognitionStatus.value.mandatory,
        setup_at: faceRecognitionStatus.value.setup_at,
        has_descriptors: faceRecognitionStatus.value.has_descriptors,
    });

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (currentTheme.value === 'system') {
            applyTheme('system');
        }
    });
});

// Face Recognition Methods
const handleFaceSetup = async (descriptors: number[][]) => {
    console.log('Face setup started with descriptors:', descriptors.length);
    console.log('First descriptor sample:', descriptors[0]?.slice(0, 5)); // First 5 values for debugging

    const success = await setupFaceRecognition(descriptors);
    console.log('Face setup result:', success);

    if (success) {
        console.log('Face recognition setup completed successfully');
    } else {
        console.error('Setup failed');
    }
};

const handleDeleteFaceData = async () => {
    if (confirm('Apakah Anda yakin ingin menghapus data face recognition? Anda perlu setup ulang nanti.')) {
        const success = await deleteFaceData();
        if (success) {
            console.log('Delete successful - automatic page refresh will sync data across app');
        }
    }
};

// Avatar upload methods
const handleAvatarUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
        alert('Mohon pilih file gambar yang valid.');
        return;
    }

    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 2MB.');
        return;
    }

    avatarForm.avatar = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        avatarPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const uploadAvatar = () => {
    if (!avatarForm.avatar) return;

    isUploadingAvatar.value = true;

    avatarForm.post('/user/profile/avatar', {
        onSuccess: () => {
            showAvatarUpload.value = false;
            avatarPreview.value = null;
            avatarForm.reset();
            // Refresh the page to update the avatar
            router.reload();
        },
        onError: (errors) => {
            console.error('Avatar upload errors:', errors);
        },
        onFinish: () => {
            isUploadingAvatar.value = false;
        },
    });
};

const cancelAvatarUpload = () => {
    showAvatarUpload.value = false;
    avatarPreview.value = null;
    avatarForm.reset();
};

const removeAvatar = () => {
    if (!confirm('Apakah Anda yakin ingin menghapus foto profil?')) return;

    useForm({}).delete('/user/profile/avatar', {
        onSuccess: () => {
            // Refresh the page to update the avatar
            router.reload();
        },
    });
};
</script>
