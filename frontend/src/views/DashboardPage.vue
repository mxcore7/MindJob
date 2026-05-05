<script setup>
import { onMounted, computed, ref, watch } from 'vue'
import { useJobsStore } from '../stores/jobs'
import { useAuthStore } from '../stores/auth'
import { useAnalyticsStore } from '../stores/analytics'
import StatsCard from '../components/StatsCard.vue'
import DashboardChart from '../components/DashboardChart.vue'

const jobsStore = useJobsStore()
const authStore = useAuthStore()
const analyticsStore = useAnalyticsStore()

// État pour les analyses
const selectedPeriod = ref('2025')
const loadingAnalytics = ref(false)

onMounted(async () => {
  await jobsStore.fetchDashboardStats()
  await loadAnalytics()
})

async function loadAnalytics() {
  loadingAnalytics.value = true
  await analyticsStore.fetchLocations(selectedPeriod.value)
  await analyticsStore.fetchSalaries(selectedPeriod.value)
  await analyticsStore.fetchSkills(selectedPeriod.value)
  await analyticsStore.fetchDashboard(selectedPeriod.value)
  loadingAnalytics.value = false
}

// Watch pour changement d'année
watch(selectedPeriod, () => {
  loadAnalytics()
})

const stats = computed(() => jobsStore.dashboardStats || {
  total_jobs_available: 0,
  high_match_jobs: 0,
  medium_match_jobs: 0,
  user_skills_count: 0,
  recent_recommendations: []
})

const matchingData = computed(() => ({
  labels: ['High Match', 'Medium', 'Low Match'],
  datasets: [{
    data: [
      stats.value.high_match_jobs,
      stats.value.medium_match_jobs,
      Math.max(0, stats.value.total_jobs_available - stats.value.high_match_jobs - stats.value.medium_match_jobs)
    ],
    backgroundColor: ['#6366f1', '#06b6d4', '#334155'],
    borderWidth: 0,
    hoverOffset: 6,
  }]
}))

// Computed pour les analyses
const locationsData = computed(() => analyticsStore.locations)
const salariesData = computed(() => analyticsStore.salaries)
const skillsData = computed(() => analyticsStore.skills)
const dashboardData = computed(() => analyticsStore.dashboard)

// Préparer les données pour les graphiques
const topCitiesData = computed(() => ({
  labels: locationsData.value?.top_cities?.map(c => c.location) || [],
  datasets: [{
    label: 'Nombre d\'offres',
    data: locationsData.value?.top_cities?.map(c => c.total) || [],
    backgroundColor: '#6366f1',
    borderRadius: 8
  }]
}))

const remoteData = computed(() => ({
  labels: ['Télétravail', 'Présentiel'],
  datasets: [{
    data: [
      locationsData.value?.remote_vs_onsite?.remote || 0,
      locationsData.value?.remote_vs_onsite?.onsite || 0
    ],
    backgroundColor: ['#10b981', '#f59e0b'],
    borderWidth: 0
  }]
}))

const salaryByContractData = computed(() => ({
  labels: salariesData.value?.by_contract?.map(c => c.contract_type) || [],
  datasets: [{
    label: 'Salaire moyen (€)',
    data: salariesData.value?.by_contract?.map(c => Math.round(c.avg_salary_min)) || [],
    backgroundColor: '#8b5cf6',
    borderRadius: 8
  }]
}))

const topSkillsData = computed(() => {
  const skills = Object.entries(skillsData.value?.top_skills || {}).slice(0, 10)
  return {
    labels: skills.map(([name]) => name),
    datasets: [{
      label: 'Fréquence',
      data: skills.map(([, count]) => count),
      backgroundColor: '#ec4899',
      borderRadius: 8
    }]
  }
})
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8 animate-fade-in-up">
      <h1 class="text-2xl font-bold text-white">Dashboard</h1>
      <p class="text-slate-400 text-sm mt-1">Welcome back, <span class="text-primary-light font-medium">{{ authStore.user?.full_name }}</span>. Here's your career overview.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      <div class="animate-fade-in-up" style="animation-delay: 0.1s;">
        <StatsCard title="Total Jobs" :value="dashboardData?.summary?.total_jobs || stats.total_jobs_available" icon="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" color="secondary" :trend="`+${dashboardData?.summary?.total_jobs || 0} this period`" />
      </div>
      <div class="animate-fade-in-up" style="animation-delay: 0.2s;">
        <StatsCard title="High Matches" :value="stats.high_match_jobs" icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" color="primary" />
      </div>
      <div class="animate-fade-in-up" style="animation-delay: 0.3s;">
        <StatsCard title="Avg Salary" :value="dashboardData?.summary?.avg_salary ? dashboardData.summary.avg_salary + '€' : 'N/A'" icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" color="orange" />
      </div>
      <div class="animate-fade-in-up" style="animation-delay: 0.4s;">
        <StatsCard title="Remote Rate" :value="dashboardData?.summary?.remote_rate ? dashboardData.summary.remote_rate + '%' : 'N/A'" icon="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.66 0 3-4 3-9s-1.34-9-3-9m0 18c-1.66 0-3-4-3-9s1.34-9 3-9" color="green" />
      </div>
    </div>

    <!-- Sélecteur d'année pour analyses -->
    <div class="mb-6 flex justify-end">
      <select v-model="selectedPeriod">
  <option value="2025">📊 Année 2025 </option>
  <option value="2026_q1">📊 Février - Avril 2026</option>
</select>
    </div>

    <!-- Section Analyses -->
    <div v-if="loadingAnalytics" class="text-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
      <p class="text-slate-400 mt-4">Chargement des analyses...</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Ligne 1 : Top villes + Remote VS Onsite -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top villes -->
        <div class="glass rounded-2xl p-6">
          <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">📍 Top 10 villes</h3>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-white/10">
                  <th class="text-left py-2 text-slate-500 font-medium">Ville</th>
                  <th class="text-right py-2 text-slate-500 font-medium">Offres</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="city in locationsData?.top_cities" :key="city.location" class="border-b border-white/5">
                  <td class="py-2 text-slate-300">{{ city.location }}</td>
                  <td class="py-2 text-right text-slate-400">{{ city.total }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Remote vs Onsite -->
        <div class="glass rounded-2xl p-6">
          <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">🏠 Télétravail</h3>
          <div class="flex items-center justify-around py-4">
            <div class="text-center">
              <div class="text-3xl font-bold text-emerald-400">{{ locationsData?.remote_vs_onsite?.remote || 0 }}</div>
              <div class="text-xs text-slate-500 mt-1">Télétravail</div>
              <div class="text-xs text-slate-600">{{ Math.round((locationsData?.remote_vs_onsite?.remote / (locationsData?.remote_vs_onsite?.remote + locationsData?.remote_vs_onsite?.onsite)) * 100) || 0 }}%</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-amber-400">{{ locationsData?.remote_vs_onsite?.onsite || 0 }}</div>
              <div class="text-xs text-slate-500 mt-1">Présentiel</div>
              <div class="text-xs text-slate-600">{{ Math.round((locationsData?.remote_vs_onsite?.onsite / (locationsData?.remote_vs_onsite?.remote + locationsData?.remote_vs_onsite?.onsite)) * 100) || 0 }}%</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ligne 2 : Salaire par contrat -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">💰 Salaire moyen par contrat (€)</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-white/10">
                <th class="text-left py-2 text-slate-500 font-medium">Type de contrat</th>
                <th class="text-right py-2 text-slate-500 font-medium">Salaire min</th>
                <th class="text-right py-2 text-slate-500 font-medium">Salaire max</th>
                <th class="text-right py-2 text-slate-500 font-medium">Nb offres</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="contract in salariesData?.by_contract" :key="contract.contract_type" class="border-b border-white/5">
                <td class="py-2 text-slate-300">{{ contract.contract_type }}</td>
                <td class="py-2 text-right text-slate-400">{{ Math.round(contract.avg_salary_min).toLocaleString() }} €</td>
                <td class="py-2 text-right text-slate-400">{{ Math.round(contract.avg_salary_max).toLocaleString() }} €</td>
                <td class="py-2 text-right text-slate-500">{{ contract.count }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Ligne 3 : Top compétences -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-4">🛠️ Top 20 compétences recherchées</h3>
        <div class="flex flex-wrap gap-2">
          <span v-for="(count, skill) in skillsData?.top_skills" :key="skill" class="px-3 py-1.5 rounded-full text-xs font-medium"
            :class="{
              'bg-primary/20 text-primary-light': count > 200,
              'bg-secondary/20 text-secondary-light': count <= 200 && count > 100,
              'bg-slate-700/50 text-slate-300': count <= 100
            }">
            {{ skill }}
            <span class="ml-1 text-xs opacity-70">{{ count }}</span>
          </span>
        </div>
      </div>
    </div>

    <!-- Charts + Recommendations -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 animate-fade-in-up" style="animation-delay: 0.5s;">
      <!-- Chart -->
      <div class="lg:col-span-1">
        <DashboardChart title="Match Distribution" type="doughnut" :chartData="matchingData" />
      </div>

      <!-- Recent Matches -->
      <div class="lg:col-span-2">
        <div class="glass rounded-2xl h-full flex flex-col overflow-hidden">
          <div class="px-6 py-4 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Top Recommendations</h3>
            <RouterLink to="/jobs" class="text-xs font-medium text-primary-light hover:text-primary transition-colors">View all →</RouterLink>
          </div>
          <div class="flex-1 overflow-y-auto">
            <div v-for="(job, i) in stats.recent_recommendations" :key="job.id" class="px-6 py-4 border-b border-white/5 hover:bg-white/[0.02] cursor-pointer transition-colors group" @click="$router.push(`/jobs/${job.id}`)">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-primary-light font-bold text-sm border border-primary/10">
                    {{ job.company?.charAt(0) }}
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-slate-200 group-hover:text-white transition-colors">{{ job.title }}</h4>
                    <p class="text-xs text-slate-500 mt-0.5">{{ job.company }} · {{ job.location }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span :class="['px-2.5 py-1 rounded-lg text-xs font-bold', job.match_score >= 80 ? 'bg-emerald-500/15 text-emerald-400' : 'bg-primary/15 text-primary-light']">
                    {{ job.match_score }}%
                  </span>
                </div>
              </div>
            </div>

            <div v-if="!stats.recent_recommendations?.length" class="flex flex-col items-center justify-center py-16 text-center px-6">
              <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-primary/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
              </div>
              <p class="text-sm text-slate-400 mb-1">No recommendations yet</p>
              <p class="text-xs text-slate-600">Add skills to your profile to get started.</p>
              <RouterLink to="/profile" class="mt-4 btn-gradient px-4 py-2 rounded-lg text-xs">Update Profile</RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>