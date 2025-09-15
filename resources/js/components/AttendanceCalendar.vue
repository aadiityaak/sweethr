<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronLeft, ChevronRight, Calendar as CalendarIcon } from 'lucide-vue-next';

interface AttendanceRecord {
    date: string;
    status: 'present' | 'late' | 'absent' | 'leave' | 'holiday';
    check_in_time?: string;
    check_out_time?: string;
    notes?: string;
}

interface Props {
    attendanceData: AttendanceRecord[];
    selectedMonth?: Date;
}

const props = withDefaults(defineProps<Props>(), {
    selectedMonth: () => new Date()
});

const emit = defineEmits<{
    'update:selectedMonth': [value: Date];
    'dayClick': [date: string, record?: AttendanceRecord];
}>();

const currentMonth = ref(new Date(props.selectedMonth));

// Indonesian month names
const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// Indonesian day names
const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Generate calendar days for current month
const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();


    // Get first day of month and how many days in month
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    const days = [];

    // Add empty cells for days before month starts
    for (let i = 0; i < startingDayOfWeek; i++) {
        days.push(null);
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dateString = date.toISOString().split('T')[0];
        const attendanceRecord = props.attendanceData.find(record => {
            // Handle both ISO string and plain date formats
            const recordDate = typeof record.date === 'string'
                ? record.date.split('T')[0]
                : record.date;
            return recordDate === dateString;
        });

        days.push({
            date,
            dateString,
            dayNumber: day,
            attendanceRecord,
            isToday: isToday(date),
            isWeekend: date.getDay() === 0 || date.getDay() === 6
        });
    }

    return days;
});

// Check if date is today
const isToday = (date: Date) => {
    const today = new Date();
    return date.toDateString() === today.toDateString();
};

// Navigate months
const previousMonth = () => {
    const newDate = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
    currentMonth.value = newDate;
    emit('update:selectedMonth', newDate);
};

const nextMonth = () => {
    const newDate = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
    currentMonth.value = newDate;
    emit('update:selectedMonth', newDate);
};

// Get status classes for calendar day
const getStatusClasses = (day: any) => {
    if (!day) return '';

    const baseClasses = 'relative w-full h-10 sm:h-12 flex items-center justify-center text-xs sm:text-sm font-medium rounded-md transition-all hover:scale-105 cursor-pointer';

    // Weekend styling
    if (day.isWeekend && !day.attendanceRecord) {
        return `${baseClasses} bg-muted/30 text-muted-foreground`;
    }

    // Today styling
    const todayClasses = day.isToday ? 'ring-2 ring-primary ring-offset-1 ring-offset-background' : '';

    if (day.attendanceRecord) {
        switch (day.attendanceRecord.status) {
            case 'present':
                return `${baseClasses} ${todayClasses} bg-green-500 text-white border-2 border-green-600 hover:bg-green-600 shadow-sm`;
            case 'late':
                return `${baseClasses} ${todayClasses} bg-yellow-500 text-white border-2 border-yellow-600 hover:bg-yellow-600 shadow-sm`;
            case 'leave':
            case 'absent':
                return `${baseClasses} ${todayClasses} bg-red-500 text-white border-2 border-red-600 hover:bg-red-600 shadow-sm`;
            case 'holiday':
                return `${baseClasses} ${todayClasses} bg-purple-500 text-white border-2 border-purple-600 hover:bg-purple-600 shadow-sm`;
        }
    }

    // Default (no attendance record)
    return `${baseClasses} ${todayClasses} bg-background text-foreground border border-border hover:bg-accent hover:text-accent-foreground`;
};

// Get status indicator dot
const getStatusDot = (status: string) => {
    const baseClasses = 'absolute top-0.5 right-0.5 w-2 h-2 rounded-full border border-white shadow-sm';
    switch (status) {
        case 'present': return `${baseClasses} bg-green-600`;
        case 'late': return `${baseClasses} bg-yellow-600`;
        case 'leave':
        case 'absent': return `${baseClasses} bg-red-600`;
        case 'holiday': return `${baseClasses} bg-purple-600`;
        default: return '';
    }
};

// Handle day click
const handleDayClick = (day: any) => {
    if (!day) return;
    emit('dayClick', day.dateString, day.attendanceRecord);
};

// Get status text for legend
const getStatusText = (status: string) => {
    const statusMap = {
        present: 'Hadir',
        late: 'Terlambat',
        leave: 'Cuti/Ijin',
        absent: 'Tidak Hadir',
        holiday: 'Libur'
    };
    return statusMap[status as keyof typeof statusMap] || status;
};

// Format time for tooltip
const formatTime = (time?: string) => {
    if (!time) return '--:--';
    return new Date(`2000-01-01T${time}`).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Watch for prop changes
watch(() => props.selectedMonth, (newMonth) => {
    currentMonth.value = new Date(newMonth);
});
</script>

<template>
    <div class="w-full">
        <!-- Calendar Header -->
        <div class="mb-4 sm:mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex h-6 w-6 sm:h-8 sm:w-8 items-center justify-center rounded-lg bg-primary/10 ring-1 ring-primary/20">
                    <CalendarIcon class="h-3 w-3 sm:h-4 sm:w-4 text-primary" />
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-semibold text-foreground">
                        {{ monthNames[currentMonth.getMonth()] }} {{ currentMonth.getFullYear() }}
                    </h2>
                    <p class="text-xs sm:text-sm text-muted-foreground">Riwayat kehadiran bulanan</p>
                </div>
            </div>
            <div class="flex items-center gap-1 sm:gap-2">
                <button
                    @click="previousMonth"
                    class="inline-flex items-center justify-center rounded-lg border bg-background p-1.5 sm:p-2 text-foreground hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring transition-colors"
                    type="button"
                >
                    <ChevronLeft class="h-3 w-3 sm:h-4 sm:w-4" />
                </button>
                <button
                    @click="nextMonth"
                    class="inline-flex items-center justify-center rounded-lg border bg-background p-1.5 sm:p-2 text-foreground hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring transition-colors"
                    type="button"
                >
                    <ChevronRight class="h-3 w-3 sm:h-4 sm:w-4" />
                </button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 border-b bg-muted/50">
                <div
                    v-for="day in dayNames"
                    :key="day"
                    class="flex items-center justify-center py-2 sm:py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7">
                <div
                    v-for="(day, index) in calendarDays"
                    :key="index"
                    class="relative border-r border-b border-border p-1 sm:p-2 last:border-r-0"
                    :class="[
                        (index + 1) % 7 === 0 ? 'border-r-0' : '',
                        index >= calendarDays.length - 7 ? 'border-b-0' : ''
                    ]"
                >
                    <div v-if="!day" class="h-10 sm:h-12"></div>
                    <div
                        v-else
                        @click="handleDayClick(day)"
                        :class="getStatusClasses(day)"
                        :title="day.attendanceRecord ?
                            `${getStatusText(day.attendanceRecord.status)} - Check-in: ${formatTime(day.attendanceRecord.check_in_time)} | Check-out: ${formatTime(day.attendanceRecord.check_out_time)}` :
                            'Tidak ada data kehadiran'"
                    >
                        <span class="relative z-10">{{ day.dayNumber }}</span>
                        <div
                            v-if="day.attendanceRecord"
                            :class="getStatusDot(day.attendanceRecord.status)"
                        ></div>
                        <!-- Status icon overlay -->
                        <div
                            v-if="day.attendanceRecord"
                            class="absolute bottom-0 left-0 right-0 text-center text-white text-xs font-bold opacity-90"
                        >
                            <span v-if="day.attendanceRecord.status === 'present'">✓</span>
                            <span v-if="day.attendanceRecord.status === 'late'">⚠</span>
                            <span v-if="day.attendanceRecord.status === 'absent'">✗</span>
                            <span v-if="day.attendanceRecord.status === 'leave'">📅</span>
                            <span v-if="day.attendanceRecord.status === 'holiday'">🏖</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-4 sm:mt-6 rounded-lg border bg-muted/50 p-3 sm:p-4">
            <h3 class="mb-2 sm:mb-3 text-sm font-semibold text-foreground">Keterangan Status:</h3>
            <div class="grid grid-cols-2 gap-2 sm:gap-3 sm:grid-cols-5">
                <!-- Present -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="h-3 w-3 sm:h-4 sm:w-4 rounded-full bg-green-500"></div>
                    <span class="text-xs text-foreground">Hadir</span>
                </div>
                <!-- Late -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="h-3 w-3 sm:h-4 sm:w-4 rounded-full bg-yellow-500"></div>
                    <span class="text-xs text-foreground">Terlambat</span>
                </div>
                <!-- Leave/Absent -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="h-3 w-3 sm:h-4 sm:w-4 rounded-full bg-red-500"></div>
                    <span class="text-xs text-foreground">Cuti/Ijin</span>
                </div>
                <!-- Holiday -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="h-3 w-3 sm:h-4 sm:w-4 rounded-full bg-purple-500"></div>
                    <span class="text-xs text-foreground">Libur</span>
                </div>
                <!-- No Data -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <div class="h-3 w-3 sm:h-4 sm:w-4 rounded-full bg-muted-foreground"></div>
                    <span class="text-xs text-foreground">Tidak ada data</span>
                </div>
            </div>
        </div>
    </div>
</template>