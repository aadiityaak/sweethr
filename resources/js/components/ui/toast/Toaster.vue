<script setup lang="ts">
import {
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport,
} from '@/components/ui/toast'
import { useToast } from './use-toast'
import { watchEffect } from 'vue'

const { toasts } = useToast()

watchEffect(() => {
  console.log('Toaster component - Current toasts:', toasts.value)
})
</script>

<template>
  <ToastProvider>
    <div v-if="toasts.length > 0" style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: red; padding: 10px;">
      DEBUG: {{ toasts.length }} toasts active
    </div>
    <Toast
      v-for="toast in toasts"
      :key="toast.id"
      v-bind="toast"
      :duration="toast.duration"
      style="z-index: 9999 !important;"
    >
      <div class="grid gap-1">
        <ToastTitle v-if="toast.title">
          {{ toast.title }}
        </ToastTitle>
        <ToastDescription v-if="toast.description">
          {{ toast.description }}
        </ToastDescription>
      </div>
      <component
        :is="toast.action"
        v-if="toast.action"
      />
      <ToastClose />
    </Toast>
    <ToastViewport />
  </ToastProvider>
</template>