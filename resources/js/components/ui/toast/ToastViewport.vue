<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ToastViewport } from 'reka-ui'

interface Props {
  class?: string
}

defineProps<Props>()
</script>

<template>
  <ToastViewport
    :class="cn(
      'fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]',
      props.class,
    )"
  />
</template>