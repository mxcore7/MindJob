<script setup>
import { computed } from 'vue'
import { Doughnut, Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

const props = defineProps({
  type: { type: String, default: 'doughnut' },
  chartData: Object,
  title: String
})

const options = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        usePointStyle: true,
        padding: 16,
        color: '#94a3b8',
        font: { family: "'Inter', sans-serif", size: 12 }
      }
    }
  },
  cutout: props.type === 'doughnut' ? '75%' : undefined,
  scales: props.type === 'bar' ? {
    x: { grid: { color: 'rgba(148, 163, 184, 0.06)' }, ticks: { color: '#64748b' } },
    y: { grid: { color: 'rgba(148, 163, 184, 0.06)' }, ticks: { color: '#64748b' } }
  } : undefined
}))
</script>

<template>
  <div class="glass rounded-2xl p-6 h-full flex flex-col">
    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">{{ title }}</h3>
    <div class="relative flex-1 min-h-[220px]">
      <Doughnut v-if="type === 'doughnut'" :data="chartData" :options="options" />
      <Bar v-else-if="type === 'bar'" :data="chartData" :options="options" />
    </div>
  </div>
</template>
