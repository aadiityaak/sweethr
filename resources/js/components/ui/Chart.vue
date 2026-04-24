<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    Filler
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    Filler
);

interface Props {
    type?: 'bar' | 'doughnut' | 'line';
    data: any;
    options?: any;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'bar',
    options: () => ({
        responsive: true,
        maintainAspectRatio: false
    })
});

const chartData = ref(props.data);
const chartOptions = ref(props.options);

// Update chart data when props change
watch(() => props.data, (newData) => {
    chartData.value = newData;
}, { deep: true });

watch(() => props.options, (newOptions) => {
    chartOptions.value = newOptions;
}, { deep: true });
</script>

<template>
    <div class="h-full w-full">
        <Doughnut
            v-if="type === 'doughnut'"
            :data="chartData"
            :options="chartOptions"
            class="h-full w-full"
        />
        <Bar
            v-else-if="type === 'bar'"
            :data="chartData"
            :options="chartOptions"
            class="h-full w-full"
        />
        <Line
            v-else-if="type === 'line'"
            :data="chartData"
            :options="chartOptions"
            class="h-full w-full"
        />
    </div>
</template>