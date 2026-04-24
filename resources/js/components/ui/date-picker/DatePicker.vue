<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { computed, ref, nextTick, onMounted, onUnmounted } from 'vue'
import { cn } from '@/lib/utils'
import { useVModel } from '@vueuse/core'
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps<{
  defaultValue?: string
  modelValue?: string
  class?: HTMLAttributes['class']
  placeholder?: string
  min?: string
  max?: string
  disabled?: boolean
  required?: boolean
  // New props for birth date optimization
  birthDate?: boolean
  defaultYear?: number
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})

const isOpen = ref(false)
const pickerRef = ref<HTMLElement>()
const calendarRef = ref<HTMLElement>()
const currentDate = ref(new Date())
const selectedDate = ref<Date | null>(null)
const popupPosition = ref<'bottom-left' | 'bottom-right' | 'top-left' | 'top-right'>('bottom-left')

// Initialize selected date from modelValue
onMounted(() => {
  if (modelValue.value) {
    selectedDate.value = new Date(modelValue.value)
    currentDate.value = new Date(modelValue.value)
  } else if (props.birthDate && props.defaultYear) {
    // For birth date picker, start with the default year (e.g., 1993)
    currentDate.value = new Date(props.defaultYear, 0, 1)
  } else if (props.birthDate) {
    // Default to 1990 for birth dates if no specific year provided
    currentDate.value = new Date(1990, 0, 1)
  }
})

// Format date for display (Indonesian style: DD/MM/YYYY)
const formatDisplayDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Format date to YYYY-MM-DD for input value
const formatInputDate = (date: Date) => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Current display value
const displayValue = computed(() => {
  return modelValue.value ? formatDisplayDate(modelValue.value) : ''
})

// Month names in Indonesian
const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

// Get calendar days for current month
const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  // Get first day of month and how many days in month
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const daysInMonth = lastDay.getDate()
  const startingDayOfWeek = firstDay.getDay()

  const days = []

  // Add empty cells for days before month starts
  for (let i = 0; i < startingDayOfWeek; i++) {
    days.push(null)
  }

  // Add days of the month
  for (let day = 1; day <= daysInMonth; day++) {
    days.push(new Date(year, month, day))
  }

  return days
})

// Navigate months
const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

// Navigate years (for birth date optimization)
const changeYear = (year: number) => {
  currentDate.value = new Date(year, currentDate.value.getMonth(), 1)
}

const changeMonth = (month: number) => {
  currentDate.value = new Date(currentDate.value.getFullYear(), month, 1)
}

// Generate year options for dropdown
const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []

  if (props.birthDate) {
    // For birth dates, show years from 1950 to current year
    for (let year = currentYear; year >= 1950; year--) {
      years.push(year)
    }
  } else {
    // For regular dates, show ±10 years from current
    for (let year = currentYear + 10; year >= currentYear - 10; year--) {
      years.push(year)
    }
  }

  return years
})

// Select a date
const selectDate = (date: Date) => {
  selectedDate.value = date
  modelValue.value = formatInputDate(date)
  isOpen.value = false
}

// Check if date is selected
const isDateSelected = (date: Date) => {
  if (!selectedDate.value) return false
  return date.toDateString() === selectedDate.value.toDateString()
}

// Check if date is today
const isToday = (date: Date) => {
  const today = new Date()
  return date.toDateString() === today.toDateString()
}

// Calculate optimal position for popup
const calculatePosition = () => {
  if (!pickerRef.value) return

  nextTick(() => {
    const rect = pickerRef.value!.getBoundingClientRect()
    const viewportWidth = window.innerWidth
    const viewportHeight = window.innerHeight
    const popupWidth = 288 // w-72 = 18rem = 288px
    const popupHeight = 320 // approximate height of calendar popup

    // Determine horizontal position
    const hasSpaceRight = rect.right + popupWidth <= viewportWidth
    const hasSpaceLeft = rect.left - popupWidth >= 0

    // Determine vertical position
    const hasSpaceBelow = rect.bottom + popupHeight <= viewportHeight
    const hasSpaceAbove = rect.top - popupHeight >= 0

    // Choose position based on available space
    if (hasSpaceBelow) {
      if (hasSpaceRight || rect.left + popupWidth <= viewportWidth) {
        popupPosition.value = 'bottom-left'
      } else {
        popupPosition.value = 'bottom-right'
      }
    } else if (hasSpaceAbove) {
      if (hasSpaceRight || rect.left + popupWidth <= viewportWidth) {
        popupPosition.value = 'top-left'
      } else {
        popupPosition.value = 'top-right'
      }
    } else {
      // Default to bottom-left if no ideal position
      popupPosition.value = 'bottom-left'
    }
  })
}

// Toggle picker
const togglePicker = () => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    calculatePosition()
  }
}

// Close picker when clicking outside
const handleClickOutside = (event: Event) => {
  if (pickerRef.value && !pickerRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

// Handle window resize to recalculate position
const handleResize = () => {
  if (isOpen.value) {
    calculatePosition()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', handleResize)
  window.addEventListener('scroll', handleResize)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('scroll', handleResize)
})
</script>

<template>
  <div ref="pickerRef" class="relative">
    <!-- Input Display -->
    <div
      @click="togglePicker"
      :class="cn(
        'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus:border-ring focus:ring-ring/50 focus:ring-[3px]',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        'flex items-center justify-between cursor-pointer',
        isOpen && 'border-ring ring-ring/50 ring-[3px]',
        props.class,
      )"
    >
      <span v-if="displayValue" class="text-foreground">
        {{ displayValue }}
      </span>
      <span v-else class="text-muted-foreground">
        {{ placeholder || 'Pilih tanggal' }}
      </span>
      <Calendar class="h-4 w-4 text-muted-foreground" />
    </div>

    <!-- Calendar Popup -->
    <div
      v-if="isOpen"
      ref="calendarRef"
      :class="cn(
        'absolute z-50 w-72 rounded-md border border-border bg-background p-3 shadow-lg',
        {
          'top-full left-0 mt-1': popupPosition === 'bottom-left',
          'top-full right-0 mt-1': popupPosition === 'bottom-right',
          'bottom-full left-0 mb-1': popupPosition === 'top-left',
          'bottom-full right-0 mb-1': popupPosition === 'top-right'
        }
      )"
    >
      <!-- Month/Year Header -->
      <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
          <button
            @click="previousMonth"
            class="p-1 hover:bg-muted rounded-md transition-colors"
            type="button"
          >
            <ChevronLeft class="h-4 w-4" />
          </button>
          <div class="font-medium text-center" v-if="!props.birthDate">
            {{ monthNames[currentDate.getMonth()] }} {{ currentDate.getFullYear() }}
          </div>
          <button
            @click="nextMonth"
            class="p-1 hover:bg-muted rounded-md transition-colors"
            type="button"
          >
            <ChevronRight class="h-4 w-4" />
          </button>
        </div>

        <!-- Quick navigation dropdowns for birth dates -->
        <div v-if="props.birthDate" class="grid grid-cols-2 gap-2">
          <!-- Month Dropdown -->
          <select
            :value="currentDate.getMonth()"
            @change="changeMonth(parseInt(($event.target as HTMLSelectElement).value))"
            class="w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
          >
            <option v-for="(month, index) in monthNames" :key="index" :value="index">
              {{ month }}
            </option>
          </select>

          <!-- Year Dropdown -->
          <select
            :value="currentDate.getFullYear()"
            @change="changeYear(parseInt(($event.target as HTMLSelectElement).value))"
            class="w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
          >
            <option v-for="year in yearOptions" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>

        <!-- Regular header for non-birth dates -->
        <div v-if="props.birthDate" class="text-center font-medium mt-2">
          {{ monthNames[currentDate.getMonth()] }} {{ currentDate.getFullYear() }}
        </div>
      </div>

      <!-- Weekday Headers -->
      <div class="grid grid-cols-7 gap-1 mb-2">
        <div v-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="day" class="text-center text-xs font-medium text-muted-foreground py-2">
          {{ day }}
        </div>
      </div>

      <!-- Calendar Days -->
      <div class="grid grid-cols-7 gap-1">
        <template v-for="(day, index) in calendarDays" :key="index">
          <div v-if="!day" class="w-8 h-8"></div>
          <button
            v-else
            @click="selectDate(day)"
            type="button"
            :class="cn(
              'w-8 h-8 text-sm rounded-md transition-colors flex items-center justify-center',
              'hover:bg-muted',
              isDateSelected(day) && 'bg-primary text-primary-foreground hover:bg-primary/90',
              isToday(day) && !isDateSelected(day) && 'bg-muted font-medium',
            )"
          >
            {{ day.getDate() }}
          </button>
        </template>
      </div>
    </div>
  </div>
</template>