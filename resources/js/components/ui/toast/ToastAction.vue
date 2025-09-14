<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ToastAction } from 'reka-ui'
import { buttonVariants } from '@/components/ui/button'

interface Props {
  class?: string
  /**
   * A short description for an action button for screen reader users.
   */
  altText: string
}

defineProps<Props>()
</script>

<template>
  <ToastAction
    :class="cn(
      buttonVariants({ variant: 'outline' }),
      'h-8 shrink-0 border-muted bg-transparent px-3 text-xs',
      props.class,
    )"
    :alt-text="altText"
  >
    <slot />
  </ToastAction>
</template>