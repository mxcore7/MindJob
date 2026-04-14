<script setup>
import { ref, onMounted } from 'vue'
import { useJobsStore } from '../stores/jobs'
import JobCard from '../components/JobCard.vue'

const jobsStore = useJobsStore()
const search = ref('')
const location = ref('')
const contractType = ref('')
const source = ref('')
let searchTimeout = null

onMounted(() => jobsStore.fetchJobs())

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    jobsStore.fetchJobs({
      search: search.value,
      location: location.value,
      contract_type: contractType.value || undefined,
      source: source.value || undefined,
      page: 1,
    })
  }, 500)
}

const handleFilterChange = () => {
  jobsStore.fetchJobs({
    search: search.value,
    location: location.value,
    contract_type: contractType.value || undefined,
    source: source.value || undefined,
    page: 1,
  })
}

const handlePageChange = (page) => {
  if (page < 1 || page > jobsStore.totalPages) return
  jobsStore.fetchJobs({
    search: search.value,
    location: location.value,
    contract_type: contractType.value || undefined,
    source: source.value || undefined,
    page,
  })
}

const clearFilters = () => {
  search.value = ''
  location.value = ''
  contractType.value = ''
  source.value = ''
  handleFilterChange()
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header + Filters -->
    <div class="mb-8 flex flex-col gap-4 animate-fade-in-up">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white">Explore Jobs</h1>
          <p class="text-slate-400 text-sm mt-1">Discover career opportunities matched to your skills.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" v-model="search" @input="handleSearch" class="input-dark pl-10 pr-4 py-2.5 rounded-xl text-sm w-full sm:w-56" placeholder="Search jobs...">
          </div>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <input type="text" v-model="location" @input="handleSearch" class="input-dark pl-10 pr-4 py-2.5 rounded-xl text-sm w-full sm:w-44" placeholder="Location...">
          </div>
        </div>
      </div>

      <!-- Advanced Filters Row -->
      <div class="flex flex-wrap gap-3">
        <select v-model="contractType" @change="handleFilterChange" class="input-dark px-4 py-2.5 rounded-xl text-sm appearance-none cursor-pointer min-w-[140px]">
          <option value="">All Contracts</option>
          <option value="CDI">CDI</option>
          <option value="CDD">CDD</option>
          <option value="Intérim">Intérim</option>
          <option value="Saisonnier">Saisonnier</option>
          <option value="Full-time">Full-time</option>
          <option value="Part-time">Part-time</option>
          <option value="Contractor">Contractor</option>
          <option value="Internship">Internship</option>
        </select>
        <select v-model="source" @change="handleFilterChange" class="input-dark px-4 py-2.5 rounded-xl text-sm appearance-none cursor-pointer min-w-[150px]">
          <option value="">All Sources</option>
          <option value="France Travail">🇫🇷 France Travail</option>
          <option value="JSearch">🌐 JSearch</option>
        </select>
        <button v-if="contractType || source" @click="clearFilters" class="px-4 py-2.5 rounded-xl text-sm text-slate-400 hover:text-white bg-white/5 hover:bg-white/10 transition-colors cursor-pointer">
          ✕ Clear filters
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="jobsStore.loading" class="flex items-center justify-center py-20">
      <div class="w-10 h-10 rounded-full border-2 border-primary/20 border-t-primary animate-spin"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!jobsStore.jobs.length" class="glass rounded-2xl flex flex-col items-center justify-center py-20 text-center">
      <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-primary/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
      </div>
      <p class="text-sm text-slate-400 mb-1">No jobs found</p>
      <p class="text-xs text-slate-600">Try adjusting your search or filters.</p>
      <button @click="clearFilters" class="mt-4 btn-gradient px-4 py-2 rounded-lg text-xs cursor-pointer">Clear Filters</button>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
      <div v-for="(job, i) in jobsStore.jobs" :key="job.id" class="animate-fade-in-up" :style="{ animationDelay: `${i * 0.05}s` }">
        <JobCard :job="job" />
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="jobsStore.totalPages > 1 && !jobsStore.loading" class="flex items-center justify-center gap-2 mt-6">
      <button @click="handlePageChange(jobsStore.currentPage - 1)" :disabled="jobsStore.currentPage === 1" class="glass px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors">
        ← Prev
      </button>
      <button v-for="page in jobsStore.totalPages" :key="page" @click="handlePageChange(page)" :class="['px-3.5 py-2 rounded-lg text-sm font-medium transition-all cursor-pointer', page === jobsStore.currentPage ? 'btn-gradient' : 'glass text-slate-400 hover:text-white']">
        {{ page }}
      </button>
      <button @click="handlePageChange(jobsStore.currentPage + 1)" :disabled="jobsStore.currentPage === jobsStore.totalPages" class="glass px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors">
        Next →
      </button>
    </div>
  </div>
</template>
