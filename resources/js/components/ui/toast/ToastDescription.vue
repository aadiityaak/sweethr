<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ToastDescription } from 'reka-ui'

interface Props {
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  class: undefined
})
</script>

<template>
  <ToastDescription :class="cn('text-sm opacity-90', props.class)">
    <slot />
  </ToastDescription>
</template>