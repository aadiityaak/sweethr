<script setup lang="ts">
import DateTimePicker from '@/components/ui/date-picker/DateTimePicker.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Upload, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface AnnouncementCategory {
    id: number;
    name: string;
    color: string;
    icon: string;
}

interface Props {
    categories: AnnouncementCategory[];
}

const { categories } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Pengumuman', href: '/admin/announcements' },
    { title: 'Buat Baru', href: '/admin/announcements/create' },
];

const form = useForm({
    title: '',
    content: '',
    excerpt: '',
    category_id: '',
    image: null as File | null,
    priority: 'normal' as 'low' | 'normal' | 'high' | 'urgent',
    is_active: true,
    published_at: '',
    expires_at: '',
});

const imagePreview = ref<string | null>(null);

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    // Reset file input
    const input = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (input) input.value = '';
};

const submit = () => {
    form.post('/admin/announcements');
};
</script>

<template>
    <Head title="Buat Pengumuman" />

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
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Buat Pengumuman Baru</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Buat pengumuman untuk semua karyawan</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="overflow-hidden rounded-xl border border-gray-200/50 bg-white shadow-sm dark:border-gray-800/50 dark:bg-gray-950">
                <form @submit.prevent="submit" class="divide-y divide-gray-200 dark:divide-gray-800">
                    <!-- Basic Information -->
                    <div class="p-6">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Informasi Dasar</h3>

                        <div class="grid gap-6">
                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Judul Pengumuman </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Kategori </label>
                                    <select
                                        v-model="form.category_id"
                                        required
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                        :class="{ 'border-red-500': form.errors.category_id }"
                                    >
                                        <option value="">Pilih kategori...</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">
                                        {{ form.errors.category_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Prioritas </label>
                                    <select
                                        v-model="form.priority"
                                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Ringkasan (Opsional) </label>
                                <textarea
                                    v-model="form.excerpt"
                                    rows="2"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Konten Pengumuman</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Isi Pengumuman </label>
                            <textarea
                                v-model="form.content"
                                rows="12"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
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
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Gambar (Opsional)</h3>

                        <div class="space-y-4">
                            <!-- Image Preview -->
                            <div v-if="imagePreview" class="relative inline-block">
                                <img :src="imagePreview" alt="Preview" class="h-32 w-auto rounded-lg border border-gray-200 dark:border-gray-700" />
                                <button
                                    type="button"
                                    @click="removeImage"
                                    class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <!-- File Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Upload Gambar </label>
                                <div
                                    class="mt-1 flex justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-10 dark:border-gray-600"
                                >
                                    <div class="text-center">
                                        <Upload class="mx-auto h-12 w-12 text-gray-400" />
                                        <div class="mt-4">
                                            <label class="cursor-pointer">
                                                <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white"> Pilih gambar </span>
                                                <input type="file" accept="image/*" class="sr-only" @change="handleImageChange" />
                                            </label>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF hingga 2MB</p>
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
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Pengaturan Publikasi</h3>

                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Published At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Tanggal Publikasi (Opsional) </label>
                                <div class="mt-1">
                                    <DateTimePicker v-model="form.published_at" placeholder="Pilih tanggal dan waktu publikasi" />
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kosongkan untuk publish sekarang jika aktif</p>
                            </div>

                            <!-- Expires At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Tanggal Kadaluarsa (Opsional) </label>
                                <div class="mt-1">
                                    <DateTimePicker v-model="form.expires_at" placeholder="Pilih tanggal dan waktu kadaluarsa" />
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pengumuman akan disembunyikan setelah tanggal ini</p>
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
                                <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300"> Aktifkan pengumuman </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pengumuman yang aktif akan tampil di dashboard karyawan</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4 dark:bg-gray-900/50">
                        <a
                            href="/admin/announcements"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengumuman' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
