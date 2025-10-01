<script setup lang="ts">
import { AlertTriangle, X } from 'lucide-vue-next';

interface Props {
    show: boolean;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    type?: 'danger' | 'warning' | 'info';
    processing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Konfirmasi',
    message: 'Apakah Anda yakin ingin melanjutkan?',
    confirmText: 'Ya',
    cancelText: 'Batal',
    type: 'warning',
    processing: false,
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

const handleConfirm = () => {
    if (!props.processing) {
        emit('confirm');
    }
};

const handleCancel = () => {
    if (!props.processing) {
        emit('cancel');
    }
};

const typeStyles = {
    danger: {
        icon: 'text-red-600 dark:text-red-400',
        iconBg: 'bg-red-100 dark:bg-red-900/30',
        confirmBtn: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    },
    warning: {
        icon: 'text-amber-600 dark:text-amber-400',
        iconBg: 'bg-amber-100 dark:bg-amber-900/30',
        confirmBtn: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500',
    },
    info: {
        icon: 'text-blue-600 dark:text-blue-400',
        iconBg: 'bg-blue-100 dark:bg-blue-900/30',
        confirmBtn: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    },
};

const currentStyles = typeStyles[props.type];
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm"
        style="z-index: 9999 !important"
        @click="handleCancel"
    >
        <div
            class="relative z-[10000] w-full max-w-md rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-950"
            style="z-index: 10000 !important"
            @click.stop
        >
            <!-- Header with Icon -->
            <div class="flex items-start gap-4">
                <div :class="`flex h-12 w-12 shrink-0 items-center justify-center rounded-full ${currentStyles.iconBg}`">
                    <AlertTriangle :class="`h-6 w-6 ${currentStyles.icon}`" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        <slot name="message">
                            {{ message }}
                        </slot>
                    </div>
                </div>
                <button
                    @click="handleCancel"
                    :disabled="processing"
                    class="rounded-lg p-1 text-gray-400 hover:text-gray-600 disabled:opacity-50 dark:hover:text-gray-300"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <button
                    @click="handleCancel"
                    :disabled="processing"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    {{ cancelText }}
                </button>
                <button
                    @click="handleConfirm"
                    :disabled="processing"
                    :class="`rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors focus:ring-2 focus:ring-offset-2 focus:outline-none disabled:opacity-50 ${currentStyles.confirmBtn}`"
                >
                    <span v-if="processing">Memproses...</span>
                    <span v-else>{{ confirmText }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
