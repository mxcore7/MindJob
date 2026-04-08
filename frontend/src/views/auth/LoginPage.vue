<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({ email: '', password: '' })

const submitLogin = async () => {
  const success = await authStore.login(form.value)
  if (success) router.push('/dashboard')
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-dark">
    <!-- Animated background orbs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-primary/10 blur-[120px] animate-pulse"></div>
      <div class="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full bg-secondary/10 blur-[120px] animate-pulse" style="animation-delay: 1s;"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] rounded-full bg-primary/5 blur-[80px]"></div>
    </div>

    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #6366f1 1px, transparent 1px); background-size: 40px 40px;"></div>

    <div class="max-w-md w-full mx-4 relative z-10 animate-fade-in-up">
      <!-- Logo -->
      <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-secondary mb-4 glow">
          <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-gradient">Job Intelligent</h1>
        <p class="text-slate-400 mt-2 text-sm">AI-Powered Career Intelligence Platform</p>
      </div>

      <!-- Card -->
      <div class="glass rounded-2xl p-8 glow-sm">
        <h2 class="text-xl font-bold text-white mb-1">Welcome back</h2>
        <p class="text-slate-400 text-sm mb-6">Sign in to your account</p>

        <div v-if="authStore.error" class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm animate-fade-in">
          {{ authStore.error }}
        </div>

        <form @submit.prevent="submitLogin" class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
            <input id="email" type="email" required v-model="form.email" class="input-dark w-full px-4 py-3 rounded-xl text-sm" placeholder="you@example.com">
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
            <input id="password" type="password" required v-model="form.password" class="input-dark w-full px-4 py-3 rounded-xl text-sm" placeholder="••••••••">
          </div>

          <button type="submit" :disabled="authStore.loading" class="btn-gradient w-full py-3 rounded-xl text-sm flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer">
            <svg v-if="authStore.loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Sign In
          </button>
        </form>

        <div class="mt-6 text-center">
          <RouterLink to="/register" class="text-sm text-primary-light hover:text-primary transition-colors">
            Don't have an account? <span class="font-semibold">Create one</span>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
