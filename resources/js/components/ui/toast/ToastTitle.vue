<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ToastTitle } from 'reka-ui'

interface Props {
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  class: undefined
})
</script>

<template>
  <ToastTitle :class="cn('text-sm font-semibold', props.class)">
    <slot />
  </ToastTitle>
</template>