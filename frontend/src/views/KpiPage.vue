<script setup>
import { ref, onMounted } from 'vue'
import api from '../api/axios'

const kpis = ref({
  total_jobs: 0,
  jobs_by_source: [],
  jobs_by_location: [],
  top_skills: [],
  average_matching_rate: '0%',
  duplication_rate: '0%',
})

const isLoading = ref(true)

const fetchKpis = async () => {
  try {
    const { data } = await api.get('/kpis')
    kpis.value = data
  } catch (error) {
    console.error('Failed to fetch KPIs:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchKpis()
})
</script>

<template>
  <div class="space-y-6">
    <div class="mb-8 flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center glow-sm">
        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
      </div>
      <div>
        <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-light to-secondary-light">
          Tableau de Bord KPI
        </h1>
        <p class="text-slate-400 text-sm mt-1">Analyse intelligente Power BI & Statistiques d'extraction</p>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin glow-sm"></div>
    </div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <!-- Top KPIs -->
      <div class="glass-light p-6 rounded-2xl relative overflow-hidden group border border-white/5 hover:border-primary/30 transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all duration-500"></div>
        <div class="flex items-start justify-between">
            <div>
              <p class="text-slate-400 text-sm font-medium mb-1">Nombre d'offres collectées</p>
              <p class="text-4xl font-bold text-white">{{ kpis.total_jobs }}</p>
            </div>
            <div class="p-3 rounded-lg bg-primary/10 text-primary-light">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
      </div>

      <div class="glass-light p-6 rounded-2xl relative overflow-hidden group border border-white/5 hover:border-green-500/30 transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-500/10 rounded-full blur-2xl group-hover:bg-green-500/20 transition-all duration-500"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-slate-400 text-sm font-medium mb-1">Taux de matching moyen</p>
                <p class="text-4xl font-bold text-white">{{ kpis.average_matching_rate }}</p>
            </div>
            <div class="p-3 rounded-lg bg-green-500/10 text-green-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
      </div>

      <div class="glass-light p-6 rounded-2xl relative overflow-hidden group border border-white/5 hover:border-orange-500/30 transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/20 transition-all duration-500"></div>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-slate-400 text-sm font-medium mb-1">Taux de duplication</p>
                <p class="text-4xl font-bold text-white">{{ kpis.duplication_rate }}</p>
            </div>
            <div class="p-3 rounded-lg bg-orange-500/10 text-orange-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
      </div>

      <!-- Advanced Stats -->
      <div class="glass-light p-6 rounded-2xl col-span-1 md:col-span-1 lg:col-span-1 border border-white/5">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center text-primary-light">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white">Offres par Source</h3>
        </div>
        <div class="space-y-4">
          <div v-for="source in kpis.jobs_by_source" :key="source.source" class="flex justify-between items-center group">
            <span class="text-slate-300 font-medium group-hover:text-white transition-colors">{{ source.source || 'Inconnue' }}</span>
            <span class="bg-primary/20 text-primary-light px-3 py-1 rounded-lg text-xs font-bold border border-primary/20 drop-shadow-md">{{ source.count }}</span>
          </div>
          <div v-if="!kpis.jobs_by_source.length" class="text-slate-500 text-sm text-center py-4">Aucune donnée</div>
        </div>
      </div>

      <div class="glass-light p-6 rounded-2xl col-span-1 md:col-span-1 lg:col-span-1 border border-white/5">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 rounded-lg bg-secondary/20 flex items-center justify-center text-secondary-light">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white">Top Compétences</h3>
        </div>
        <div class="space-y-5">
          <div v-for="skill in kpis.top_skills" :key="skill.name" class="group">
            <div class="flex justify-between text-sm mb-2">
              <span class="text-slate-300 font-medium group-hover:text-white transition-colors">{{ skill.name }}</span>
              <span class="text-secondary-light font-bold">{{ skill.count }}</span>
            </div>
            <div class="w-full bg-slate-800/50 rounded-full h-2 border border-white/5">
              <div class="bg-gradient-to-r from-primary to-secondary h-2 rounded-full shadow-[0_0_10px_rgba(var(--color-primary),0.5)] transition-all duration-1000 ease-out" :style="{ width: Math.min((skill.count / (kpis.top_skills[0]?.count || 1)) * 100, 100) + '%' }"></div>
            </div>
          </div>
          <div v-if="!kpis.top_skills.length" class="text-slate-500 text-sm text-center py-4">Aucune donnée</div>
        </div>
      </div>

      <div class="glass-light p-6 rounded-2xl col-span-1 md:col-span-2 lg:col-span-1 border border-white/5">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white">Répartition par Lieu</h3>
        </div>
        <div class="space-y-4">
          <div v-for="(loc, index) in kpis.jobs_by_location" :key="loc.location" class="flex items-center gap-4 group">
            <div class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-sm font-bold text-slate-400 group-hover:bg-primary/20 group-hover:text-primary-light group-hover:border-primary/30 transition-all">{{ index + 1 }}</div>
            <span class="text-slate-300 flex-1 font-medium group-hover:text-white transition-colors truncate">{{ loc.location || 'Non spécifié' }}</span>
            <span class="text-white font-bold bg-white/5 px-2.5 py-1 rounded-md">{{ loc.count }}</span>
          </div>
          <div v-if="!kpis.jobs_by_location.length" class="text-slate-500 text-sm text-center py-4">Aucune donnée</div>
        </div>
      </div>

    </div>
  </div>
</template>
