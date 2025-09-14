<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ToastRoot } from 'reka-ui'
import { toastVariants, type ToastVariants } from '.'

interface Props {
  class?: string
  /**
   * Toast variant style
   */
  variant?: 'default' | 'destructive' | 'success' | 'warning'
  /**
   * A unique value that associates the toast with a form control.
   */
  altText?: string
  /**
   * Time in milliseconds that toast should remain visible for. Overrides value given to `ToastProvider`.
   */
  duration?: number
  /**
   * Control the sensitivity of the toast for accessibility purposes.
   * For toasts that are the result of a user action, choose foreground.
   * Toasts generated from background tasks should use background.
   *
   * @defaultValue foreground
   */
  type?: 'foreground' | 'background'
}

const props = defineProps<Props>()

defineEmits<{
  escapeKeyDown: [event: KeyboardEvent]
  pause: []
  resume: []
  swipeStart: [event: { currentTarget: EventTarget & Element }]
  swipeMove: [event: { currentTarget: EventTarget & Element; delta: { x: number; y: number } }]
  swipeCancel: [event: { currentTarget: EventTarget & Element }]
  swipeEnd: [event: { currentTarget: EventTarget & Element; delta: { x: number; y: number } }]
}>()
</script>

<template>
  <ToastRoot
    v-bind="$attrs"
    :class="cn(toastVariants({ variant: props.variant }), props.class)"
    :alt-text="props.altText"
    :duration="props.duration"
    :type="props.type"
    @escape-key-down="$emit('escapeKeyDown', $event)"
    @pause="$emit('pause')"
    @resume="$emit('resume')"
    @swipe-start="$emit('swipeStart', $event)"
    @swipe-move="$emit('swipeMove', $event)"
    @swipe-cancel="$emit('swipeCancel', $event)"
    @swipe-end="$emit('swipeEnd', $event)"
  >
    <slot />
  </ToastRoot>
</template>