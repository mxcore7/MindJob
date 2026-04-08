<script setup>
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { name: 'Jobs', href: '/jobs', icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
  { name: 'Applications', href: '/applications', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { name: 'Profile', href: '/profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
]

const isActive = (href) => route.path.startsWith(href)
</script>

<template>
  <aside class="hidden sm:flex sm:w-64 sm:flex-col sm:fixed sm:inset-y-0 z-20 bg-dark-light border-r border-white/5">
    <!-- Logo -->
    <div class="flex items-center gap-3 px-6 h-16 border-b border-white/5">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center glow-sm">
        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      </div>
      <span class="text-lg font-bold text-gradient">Job Intelligent</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto">
      <RouterLink
        v-for="item in navigation"
        :key="item.name"
        :to="item.href"
        :class="[
          'group flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
          isActive(item.href)
            ? 'bg-gradient-to-r from-primary/15 to-secondary/10 text-white border border-primary/20 glow-sm'
            : 'text-slate-400 hover:text-slate-200 hover:bg-white/5'
        ]"
      >
        <svg
          :class="[
            'w-5 h-5 transition-colors flex-shrink-0',
            isActive(item.href) ? 'text-primary-light' : 'text-slate-500 group-hover:text-slate-400'
          ]"
          fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
        </svg>
        {{ item.name }}

        <!-- Active indicator -->
        <div v-if="isActive(item.href)" class="ml-auto w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
      </RouterLink>
    </nav>

    <!-- Bottom section -->
    <div class="p-4 border-t border-white/5">
      <div class="glass-light rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
          <svg class="w-4 h-4 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          <span class="text-xs font-semibold text-slate-300">AI Matching</span>
        </div>
        <p class="text-xs text-slate-500">Your skills are matched against opportunities in real-time.</p>
      </div>
    </div>
  </aside>
</template>
