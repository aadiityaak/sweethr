<script setup lang="ts">
import { ToastProvider } from 'reka-ui'

interface Props {
  /**
   * An author-localized label for each toast. Used to help screen reader users
   * associate the interruption with a toast.
   * @defaultValue 'Notification'
   */
  label?: string
  /**
   * Time in milliseconds that each toast should remain visible for.
   * @defaultValue 5000
   */
  duration?: number
  /**
   * Direction of pointer swipe that should close the toast.
   * @defaultValue 'right'
   */
  swipeDirection?: 'right' | 'left' | 'up' | 'down'
  /**
   * Distance in pixels that the swipe must pass before a close is triggered.
   * @defaultValue 50
   */
  swipeThreshold?: number
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Notification',
  duration: 5000,
  swipeDirection: 'right',
  swipeThreshold: 50,
})
</script>

<template>
  <ToastProvider
    :label="label"
    :duration="duration"
    :swipe-direction="swipeDirection"
    :swipe-threshold="swipeThreshold"
  >
    <slot />
  </ToastProvider>
</template>