<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Save, ArrowLeft, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';
import DateTimePicker from '@/components/ui/date-picker/DateTimePicker.vue';

interface AnnouncementCategory {
    id: number;
    name: string;
    color: string;
    icon: string;
}

interface User {
    id: number;
    name: string;
    employee_id: string;
}

interface Announcement {
    id: number;
    title: string;
    content: string;
    excerpt: string;
    category: AnnouncementCategory;
    author: User;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    is_active: boolean;
    published_at: string;
    expires_at: string | null;
    created_at: string;
    image_url: string | null;
}

interface Props {
    announcement: Announcement;
    categories: AnnouncementCategory[];
}

const { announcement, categories } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Pengumuman', href: '/admin/announcements' },
    { title: 'Edit', href: `/admin/announcements/${announcement.id}/edit` },
];

const form = useForm({
    title: announcement.title,
    content: announcement.content || '',
    excerpt: announcement.excerpt,
    category_id: announcement.category.id,
    image: null as File | null,
    remove_image: false,
    priority: announcement.priority,
    is_active: announcement.is_active,
    published_at: announcement.published_at,
    expires_at: announcement.expires_at || '',
});

const imagePreview = ref<string | null>(announcement.image_url);

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.image = file;
        form.remove_image = false; // Reset remove flag when new image is selected
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    form.remove_image = true;
    imagePreview.value = null;
    // Reset file input
    const input = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (input) input.value = '';
};

const submit = () => {
    // Use dedicated POST route for file uploads
    form.post(`/admin/announcements/${announcement.id}/update`, {
        forceFormData: true,
    });
};

const getCategoryColor = (color: string) => {
    const colors = {
        blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        amber: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
        indigo: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    };
    return colors[color as keyof typeof colors] || colors.blue;
};
</script>

<template>
    <Head :title="`Edit Pengumuman: ${announcement.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4">
                    <a
                        href="/admin/announcements"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Edit Pengumuman
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                            {{ announcement.title }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <form @submit.prevent="submit" class="divide-y divide-gray-200 dark:divide-gray-800">
                    <!-- Basic Information -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Informasi Dasar
                        </h3>

                        <div class="grid gap-6">
                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Judul Pengumuman
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    :class="{ 'border-red-500': form.errors.title }"
                                    placeholder="Masukkan judul pengumuman..."
                                />
                                <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Category and Priority -->
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kategori
                                    </label>
                                    <select
                                        v-model="form.category_id"
                                        required
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                        :class="{ 'border-red-500': form.errors.category_id }"
                                    >
                                        <option value="">Pilih kategori...</option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">
                                        {{ form.errors.category_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Prioritas
                                    </label>
                                    <select
                                        v-model="form.priority"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    >
                                        <option value="low">Rendah</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">Tinggi</option>
                                        <option value="urgent">Mendesak</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Excerpt -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ringkasan (Opsional)
                                </label>
                                <textarea
                                    v-model="form.excerpt"
                                    rows="2"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                    placeholder="Ringkasan singkat untuk tampilan preview..."
                                    maxlength="500"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Maksimal 500 karakter. Jika kosong, akan otomatis dibuat dari konten.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Konten Pengumuman
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Isi Pengumuman
                            </label>
                            <textarea
                                v-model="form.content"
                                rows="12"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                :class="{ 'border-red-500': form.errors.content }"
                                placeholder="Tulis isi pengumuman di sini..."
                            ></textarea>
                            <p v-if="form.errors.content" class="mt-1 text-xs text-red-600">
                                {{ form.errors.content }}
                            </p>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Gambar (Opsional)
                        </h3>

                        <div class="space-y-4">
                            <!-- Current/Preview Image -->
                            <div v-if="imagePreview" class="relative inline-block">
                                <img
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-700"
                                />
                                <button
                                    type="button"
                                    @click="removeImage"
                                    class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <!-- File Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ imagePreview ? 'Ganti Gambar' : 'Upload Gambar' }}
                                </label>
                                <div class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-10 dark:border-gray-600">
                                    <div class="text-center">
                                        <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="mt-4">
                                            <label class="cursor-pointer">
                                                <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                                                    Pilih gambar
                                                </span>
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    class="sr-only"
                                                    @change="handleImageChange"
                                                />
                                            </label>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                PNG, JPG, GIF hingga 2MB
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="form.errors.image" class="mt-1 text-xs text-red-600">
                                    {{ form.errors.image }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Publishing Options -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Pengaturan Publikasi
                        </h3>

                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Published At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal Publikasi (Opsional)
                                </label>
                                <div class="mt-1">
                                    <DateTimePicker
                                        v-model="form.published_at"
                                        placeholder="Pilih tanggal dan waktu publikasi"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Kosongkan untuk publish sekarang jika aktif
                                </p>
                            </div>

                            <!-- Expires At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tanggal Kadaluarsa (Opsional)
                                </label>
                                <div class="mt-1">
                                    <DateTimePicker
                                        v-model="form.expires_at"
                                        placeholder="Pilih tanggal dan waktu kadaluarsa"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Pengumuman akan disembunyikan setelah tanggal ini
                                </p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mt-6">
                            <div class="flex items-center">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Aktifkan pengumuman
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Pengumuman yang aktif akan tampil di dashboard karyawan
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50">
                        <a
                            href="/admin/announcements"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>