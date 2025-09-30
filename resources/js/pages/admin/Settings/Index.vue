<template>
    <Head title="Admin - Pengaturan Perusahaan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Pengaturan Perusahaan</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola informasi dan branding perusahaan</p>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <form @submit.prevent="submitForm" class="w-full space-y-8">
                <!-- Branding & Identity -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ groups.branding }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Atur identitas visual dan branding perusahaan</p>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div v-for="(setting, key) in settings.branding" :key="key" class="space-y-2">
                                <label class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ setting.label }}
                                    <span v-if="setting.required" class="text-red-500">*</span>
                                </label>

                                <!-- Text Input -->
                                <Input
                                    v-if="setting.type === 'text' || setting.type === 'email' || setting.type === 'url'"
                                    :type="setting.type === 'email' ? 'email' : setting.type === 'url' ? 'url' : 'text'"
                                    v-model="form[key]"
                                    :placeholder="setting.description"
                                    class="w-full"
                                />

                                <!-- Textarea -->
                                <Textarea
                                    v-else-if="setting.type === 'textarea'"
                                    v-model="form[key]"
                                    :placeholder="setting.description"
                                    class="w-full"
                                    rows="3"
                                />

                                <!-- Color Input -->
                                <div v-else-if="setting.type === 'color'" class="flex items-center gap-3">
                                    <input type="color" v-model="form[key]" class="h-10 w-20 cursor-pointer rounded border border-input" />
                                    <Input v-model="form[key]" placeholder="#3b82f6" class="flex-1" />
                                </div>

                                <!-- Image Upload -->
                                <div v-else-if="setting.type === 'image'" class="space-y-3">
                                    <div v-if="setting.current_value" class="flex items-center gap-3">
                                        <img
                                            :src="setting.current_value"
                                            :alt="setting.label"
                                            class="h-16 w-16 rounded border bg-muted object-contain"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="deleteFile(key)"
                                            class="text-red-600 hover:bg-red-50"
                                        >
                                            <Trash class="mr-2 h-4 w-4" />
                                            Hapus
                                        </Button>
                                    </div>
                                    <input
                                        type="file"
                                        :ref="`file_${key}`"
                                        @change="handleFileChange(key, $event)"
                                        accept="image/*"
                                        class="block w-full text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                                    />
                                </div>

                                <p v-if="setting.description" class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ setting.description }}
                                </p>
                                <p v-if="errors[key]" class="text-sm text-red-600">
                                    {{ errors[key] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ groups.company_info }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Informasi kontak dan detail perusahaan</p>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div
                                v-for="(setting, key) in settings.company_info"
                                :key="key"
                                class="space-y-2"
                                :class="{ 'md:col-span-2': setting.type === 'textarea' }"
                            >
                                <label class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ setting.label }}
                                    <span v-if="setting.required" class="text-red-500">*</span>
                                </label>

                                <!-- Text Input -->
                                <Input
                                    v-if="setting.type === 'text' || setting.type === 'email' || setting.type === 'url'"
                                    :type="setting.type === 'email' ? 'email' : setting.type === 'url' ? 'url' : 'text'"
                                    v-model="form[key]"
                                    :placeholder="setting.description"
                                    class="w-full"
                                />

                                <!-- Textarea -->
                                <Textarea
                                    v-else-if="setting.type === 'textarea'"
                                    v-model="form[key]"
                                    :placeholder="setting.description"
                                    class="w-full"
                                    rows="3"
                                />

                                <p v-if="setting.description" class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ setting.description }}
                                </p>
                                <p v-if="errors[key]" class="text-sm text-red-600">
                                    {{ errors[key] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-8 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Trash } from 'lucide-vue-next';
import { reactive, ref } from 'vue';

interface SettingDefinition {
    key: string;
    type: 'text' | 'textarea' | 'email' | 'url' | 'image' | 'color';
    label: string;
    description: string;
    required: boolean;
    is_public: boolean;
    current_value: any;
    has_value: boolean;
}

interface Props {
    settings: {
        branding: Record<string, SettingDefinition>;
        company_info: Record<string, SettingDefinition>;
    };
    groups: {
        branding: string;
        company_info: string;
    };
    errors?: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Pengaturan Perusahaan',
        href: '/admin/settings',
    },
];

const { toast } = useToast();

// Initialize form data
const formData: Record<string, any> = {};
const fileInputs: Record<string, File | null> = {};

// Populate form with current values
Object.entries(props.settings).forEach(([group, groupSettings]) => {
    Object.entries(groupSettings).forEach(([key, setting]) => {
        formData[key] = setting.current_value || '';
        if (setting.type === 'image') {
            fileInputs[key] = null;
        }
    });
});

const form = reactive(formData);
const processing = ref(false);
const errors = ref<Record<string, string>>({});

const handleFileChange = (key: string, event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        fileInputs[key] = file;
    }
};

const deleteFile = (key: string) => {
    if (confirm('Yakin ingin menghapus file ini?')) {
        router.delete(`/admin/settings/file/${key}`, {
            onSuccess: () => {
                toast({
                    title: 'Berhasil!',
                    description: 'File berhasil dihapus.',
                    variant: 'success',
                });
            },
            onError: () => {
                toast({
                    title: 'Gagal!',
                    description: 'Terjadi kesalahan saat menghapus file.',
                    variant: 'destructive',
                });
            },
        });
    }
};

const submitForm = () => {
    processing.value = true;
    errors.value = {};

    const formData = new FormData();

    // Add regular form fields
    Object.entries(form).forEach(([key, value]) => {
        if (value !== null && value !== '') {
            formData.append(key, value);
        }
    });

    // Add file uploads
    Object.entries(fileInputs).forEach(([key, file]) => {
        if (file) {
            formData.append(key, file);
        }
    });

    formData.append('_method', 'PUT');

    router.post('/admin/settings', formData, {
        onSuccess: () => {
            toast({
                title: 'Berhasil!',
                description: 'Pengaturan berhasil disimpan.',
                variant: 'success',
            });
            processing.value = false;

            // Clear file inputs
            Object.keys(fileInputs).forEach((key) => {
                fileInputs[key] = null;
                const input = document.querySelector(`input[type="file"][data-key="${key}"]`) as HTMLInputElement;
                if (input) {
                    input.value = '';
                }
            });
        },
        onError: (err) => {
            errors.value = err;
            toast({
                title: 'Gagal!',
                description: 'Terjadi kesalahan saat menyimpan pengaturan.',
                variant: 'destructive',
            });
            processing.value = false;
        },
    });
};
</script>
