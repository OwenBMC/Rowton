<template>
  <Bar :data="data" :options="options" />
</template>

<script setup lang="ts">
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js';

import { Bar } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

defineProps<{ data: any }>();

const options = {
  responsive: true,
  scales: {
    x: { stacked: true },
    y: { stacked: true },
  },
};
</script>