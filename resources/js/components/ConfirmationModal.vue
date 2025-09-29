<template>
    <div v-if="open" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="handleCancel" />

            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg dark:bg-gray-900"
            >
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-900">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                            :class="iconBgClass"
                        >
                            <component :is="icon" class="h-6 w-6" :class="iconClass" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-base leading-6 font-semibold text-gray-900 dark:text-white">
                                {{ title }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ message }}
                                </p>
                                <slot />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-800">
                    <button
                        type="button"
                        class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto"
                        :class="confirmButtonClass"
                        @click="handleConfirm"
                    >
                        {{ confirmText }}
                    </button>
                    <button
                        type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-600"
                        @click="handleCancel"
                    >
                        {{ cancelText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { AlertTriangle, CheckCircle, Info } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    open?: boolean;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    confirmVariant?: 'primary' | 'danger' | 'success';
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    confirmVariant: 'primary',
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
    'update:open': [value: boolean];
}>();

const handleConfirm = () => {
    emit('confirm');
    emit('update:open', false);
};

const handleCancel = () => {
    emit('cancel');
    emit('update:open', false);
};

const icon = computed(() => {
    switch (props.confirmVariant) {
        case 'danger':
            return AlertTriangle;
        case 'success':
            return CheckCircle;
        default:
            return Info;
    }
});

const iconBgClass = computed(() => {
    switch (props.confirmVariant) {
        case 'danger':
            return 'bg-red-100 dark:bg-red-900/20';
        case 'success':
            return 'bg-green-100 dark:bg-green-900/20';
        default:
            return 'bg-blue-100 dark:bg-blue-900/20';
    }
});

const iconClass = computed(() => {
    switch (props.confirmVariant) {
        case 'danger':
            return 'text-red-600 dark:text-red-400';
        case 'success':
            return 'text-green-600 dark:text-green-400';
        default:
            return 'text-blue-600 dark:text-blue-400';
    }
});

const confirmButtonClass = computed(() => {
    switch (props.confirmVariant) {
        case 'danger':
            return 'bg-red-600 hover:bg-red-500 focus-visible:outline-red-600';
        case 'success':
            return 'bg-green-600 hover:bg-green-500 focus-visible:outline-green-600';
        default:
            return 'bg-blue-600 hover:bg-blue-500 focus-visible:outline-blue-600';
    }
});
</script>
