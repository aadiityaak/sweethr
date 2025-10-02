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
        presentData?: number[];
        absentData?: number[];
        lateData?: number[];
    };
    trendData?: {
        labels: string[];
        data: number[];
        presentData?: number[];
        absentData?: number[];
        lateData?: number[];
    };
    departmentData?: {
        labels: string[];
        data: number[];
    };
}

const { type = 'doughnut', attendanceData, weeklyData, monthlyData, trendData, departmentData } = defineProps<Props>();

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

// For bar chart (weekly attendance or department data)
const barData = ref({
    labels: weeklyData?.labels || departmentData?.labels || ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    datasets: [
        {
            label: weeklyData ? 'Kehadiran Mingguan' : departmentData ? 'Kehadiran per Departemen' : 'Kehadiran',
            data: weeklyData?.data || departmentData?.data || [85, 92, 88, 95, 90, 0, 0],
            backgroundColor: (context: any) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;

                if (!chartArea) {
                    return 'rgba(139, 92, 246, 0.8)';
                }

                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                gradient.addColorStop(0, 'rgba(139, 92, 246, 0.5)'); // violet-500 at bottom
                gradient.addColorStop(0.5, 'rgba(124, 58, 237, 0.7)'); // violet-600 at middle
                gradient.addColorStop(1, 'rgba(109, 40, 217, 0.9)'); // violet-700 at top
                return gradient;
            },
            borderColor: () => {
                return 'rgb(124, 58, 237)'; // violet-600
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
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0)'); // violet-500 transparent at bottom
    gradient.addColorStop(0.5, 'rgba(139, 92, 246, 0.2)'); // violet-500 20% at middle
    gradient.addColorStop(1, 'rgba(124, 58, 237, 0.4)'); // violet-600 40% at top
    return gradient;
};

// For line chart (30-day attendance trend)
const lineData = ref({
    labels: trendData?.labels || monthlyData?.labels || [],
    datasets: [
        {
            label: 'Total Kehadiran',
            data: trendData?.data || monthlyData?.data || [],
            borderColor: (context: any) => {
                const ctx = context.chart.ctx;
                const gradient = ctx.createLinearGradient(0, 0, context.chart.width, 0);
                gradient.addColorStop(0, 'rgb(139, 92, 246)'); // violet-500
                gradient.addColorStop(0.5, 'rgb(124, 58, 237)'); // violet-600
                gradient.addColorStop(1, 'rgb(109, 40, 217)'); // violet-700
                return gradient;
            },
            backgroundColor: (context: any) => {
                const chart = context.chart;
                const { ctx, chartArea } = chart;
                if (!chartArea) {
                    return 'rgba(139, 92, 246, 0.1)';
                }
                return createGradient(ctx, chartArea);
            },
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgb(255, 255, 255)',
            pointBorderColor: 'rgb(139, 92, 246)',
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: 'rgb(255, 255, 255)',
            pointHoverBorderColor: 'rgb(124, 58, 237)',
            pointHoverBorderWidth: 4,
        },
        ...(trendData?.presentData
            ? [
                  {
                      label: 'Hadir',
                      data: trendData.presentData,
                      borderColor: 'rgb(16, 185, 129)',
                      backgroundColor: 'rgba(16, 185, 129, 0.1)',
                      borderWidth: 2,
                      tension: 0.4,
                      fill: false,
                      pointBackgroundColor: 'rgb(255, 255, 255)',
                      pointBorderColor: 'rgb(16, 185, 129)',
                      pointBorderWidth: 2,
                      pointRadius: 4,
                      pointHoverRadius: 6,
                  },
              ]
            : []),
        ...(trendData?.lateData
            ? [
                  {
                      label: 'Terlambat',
                      data: trendData.lateData,
                      borderColor: 'rgb(245, 158, 11)',
                      backgroundColor: 'rgba(245, 158, 11, 0.1)',
                      borderWidth: 2,
                      tension: 0.4,
                      fill: false,
                      pointBackgroundColor: 'rgb(255, 255, 255)',
                      pointBorderColor: 'rgb(245, 158, 11)',
                      pointBorderWidth: 2,
                      pointRadius: 4,
                      pointHoverRadius: 6,
                  },
              ]
            : []),
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
                    weight: 'bold' as const,
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
                    return weeklyData ? `Kehadiran: ${context.parsed.y}%` : `Kehadiran: ${context.parsed.y}`;
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                color: 'rgba(156, 163, 175, 0.8)',
                font: {
                    size: 11,
                    weight: 500 as const,
                },
                padding: 8,
                callback: weeklyData ? (value: any) => value + '%' : undefined,
            },
            grid: {
                color: 'rgba(156, 163, 175, 0.08)',
                lineWidth: 1,
            },
            border: {
                display: false,
            },
        },
        x: {
            ticks: {
                color: 'rgba(156, 163, 175, 0.8)',
                maxTicksLimit: 8,
                font: {
                    size: 10,
                    weight: 500 as const,
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
};

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                padding: 15,
                usePointStyle: true,
                pointStyle: 'circle',
                font: {
                    size: 11,
                    weight: 500 as const,
                },
                color: 'rgb(107, 114, 128)',
            },
        },
        tooltip: {
            enabled: true,
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgb(124, 58, 237)',
            borderWidth: 2,
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
                label: (context: any) => {
                    return `${context.dataset.label}: ${context.parsed.y}${weeklyData ? '%' : ''}`;
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
            ticks: {
                color: 'rgba(156, 163, 175, 0.8)',
                font: {
                    size: 11,
                    weight: 500 as const,
                },
                padding: 8,
                callback: weeklyData ? (value: any) => value + '%' : undefined,
            },
            grid: {
                color: 'rgba(156, 163, 175, 0.08)',
                lineWidth: 1,
            },
            border: {
                display: false,
            },
        },
        x: {
            ticks: {
                color: 'rgba(156, 163, 175, 0.8)',
                maxTicksLimit: 15,
                font: {
                    size: 10,
                    weight: 500 as const,
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
            hoverBorderColor: 'rgb(124, 58, 237)',
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
    () => [attendanceData, weeklyData, monthlyData, trendData, departmentData],
    (newValues) => {
        const [newAttendanceData, newWeeklyData, newMonthlyData, newTrendData, newDepartmentData] = newValues;

        if (newAttendanceData && 'present' in newAttendanceData) {
            doughnutData.value.datasets[0].data = [newAttendanceData.present, newAttendanceData.absent, newAttendanceData.late];
        }

        if (newWeeklyData && 'labels' in newWeeklyData) {
            barData.value.labels = newWeeklyData.labels;
            barData.value.datasets[0].data = newWeeklyData.data;
            barData.value.datasets[0].label = 'Kehadiran Mingguan';
        }

        if (newDepartmentData && 'labels' in newDepartmentData) {
            barData.value.labels = newDepartmentData.labels;
            barData.value.datasets[0].data = newDepartmentData.data;
            barData.value.datasets[0].label = 'Kehadiran per Departemen';
        }

        if (newMonthlyData && 'labels' in newMonthlyData) {
            lineData.value.labels = newMonthlyData.labels;
            lineData.value.datasets[0].data = newMonthlyData.data;
        }

        if (newTrendData && 'labels' in newTrendData) {
            lineData.value.labels = newTrendData.labels;
            lineData.value.datasets[0].data = newTrendData.data;

            if ('presentData' in newTrendData && newTrendData.presentData && lineData.value.datasets[1]) {
                lineData.value.datasets[1].data = newTrendData.presentData as number[];
            }
            if ('lateData' in newTrendData && newTrendData.lateData && lineData.value.datasets[2]) {
                lineData.value.datasets[2].data = newTrendData.lateData as number[];
            }
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
