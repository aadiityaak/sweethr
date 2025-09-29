<template>
    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', statusClasses[status] || statusClasses.pending]">
        <component :is="statusIcon" class="mr-1.5 -ml-0.5 h-3 w-3" />
        {{ statusText }}
    </span>
</template>

<script setup lang="ts">
import { AlertTriangle, CheckCircle, Clock, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    status: 'pending' | 'approved' | 'rejected' | 'expired';
    isExpiringSoon?: boolean;
}

const props = defineProps<Props>();

const statusConfig = {
    pending: {
        text: 'Menunggu Persetujuan',
        icon: Clock,
        classes: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    },
    approved: {
        text: 'Disetujui',
        icon: CheckCircle,
        classes: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    },
    rejected: {
        text: 'Ditolak',
        icon: XCircle,
        classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    },
    expired: {
        text: 'Kadaluarsa',
        icon: AlertTriangle,
        classes: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    },
};

const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    approved: props.isExpiringSoon
        ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
        : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    expired: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
};

const statusText = computed(() => {
    if (props.status === 'approved' && props.isExpiringSoon) {
        return 'Akan Kadaluarsa';
    }
    return statusConfig[props.status]?.text || 'Unknown';
});

const statusIcon = computed(() => {
    if (props.status === 'approved' && props.isExpiringSoon) {
        return AlertTriangle;
    }
    return statusConfig[props.status]?.icon || Clock;
});
</script>
