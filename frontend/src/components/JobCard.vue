<script setup>
import { computed } from 'vue'

const props = defineProps({ job: Object })

const scoreClass = computed(() => {
  if (!props.job.match_score) return 'bg-slate-500/15 text-slate-400'
  if (props.job.match_score >= 80) return 'bg-emerald-500/15 text-emerald-400'
  if (props.job.match_score >= 50) return 'bg-primary/15 text-primary-light'
  return 'bg-amber-500/15 text-amber-400'
})

const contractBadge = computed(() => {
  const type = props.job.contract_type
  if (!type) return null
  const map = {
    'CDI':        { color: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20', label: 'CDI' },
    'CDD':        { color: 'bg-amber-500/15 text-amber-400 border-amber-500/20', label: 'CDD' },
    'Intérim':    { color: 'bg-blue-500/15 text-blue-400 border-blue-500/20', label: 'Intérim' },
    'Saisonnier': { color: 'bg-orange-500/15 text-orange-400 border-orange-500/20', label: 'Saisonnier' },
    'Full-time':  { color: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20', label: 'Full-time' },
    'Part-time':  { color: 'bg-violet-500/15 text-violet-400 border-violet-500/20', label: 'Part-time' },
    'Contractor': { color: 'bg-cyan-500/15 text-cyan-400 border-cyan-500/20', label: 'Contractor' },
    'Internship': { color: 'bg-pink-500/15 text-pink-400 border-pink-500/20', label: 'Internship' },
  }
  return map[type] || { color: 'bg-slate-500/15 text-slate-400 border-slate-500/20', label: type }
})

const sourceBadge = computed(() => {
  const src = props.job.source
  if (!src) return null
  if (src === 'France Travail') return { color: 'text-blue-400', icon: '🇫🇷' }
  return { color: 'text-slate-500', icon: '🌐' }
})
</script>

<template>
  <div class="glass rounded-2xl p-6 hover:bg-white/[0.04] transition-all duration-300 hover:scale-[1.01] cursor-pointer flex flex-col h-full group" @click="$router.push(`/jobs/${job.id}`)">
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-primary-light font-bold text-sm border border-primary/10 flex-shrink-0">
          {{ job.company?.charAt(0) }}
        </div>
        <div class="min-w-0">
          <h3 class="text-base font-bold text-slate-200 group-hover:text-white transition-colors line-clamp-2 break-words">{{ job.title }}</h3>
          <p class="text-sm text-primary-light font-medium">{{ job.company }}</p>
        </div>
      </div>
      <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
        <span v-if="job.match_score !== undefined" :class="['px-2.5 py-1 rounded-lg text-xs font-bold', scoreClass]">
          {{ job.match_score }}%
        </span>
        <span v-if="contractBadge" :class="['px-2 py-0.5 rounded-md text-xs font-semibold border', contractBadge.color]">
          {{ contractBadge.label }}
        </span>
      </div>
    </div>

    <p class="text-sm text-slate-400 line-clamp-2 mb-4 flex-grow">{{ job.description }}</p>

    <!-- Tags -->
    <div class="flex flex-wrap gap-1.5 mb-4">
      <span v-for="skill in (job.skills_required || []).slice(0, 3)" :key="skill" class="px-2 py-0.5 rounded-md text-xs font-medium bg-primary/10 text-primary-light border border-primary/10">
        {{ skill }}
      </span>
      <span v-if="(job.skills_required || []).length > 3" class="px-2 py-0.5 rounded-md text-xs font-medium bg-white/5 text-slate-500">
        +{{ job.skills_required.length - 3 }}
      </span>
    </div>

    <!-- Footer -->
    <div class="flex items-center gap-4 text-xs text-slate-500 mt-auto pt-4 border-t border-white/5">
      <span class="flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        {{ job.location || 'Remote' }}
      </span>
      <span v-if="job.salary" class="flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        {{ job.salary }}
      </span>
      <span v-if="sourceBadge" :class="['flex items-center gap-1 ml-auto', sourceBadge.color]">
        {{ sourceBadge.icon }} {{ job.source }}
      </span>
    </div>
  </div>
</template>
