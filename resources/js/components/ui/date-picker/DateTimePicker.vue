<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { computed, ref } from 'vue'
import { cn } from '@/lib/utils'
import { useVModel } from '@vueuse/core'
import DatePicker from './DatePicker.vue'
import { Clock } from 'lucide-vue-next'

const props = defineProps<{
  defaultValue?: string
  modelValue?: string
  class?: HTMLAttributes['class']
  placeholder?: string
  min?: string
  max?: string
  disabled?: boolean
  required?: boolean
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

// Split datetime value into date and time parts
const dateValue = computed({
  get: () => {
    if (!modelValue.value) return ''
    return modelValue.value.split('T')[0] || ''
  },
  set: (value: string) => {
    const currentTime = timeValue.value || '00:00'
    if (value) {
      modelValue.value = `${value}T${currentTime}`
    } else {
      modelValue.value = ''
    }
  }
})

const timeValue = computed({
  get: () => {
    if (!modelValue.value) return ''
    const timePart = modelValue.value.split('T')[1]
    return timePart ? timePart.substring(0, 5) : '' // Remove seconds if present
  },
  set: (value: string) => {
    const currentDate = dateValue.value
    if (currentDate && value) {
      modelValue.value = `${currentDate}T${value}`
    } else if (currentDate) {
      modelValue.value = `${currentDate}T00:00`
    }
  }
})
</script>

<template>
  <div :class="cn('space-y-2', props.class)">
    <!-- Date Picker -->
    <div>
      <DatePicker
        v-model="dateValue"
        :placeholder="placeholder || 'Pilih tanggal'"
        :disabled="disabled"
        :required="required"
      />
    </div>

    <!-- Time Input -->
    <div class="relative">
      <input
        v-model="timeValue"
        type="time"
        :disabled="disabled || !dateValue"
        :class="cn(
          'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
          'focus:border-ring focus:ring-ring/50 focus:ring-[3px]',
          'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
          'pl-10'
        )"
      />
      <Clock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
    </div>

    <p v-if="!dateValue" class="text-xs text-muted-foreground">
      Pilih tanggal terlebih dahulu
    </p>
  </div>
</template>