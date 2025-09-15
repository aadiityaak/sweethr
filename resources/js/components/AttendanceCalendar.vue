<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronLeft, ChevronRight, Calendar } from 'lucide-vue-next';

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
        const attendanceRecord = props.attendanceData.find(record => record.date === dateString);

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

    const baseClasses = 'relative w-full h-12 flex items-center justify-center text-sm font-medium rounded-lg transition-all hover:scale-105 cursor-pointer';

    // Weekend styling
    if (day.isWeekend && !day.attendanceRecord) {
        return `${baseClasses} bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500`;
    }

    // Today styling
    const todayClasses = day.isToday ? 'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-gray-900' : '';

    if (day.attendanceRecord) {
        switch (day.attendanceRecord.status) {
            case 'present':
                return `${baseClasses} ${todayClasses} bg-emerald-100 text-emerald-800 border-2 border-emerald-200 hover:bg-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-800`;
            case 'late':
                return `${baseClasses} ${todayClasses} bg-amber-100 text-amber-800 border-2 border-amber-200 hover:bg-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800`;
            case 'leave':
            case 'absent':
                return `${baseClasses} ${todayClasses} bg-red-100 text-red-800 border-2 border-red-200 hover:bg-red-200 dark:bg-red-950 dark:text-red-300 dark:border-red-800`;
            case 'holiday':
                return `${baseClasses} ${todayClasses} bg-purple-100 text-purple-800 border-2 border-purple-200 hover:bg-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800`;
        }
    }

    // Default (no attendance record)
    return `${baseClasses} ${todayClasses} bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-800`;
};

// Get status indicator dot
const getStatusDot = (status: string) => {
    const baseClasses = 'absolute top-1 right-1 w-2 h-2 rounded-full';
    switch (status) {
        case 'present': return `${baseClasses} bg-emerald-500`;
        case 'late': return `${baseClasses} bg-amber-500`;
        case 'leave':
        case 'absent': return `${baseClasses} bg-red-500`;
        case 'holiday': return `${baseClasses} bg-purple-500`;
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
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                    <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ monthNames[currentMonth.getMonth()] }} {{ currentMonth.getFullYear() }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Riwayat kehadiran bulanan</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="previousMonth"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    type="button"
                >
                    <ChevronLeft class="h-4 w-4" />
                </button>
                <button
                    @click="nextMonth"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    type="button"
                >
                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                <div
                    v-for="day in dayNames"
                    :key="day"
                    class="flex items-center justify-center py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7">
                <div
                    v-for="(day, index) in calendarDays"
                    :key="index"
                    class="relative border-r border-b border-gray-100 p-2 dark:border-gray-800 last:border-r-0"
                    :class="[
                        (index + 1) % 7 === 0 ? 'border-r-0' : '',
                        index >= calendarDays.length - 7 ? 'border-b-0' : ''
                    ]"
                >
                    <div v-if="!day" class="h-12"></div>
                    <div
                        v-else
                        @click="handleDayClick(day)"
                        :class="getStatusClasses(day)"
                        :title="day.attendanceRecord ?
                            `${getStatusText(day.attendanceRecord.status)} - Check-in: ${formatTime(day.attendanceRecord.check_in_time)} | Check-out: ${formatTime(day.attendanceRecord.check_out_time)}` :
                            'Tidak ada data kehadiran'"
                    >
                        {{ day.dayNumber }}
                        <div
                            v-if="day.attendanceRecord"
                            :class="getStatusDot(day.attendanceRecord.status)"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
            <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Keterangan Status:</h3>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                <!-- Present -->
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded-full bg-emerald-500"></div>
                    <span class="text-xs text-gray-700 dark:text-gray-300">Hadir</span>
                </div>
                <!-- Late -->
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded-full bg-amber-500"></div>
                    <span class="text-xs text-gray-700 dark:text-gray-300">Terlambat</span>
                </div>
                <!-- Leave/Absent -->
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded-full bg-red-500"></div>
                    <span class="text-xs text-gray-700 dark:text-gray-300">Cuti/Ijin</span>
                </div>
                <!-- Holiday -->
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded-full bg-purple-500"></div>
                    <span class="text-xs text-gray-700 dark:text-gray-300">Libur</span>
                </div>
                <!-- No Data -->
                <div class="flex items-center gap-2">
                    <div class="h-4 w-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <span class="text-xs text-gray-700 dark:text-gray-300">Tidak ada data</span>
                </div>
            </div>
        </div>
    </div>
</template>