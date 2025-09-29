<script setup lang="ts">
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { ref, watch } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, ArcElement, Filler);

interface Props {
    type?: 'bar' | 'doughnut' | 'line';
    attendanceData?: {
        present: number;
        absent: number;
        late: number;
    };
    weeklyData?: {
        labels: string[];
        data: number[];
    };
    monthlyData?: {
        labels: string[];
        data: number[];
        presentData: number[];
        absentData: number[];
    };
}

const { type = 'doughnut', attendanceData, weeklyData, monthlyData } = defineProps<Props>();

// For doughnut chart (attendance breakdown)
const doughnutData = ref({
    labels: ['Hadir', 'Tidak Hadir', 'Terlambat'],
    datasets: [
        {
            data: [attendanceData?.present || 0, attendanceData?.absent || 0, attendanceData?.late || 0],
            backgroundColor: [
                '#10b981', // emerald-500
                '#ef4444', // red-500
                '#f59e0b', // amber-500
            ],
            borderColor: [
                '#059669', // emerald-600
                '#dc2626', // red-600
                '#d97706', // amber-600
            ],
            borderWidth: 2,
            hoverBackgroundColor: ['#059669', '#dc2626', '#d97706'],
        },
    ],
});

// For bar chart (weekly attendance)
const barData = ref({
    labels: weeklyData?.labels || ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    datasets: [
        {
            label: 'Kehadiran',
            data: weeklyData?.data || [85, 92, 88, 95, 90, 0, 0],
            backgroundColor: 'rgba(59, 130, 246, 0.8)', // blue-500 with opacity
            borderColor: 'rgb(59, 130, 246)', // blue-500
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
        },
    ],
});

// For line chart (30-day attendance)
const lineData = ref({
    labels: monthlyData?.labels || [],
    datasets: [
        {
            label: 'Tingkat Kehadiran',
            data: monthlyData?.data || [],
            borderColor: 'rgb(59, 130, 246)', // blue-500
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgb(59, 130, 246)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
        },
    ],
});

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                padding: 20,
                usePointStyle: true,
                font: {
                    size: 12,
                },
            },
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                    return `${label}: ${value} (${percentage}%)`;
                },
            },
        },
    },
    cutout: '60%',
    elements: {
        arc: {
            borderWidth: 0,
        },
    },
};

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context: any) => {
                    return `Kehadiran: ${context.parsed.y}%`;
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            max: 100,
            ticks: {
                callback: (value: any) => value + '%',
            },
            grid: {
                color: 'rgba(156, 163, 175, 0.1)', // gray-400 with opacity
            },
        },
        x: {
            grid: {
                display: false,
            },
        },
    },
};

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1,
            callbacks: {
                label: (context: any) => {
                    return `${context.parsed.y}% kehadiran`;
                },
                title: (context: any) => {
                    return context[0].label;
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            max: 100,
            ticks: {
                callback: (value: any) => value + '%',
                color: 'rgba(156, 163, 175, 0.8)',
            },
            grid: {
                color: 'rgba(156, 163, 175, 0.1)',
            },
            border: {
                display: false,
            },
        },
        x: {
            ticks: {
                color: 'rgba(156, 163, 175, 0.8)',
                maxTicksLimit: 10,
            },
            grid: {
                display: false,
            },
            border: {
                display: false,
            },
        },
    },
    interaction: {
        intersect: false,
        mode: 'index' as const,
    },
    elements: {
        point: {
            hoverBackgroundColor: 'rgb(59, 130, 246)',
            hoverBorderColor: '#fff',
        },
    },
};

// Update chart data when props change
watch(
    [attendanceData, weeklyData, monthlyData],
    () => {
        if (attendanceData) {
            doughnutData.value.datasets[0].data = [attendanceData.present, attendanceData.absent, attendanceData.late];
        }

        if (weeklyData) {
            barData.value.labels = weeklyData.labels;
            barData.value.datasets[0].data = weeklyData.data;
        }

        if (monthlyData) {
            lineData.value.labels = monthlyData.labels;
            lineData.value.datasets[0].data = monthlyData.data;
        }
    },
    { deep: true },
);
</script>

<template>
    <div class="h-full w-full">
        <Doughnut v-if="type === 'doughnut'" :data="doughnutData" :options="doughnutOptions" class="h-full w-full" />
        <Bar v-else-if="type === 'bar'" :data="barData" :options="barOptions" class="h-full w-full" />
        <Line v-else-if="type === 'line'" :data="lineData" :options="lineOptions" class="h-full w-full" />
    </div>
</template>
