<template>
  <div class="chart-container">
    <canvas class="m-auto" ref="chartCanvas"></canvas>
  </div>
</template>

<script setup lang="ts">
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
      datasets: [{
        label: props.title || 'Statistics',
        data: props.data,
        backgroundColor: props.backgroundColor || '#6C9A6C',
        borderColor: props.borderColor || '#6C9A6C',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: props.type === 'doughnut' || props.type === 'pie' ? {} : {
        y: {
          beginAtZero: true
        }
      }
    }
  });
};

onMounted(() => {
  createChart();
});

watch(() => [props.labels, props.data], () => {
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
