<template>
  <div class="chart-container">
    <canvas class="m-auto" ref="chartCanvas"></canvas>
  </div>
</template>

<script setup lang="ts">
import { getChartColors } from '@/theme/colorScheme';
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps<{
  type?: 'line' | 'bar' | 'doughnut' | 'pie';
  labels: string[];
  data: number[];
  title?: string;
  backgroundColor?: string | string[];
  borderColor?: string | string[];
  // Optional extra series for grouped bars/lines; when given, data/title/colors above are ignored
  datasets?: { label: string; data: number[]; color: string }[];
}>();

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const createChart = () => {
  if (!chartCanvas.value) return;

  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');
  if (!ctx) return;

  chartInstance = new Chart(ctx, {
    type: props.type || 'bar',
    data: {
      labels: props.labels,
      datasets: props.datasets?.length
        ? props.datasets.map(d => ({
            label: d.label,
            data: d.data,
            backgroundColor: d.color,
            borderColor: d.color,
            borderWidth: 1
          }))
        : [{
            label: props.title || 'Statistics',
            data: props.data,
            backgroundColor: props.backgroundColor || getChartColors(1)[0],
            borderColor: props.borderColor || getChartColors(1)[0],
            borderWidth: 1
          }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: !!props.datasets?.length,
          position: 'bottom'
        },
        tooltip: {
          titleFont: { size: 14 },
          bodyFont: { size: 14 }
        }
      },
      scales: props.type === 'doughnut' || props.type === 'pie' ? {} : {
        y: {
          beginAtZero: true,
          ticks: { font: { size: 13 } }
        },
        x: {
          ticks: { font: { size: 13 } }
        }
      }
    }
  });
};

onMounted(() => {
  createChart();
});

watch(() => [props.labels, props.data, props.datasets], () => {
  createChart();
}, { deep: true });
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 100%;
  width: 100%;
}
</style>
