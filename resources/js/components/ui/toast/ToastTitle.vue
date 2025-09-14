<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ToastTitle } from 'reka-ui'

interface Props {
  class?: string
}

defineProps<Props>()
</script>

<template>
  <ToastTitle :class="cn('text-sm font-semibold', props.class)">
    <slot />
  </ToastTitle>
</template>