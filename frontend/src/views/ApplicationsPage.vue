<script setup>
import { onMounted } from 'vue'
import { useJobsStore } from '../stores/jobs'

const jobsStore = useJobsStore()

onMounted(() => jobsStore.fetchApplications())

const statusColors = {
  applied: 'bg-primary/15 text-primary-light border-primary/20',
  interviewing: 'bg-amber-500/15 text-amber-400 border-amber-500/20',
  rejected: 'bg-red-500/15 text-red-400 border-red-500/20',
  accepted: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
}

const withdraw = async (id) => {
  if (confirm('Withdraw this application?')) {
    await jobsStore.withdrawApplication(id)
  }
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 animate-fade-in-up">
      <h1 class="text-2xl font-bold text-white">My Applications</h1>
      <p class="text-slate-400 text-sm mt-1">Track the jobs you've applied to.</p>
    </div>

    <!-- Empty -->
    <div v-if="!jobsStore.applications.length" class="glass rounded-2xl flex flex-col items-center justify-center py-20 text-center animate-fade-in-up">
      <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-primary/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <p class="text-sm text-slate-400 mb-1">No applications yet</p>
      <p class="text-xs text-slate-600">Browse jobs and click "Apply Now" to get started.</p>
      <RouterLink to="/jobs" class="mt-4 btn-gradient px-4 py-2 rounded-lg text-xs">Explore Jobs</RouterLink>
    </div>

    <!-- Applications List -->
    <div v-else class="space-y-4">
      <div v-for="(app, i) in jobsStore.applications" :key="app.id" class="glass rounded-2xl p-5 hover:bg-white/[0.03] transition-all animate-fade-in-up" :style="{ animationDelay: `${i * 0.05}s` }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4 cursor-pointer" @click="$router.push(`/jobs/${app.job?.id}`)">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-primary-light font-bold text-lg border border-primary/10 flex-shrink-0">
              {{ app.job?.company?.charAt(0) || '?' }}
            </div>
            <div>
              <h3 class="text-base font-semibold text-slate-200 hover:text-white transition-colors">{{ app.job?.title }}</h3>
              <p class="text-sm text-slate-500">{{ app.job?.company }} · {{ app.job?.location || 'Remote' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <span :class="['px-3 py-1 rounded-lg text-xs font-bold border capitalize', statusColors[app.status] || statusColors.applied]">
              {{ app.status }}
            </span>
            <span class="text-xs text-slate-600">{{ new Date(app.applied_at).toLocaleDateString() }}</span>
            <button @click="withdraw(app.id)" class="p-2 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer" title="Withdraw">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
