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
            backgroundColor: (context: any) => {
                const chart = context.chart;
                const { ctx } = chart;

                const colors = [
                    { start: '#10b981', end: '#34d399' }, // emerald gradient
                    { start: '#ef4444', end: '#f87171' }, // red gradient
                    { start: '#f59e0b', end: '#fbbf24' }, // amber gradient
                ];

                const color = colors[context.dataIndex];
                if (!color) return '#10b981';

                const gradient = ctx.createLinearGradient(0, 0, 200, 200);
                gradient.addColorStop(0, color.start);
                gradient.addColorStop(1, color.end);
                return gradient;
            },
            borderColor: '#ffffff',
            borderWidth: 4,
            hoverBorderWidth: 6,
            hoverOffset: 8,
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
            backgroundColor: (context: any) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;

                if (!chartArea) {
                    return 'rgba(59, 130, 246, 0.8)';
                }

                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // blue-500 at bottom
                gradient.addColorStop(0.5, 'rgba(99, 102, 241, 0.7)'); // indigo-500 at middle
                gradient.addColorStop(1, 'rgba(139, 92, 246, 0.9)'); // violet-500 at top
                return gradient;
            },
            borderColor: (context: any) => {
                return 'rgb(99, 102, 241)'; // indigo-500
            },
            borderWidth: 0,
            borderRadius: 8,
            borderSkipped: false,
        },
    ],
});

// Create gradient for line chart
const createGradient = (ctx: CanvasRenderingContext2D, chartArea: any) => {
    const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0)'); // blue-500 transparent at bottom
    gradient.addColorStop(0.5, 'rgba(59, 130, 246, 0.2)'); // blue-500 20% at middle
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.4)'); // indigo-500 40% at top
    return gradient;
};

// For line chart (30-day attendance)
const lineData = ref({
    labels: monthlyData?.labels || [],
    datasets: [
        {
            label: 'Tingkat Kehadiran',
            data: monthlyData?.data || [],
            borderColor: (context: any) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, context.chart.width, 0);
                gradient.addColorStop(0, 'rgb(59, 130, 246)'); // blue-500
                gradient.addColorStop(0.5, 'rgb(99, 102, 241)'); // indigo-500
                gradient.addColorStop(1, 'rgb(139, 92, 246)'); // violet-500
                return gradient;
            },
            backgroundColor: (context: any) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) {
                    return 'rgba(59, 130, 246, 0.1)';
                }
                return createGradient(ctx, chartArea);
            },
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgb(255, 255, 255)',
            pointBorderColor: 'rgb(59, 130, 246)',
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: 'rgb(255, 255, 255)',
            pointHoverBorderColor: 'rgb(99, 102, 241)',
            pointHoverBorderWidth: 4,
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
                pointStyle: 'circle',
                font: {
                    size: 12,
                    weight: '500' as const,
                },
                color: 'rgb(107, 114, 128)',
            },
        },
        tooltip: {
            enabled: true,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgba(255, 255, 255, 0.2)',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
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
    cutout: '65%',
    elements: {
        arc: {
            borderWidth: 4,
            borderAlign: 'inner' as const,
        },
    },
    animation: {
        animateRotate: true,
        animateScale: true,
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
            enabled: true,
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgb(99, 102, 241)',
            borderWidth: 2,
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
                label: (context: any) => {
                    return `Kehadiran: ${context.parsed.y}%`;
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
                font: {
                    size: 11,
                    weight: '500' as const,
                },
                padding: 8,
            },
            grid: {
                color: 'rgba(156, 163, 175, 0.08)',
                lineWidth: 1,
            },
            border: {
                display: false,
                dash: [5, 5],
            },
        },
        x: {
            ticks: {
                color: 'rgba(156, 163, 175, 0.8)',
                maxTicksLimit: 15,
                font: {
                    size: 10,
                    weight: '500' as const,
                },
                padding: 8,
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
            hoverBackgroundColor: 'rgb(255, 255, 255)',
            hoverBorderColor: 'rgb(99, 102, 241)',
            hoverBorderWidth: 4,
        },
        line: {
            borderJoinStyle: 'round' as const,
        },
    },
    animation: {
        duration: 1000,
        easing: 'easeInOutQuart' as const,
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
