<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import apiClient from '../api/axios'

const authStore = useAuthStore()

const form = ref({
  full_name: '', email: '', experience: '',
  skills: [],
  preferences: { location: '', remote: false }
})

const skillInput = ref('')
const loading = ref(false)
const successMessage = ref('')

onMounted(async () => {
  await authStore.fetchUser()
  if (authStore.user) {
    form.value = {
      full_name: authStore.user.full_name || '',
      email: authStore.user.email || '',
      experience: authStore.user.experience || '',
      skills: authStore.user.skills || [],
      preferences: authStore.user.preferences || { location: '', remote: false }
    }
  }
})

const addSkill = () => {
  const skill = skillInput.value.trim()
  if (skill && !form.value.skills.includes(skill)) {
    form.value.skills.push(skill)
  }
  skillInput.value = ''
}
const removeSkill = (index) => form.value.skills.splice(index, 1)

const saveProfile = async () => {
  loading.value = true
  successMessage.value = ''
  try {
    const response = await apiClient.put('/user', form.value)
    authStore.user = response.data
    localStorage.setItem('user', JSON.stringify(response.data))
    successMessage.value = 'Profile updated successfully!'
    setTimeout(() => { successMessage.value = '' }, 3000)
  } catch (err) {
    console.error('Failed to update profile', err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-3xl mx-auto">
    <div class="mb-8 animate-fade-in-up">
      <h1 class="text-2xl font-bold text-white">Your Profile</h1>
      <p class="text-slate-400 text-sm mt-1">Manage your skills and preferences for better recommendations.</p>
    </div>

    <!-- Success -->
    <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="successMessage" class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
        {{ successMessage }}
      </div>
    </Transition>

    <form @submit.prevent="saveProfile" class="space-y-6 animate-fade-in-up" style="animation-delay: 0.1s;">
      <!-- Personal Info -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-5 flex items-center gap-2">
          <svg class="w-4 h-4 text-primary-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
          Personal Information
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-slate-400 mb-1.5">Full Name</label>
            <input type="text" v-model="form.full_name" required class="input-dark w-full px-4 py-3 rounded-xl text-sm">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-400 mb-1.5">Email</label>
            <input type="email" v-model="form.email" disabled class="input-dark w-full px-4 py-3 rounded-xl text-sm opacity-50 cursor-not-allowed">
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-400 mb-1.5">Experience</label>
            <textarea rows="3" v-model="form.experience" class="input-dark w-full px-4 py-3 rounded-xl text-sm resize-none" placeholder="Describe your professional experience..."></textarea>
          </div>
        </div>
      </div>

      <!-- Skills -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-5 flex items-center gap-2">
          <svg class="w-4 h-4 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
          Your Skills
        </h3>
        <p class="text-xs text-slate-500 mb-4">Add skills to improve your AI-driven recommendations.</p>
        <div class="flex gap-3 mb-5">
          <input type="text" v-model="skillInput" @keydown.enter.prevent="addSkill" class="input-dark flex-1 px-4 py-2.5 rounded-xl text-sm" placeholder="e.g. Vue.js, Laravel, Python...">
          <button type="button" @click="addSkill" class="btn-gradient px-5 py-2.5 rounded-xl text-sm cursor-pointer flex-shrink-0">Add</button>
        </div>
        <div v-if="form.skills.length" class="flex flex-wrap gap-2">
          <span v-for="(skill, index) in form.skills" :key="index" class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full text-sm font-medium bg-primary/10 text-primary-light border border-primary/15 group">
            {{ skill }}
            <button type="button" @click="removeSkill(index)" class="w-4 h-4 rounded-full flex items-center justify-center text-primary/50 hover:text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer">
              <svg class="w-3 h-3" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
            </button>
          </span>
        </div>
        <p v-else class="text-sm text-slate-600 italic">No skills added yet.</p>
      </div>

      <!-- Save -->
      <div class="flex justify-end">
        <button type="submit" :disabled="loading" class="btn-gradient px-8 py-3 rounded-xl text-sm flex items-center gap-2 disabled:opacity-60 cursor-pointer">
          <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
          Save Changes
        </button>
      </div>
    </form>
  </div>
</template>
