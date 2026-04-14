<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useJobsStore } from '../stores/jobs'
import apiClient from '../api/axios'

const route = useRoute()
const jobsStore = useJobsStore()
const job = ref(null)
const loading = ref(true)
const applying = ref(false)

const isApplied = computed(() => jobsStore.isApplied(Number(route.params.id)))

const applyToJob = async () => {
  applying.value = true
  await jobsStore.applyToJob(Number(route.params.id))
  applying.value = false
}

onMounted(async () => {
  try {
    const response = await apiClient.get(`/jobs/${route.params.id}`)
    job.value = response.data
  } catch (err) {
    console.error('Failed to fetch job', err)
  } finally {
    loading.value = false
  }
  // Also load applications to check if already applied
  if (!jobsStore.applications.length) {
    await jobsStore.fetchApplications()
  }
})
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-4xl mx-auto">
    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-10 h-10 rounded-full border-2 border-primary/20 border-t-primary animate-spin"></div>
    </div>

    <div v-else-if="job" class="animate-fade-in-up">
      <!-- Header Card -->
      <div class="glass rounded-2xl overflow-hidden glow-sm mb-6">
        <div class="bg-gradient-to-r from-primary/10 to-secondary/10 px-8 py-8 border-b border-white/5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                {{ job.company?.charAt(0) }}
              </div>
              <div>
                <h1 class="text-2xl font-bold text-white">{{ job.title }}</h1>
                <p class="text-primary-light font-medium mt-1">{{ job.company }}</p>
              </div>
            </div>
            
            <!-- Apply / Applied button -->
            <button v-if="!isApplied" @click="applyToJob" :disabled="applying" class="btn-gradient px-6 py-3 rounded-xl text-sm flex items-center gap-2 cursor-pointer self-start disabled:opacity-60">
              <svg v-if="applying" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
              {{ applying ? 'Applying...' : 'Apply Now' }}
            </button>
            <div v-else class="px-6 py-3 rounded-xl text-sm flex items-center gap-2 self-start bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
              Applied ✓
            </div>
          </div>
        </div>

        <div class="px-8 py-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Main -->
            <div class="md:col-span-2 space-y-8">
              <section>
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">Description</h3>
                <div class="text-sm text-slate-400 leading-relaxed">{{ job.description }}</div>
              </section>

              <section v-if="job.skills_required?.length">
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">Skills Required</h3>
                <div class="flex flex-wrap gap-2">
                  <span v-for="skill in job.skills_required" :key="skill" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-primary/10 text-primary-light border border-primary/10">
                    {{ skill }}
                  </span>
                </div>
              </section>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
              <div class="glass-light rounded-xl p-5 space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Details</h3>
                <div>
                  <p class="text-xs text-slate-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Location
                  </p>
                  <p class="text-sm font-medium text-slate-200 mt-1">{{ job.location || 'Not specified' }}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Salary
                  </p>
                  <p class="text-sm font-medium text-slate-200 mt-1">{{ job.salary || 'Not specified' }}</p>
                </div>
                <div v-if="job.contract_type">
                  <p class="text-xs text-slate-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Contract Type
                  </p>
                  <p class="text-sm font-medium text-slate-200 mt-1">{{ job.contract_type }}</p>
                </div>
                <div>
                  <p class="text-xs text-slate-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Source
                  </p>
                  <p class="text-sm font-medium text-slate-200 mt-1">{{ job.source || 'Internal' }}</p>
                </div>
              </div>

              <button @click="$router.back()" class="w-full glass text-center py-2.5 rounded-xl text-sm text-slate-400 hover:text-white transition-colors cursor-pointer">
                ← Back to jobs
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Not found -->
    <div v-else class="glass rounded-2xl flex flex-col items-center justify-center py-20 text-center">
      <p class="text-sm text-slate-400">Job not found.</p>
    </div>
  </div>
</template>
