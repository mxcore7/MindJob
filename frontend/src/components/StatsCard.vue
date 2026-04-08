<script setup>
defineProps({
  title: String,
  value: [String, Number],
  icon: String,
  trend: { type: String, default: '' },
  color: { type: String, default: 'primary' }
})
</script>

<template>
  <div class="gradient-border rounded-2xl p-5 hover:scale-[1.02] transition-transform duration-300">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm font-medium text-slate-400 mb-1">{{ title }}</p>
        <p class="text-3xl font-bold text-white">{{ value }}</p>
        <p v-if="trend" class="text-xs mt-2 text-emerald-400 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
          {{ trend }}
        </p>
      </div>
      <div :class="[
        'w-12 h-12 rounded-xl flex items-center justify-center',
        color === 'primary' ? 'bg-primary/15 text-primary-light' : '',
        color === 'secondary' ? 'bg-secondary/15 text-secondary' : '',
        color === 'green' ? 'bg-emerald-500/15 text-emerald-400' : '',
        color === 'orange' ? 'bg-amber-500/15 text-amber-400' : ''
      ]">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
        </svg>
      </div>
    </div>
  </div>
</template>
