<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { AlertTriangle, CheckCircle, Info, XCircle } from 'lucide-vue-next';

interface Props {
  open: boolean;
  title: string;
  description?: string;
  confirmText?: string;
  cancelText?: string;
  variant?: 'default' | 'destructive' | 'warning' | 'success';
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  confirmText: 'Konfirmasi',
  cancelText: 'Batal',
  variant: 'default',
  loading: false,
});

const emit = defineEmits<{
  'update:open': [value: boolean];
  confirm: [];
  cancel: [];
}>();

const handleOpenChange = (open: boolean) => {
  emit('update:open', open);
  if (!open) {
    emit('cancel');
  }
};

const handleConfirm = () => {
  emit('confirm');
};

const handleCancel = () => {
  emit('cancel');
  emit('update:open', false);
};

const getIcon = () => {
  switch (props.variant) {
    case 'destructive':
      return XCircle;
    case 'warning':
      return AlertTriangle;
    case 'success':
      return CheckCircle;
    default:
      return Info;
  }
};

const getIconClass = () => {
  switch (props.variant) {
    case 'destructive':
      return 'text-red-600 dark:text-red-400';
    case 'warning':
      return 'text-yellow-600 dark:text-yellow-400';
    case 'success':
      return 'text-green-600 dark:text-green-400';
    default:
      return 'text-blue-600 dark:text-blue-400';
  }
};

const getConfirmButtonClass = () => {
  switch (props.variant) {
    case 'destructive':
      return 'bg-red-600 hover:bg-red-700';
    case 'warning':
      return 'bg-yellow-600 hover:bg-yellow-700';
    case 'success':
      return 'bg-green-600 hover:bg-green-700';
    default:
      return '';
  }
};
</script>

<template>
  <Dialog :open="open" @update:open="handleOpenChange">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <div class="flex items-center gap-3">
          <div :class="getIconClass()">
            <component :is="getIcon()" class="h-6 w-6" />
          </div>
          <DialogTitle>{{ title }}</DialogTitle>
        </div>
        <DialogDescription v-if="description">
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button
          variant="outline"
          @click="handleCancel"
          :disabled="loading"
        >
          {{ cancelText }}
        </Button>
        <Button
          @click="handleConfirm"
          :disabled="loading"
          :class="getConfirmButtonClass()"
        >
          {{ loading ? 'Memproses...' : confirmText }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>