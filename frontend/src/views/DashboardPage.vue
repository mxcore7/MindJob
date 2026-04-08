<script setup>
import { onMounted, computed } from 'vue'
import { useJobsStore } from '../stores/jobs'
import { useAuthStore } from '../stores/auth'
import StatsCard from '../components/StatsCard.vue'
import DashboardChart from '../components/DashboardChart.vue'

const jobsStore = useJobsStore()
const authStore = useAuthStore()

onMounted(async () => {
  await jobsStore.fetchDashboardStats()
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
        <StatsCard title="Total Jobs" :value="stats.total_jobs_available" icon="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" color="secondary" trend="+12% this week" />
      </div>
      <div class="animate-fade-in-up" style="animation-delay: 0.2s;">
        <StatsCard title="High Matches" :value="stats.high_match_jobs" icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" color="primary" />
      </div>
      <div class="animate-fade-in-up" style="animation-delay: 0.3s;">
        <StatsCard title="Your Skills" :value="stats.user_skills_count" icon="M13 10V3L4 14h7v7l9-11h-7z" color="orange" />
      </div>
      <div class="animate-fade-in-up" style="animation-delay: 0.4s;">
        <StatsCard title="Score" :value="authStore.user?.skills?.length > 0 ? '85%' : '40%'" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" color="green" trend="Profile completeness" />
      </div>
    </div>

    <!-- Charts + Recommendations -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in-up" style="animation-delay: 0.5s;">
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
            <div v-for="(job, i) in stats.recent_recommendations" :key="job.id" class="px-6 py-4 border-b border-white/5 hover:bg-white/[0.02] cursor-pointer transition-colors group" :style="{ animationDelay: `${0.6 + i * 0.1}s` }" @click="$router.push(`/jobs/${job.id}`)">
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

            <!-- Empty state -->
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
