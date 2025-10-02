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
    statusData?: {
        pending: number;
        approved: number;
        rejected: number;
    };
    trendData?: {
        labels: string[];
        data: number[];
        approvedData?: number[];
        pendingData?: number[];
        rejectedData?: number[];
    };
    departmentData?: {
        labels: string[];
        data: number[];
    };
    leaveTypeData?: {
        labels: string[];
        data: number[];
    };
}

const { type = 'doughnut', statusData, trendData, departmentData, leaveTypeData } = defineProps<Props>();

// For doughnut chart (status distribution)
const doughnutData = ref({
    labels: ['Menunggu', 'Disetujui', 'Ditolak'],
    datasets: [
        {
            data: [statusData?.pending || 0, statusData?.approved || 0, statusData?.rejected || 0],
            backgroundColor: (context: any) => {
                const chart = context.chart;
                const { ctx } = chart;

                const colors = [
                    { start: '#f59e0b', end: '#fbbf24' }, // amber gradient
                    { start: '#10b981', end: '#34d399' }, // emerald gradient
                    { start: '#ef4444', end: '#f87171' }, // red gradient
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

// For bar chart (department or leave type distribution)
const barData = ref({
    labels: departmentData?.labels || leaveTypeData?.labels || [],
    datasets: [
        {
            label: departmentData ? 'Pengajuan Cuti per Departemen' : 'Pengajuan per Tipe Cuti',
            data: departmentData?.data || leaveTypeData?.data || [],
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

// For line chart (trend data)
const lineData = ref({
    labels: trendData?.labels || [],
    datasets: [
        {
            label: 'Total Pengajuan',
            data: trendData?.data || [],
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
        ...(trendData?.approvedData ? [{
            label: 'Disetujui',
            data: trendData.approvedData,
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
        }] : []),
        ...(trendData?.pendingData ? [{
            label: 'Menunggu',
            data: trendData.pendingData,
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
        }] : []),
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
                    return `Pengajuan: ${context.parsed.y}`;
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
                    return `${context.dataset.label}: ${context.parsed.y}`;
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
    () => [statusData, trendData, departmentData, leaveTypeData],
    (newValues) => {
        const [newStatusData, newTrendData, newDepartmentData, newLeaveTypeData] = newValues;

        if (newStatusData && 'pending' in newStatusData) {
            doughnutData.value.datasets[0].data = [newStatusData.pending, newStatusData.approved, newStatusData.rejected];
        }

        if (newTrendData && 'labels' in newTrendData) {
            lineData.value.labels = newTrendData.labels;
            lineData.value.datasets[0].data = newTrendData.data;
            
            if ('approvedData' in newTrendData && newTrendData.approvedData && lineData.value.datasets[1]) {
                lineData.value.datasets[1].data = newTrendData.approvedData as number[];
            }
            if ('pendingData' in newTrendData && newTrendData.pendingData && lineData.value.datasets[2]) {
                lineData.value.datasets[2].data = newTrendData.pendingData as number[];
            }
        }

        if (newDepartmentData && 'labels' in newDepartmentData) {
            barData.value.labels = newDepartmentData.labels;
            barData.value.datasets[0].data = newDepartmentData.data;
            barData.value.datasets[0].label = 'Pengajuan Cuti per Departemen';
        }

        if (newLeaveTypeData && 'labels' in newLeaveTypeData) {
            barData.value.labels = newLeaveTypeData.labels;
            barData.value.datasets[0].data = newLeaveTypeData.data;
            barData.value.datasets[0].label = 'Pengajuan per Tipe Cuti';
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