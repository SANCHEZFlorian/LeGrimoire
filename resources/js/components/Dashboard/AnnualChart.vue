<template>
  <div class="chart-container">
    <Bar v-if="chartData" :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
  monthlyStats: {
    type: Array,
    required: true
  },
  year: {
    type: [Number, String],
    required: true
  }
})

const chartData = computed(() => {
  return {
    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
    datasets: [
      {
        label: 'Livres lus',
        backgroundColor: '#721c24', /* Rouge enluminure */
        borderRadius: 4,
        data: props.monthlyStats
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  backgroundColor: 'transparent',
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    x: {
      grid: {
        display: false,
        drawBorder: false
      },
      ticks: {
        color: '#2c1e16',
        font: { family: 'Cormorant Garamond', size: 14 }
      }
    },
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(44, 30, 22, 0.2)', /* Lignes d'encre */
        drawBorder: false
      },
      ticks: {
        stepSize: 1,
        color: '#2c1e16',
        font: { family: 'Cormorant Garamond', size: 14 }
      }
    }
  }
}
</script>

<style scoped>
.chart-container {
  height: 300px;
  width: 100%;
}
</style>
