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

// Upload CV
const uploading = ref(false)
const extractedSkills = ref([])
const cvFile = ref(null)
const coverLetterFile = ref(null)

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

// Upload CV
const handleCvChange = (event) => {
  cvFile.value = event.target.files[0]
}

const handleCoverLetterChange = (event) => {
  coverLetterFile.value = event.target.files[0]
}

const uploadCv = async () => {
  if (!cvFile.value) {
    alert('Sélectionnez un fichier PDF')
    return
  }
  
  uploading.value = true
  const formData = new FormData()
  formData.append('cv', cvFile.value)
  
  try {
    const response = await apiClient.post('/upload/cv', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    extractedSkills.value = response.data.extracted_skills || []
    
    // Ajouter les compétences extraites au formulaire
    if (response.data.all_skills) {
      const newSkills = response.data.all_skills.split(', ')
      newSkills.forEach(skill => {
        if (!form.value.skills.includes(skill)) {
          form.value.skills.push(skill)
        }
      })
    }
    
    alert('CV uploadé avec succès ! ' + (extractedSkills.value.length ? `${extractedSkills.value.length} compétences extraites` : ''))
    cvFile.value = null
  } catch (error) {
    console.error(error)
    alert('Erreur lors de l\'upload du CV')
  } finally {
    uploading.value = false
  }
}

const uploadCoverLetter = async () => {
  if (!coverLetterFile.value) {
    alert('Sélectionnez un fichier PDF')
    return
  }
  
  uploading.value = true
  const formData = new FormData()
  formData.append('cover_letter', coverLetterFile.value)
  
  try {
    await apiClient.post('/upload/cover-letter', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    alert('Lettre de motivation uploadée avec succès !')
    coverLetterFile.value = null
  } catch (error) {
    console.error(error)
    alert('Erreur lors de l\'upload de la lettre de motivation')
  } finally {
    uploading.value = false
  }
}

const deleteCv = async () => {
  if (!confirm('Supprimer votre CV ?')) return
  
  try {
    await apiClient.delete('/upload/cv')
    extractedSkills.value = []
    alert('CV supprimé avec succès')
  } catch (error) {
    console.error(error)
    alert('Erreur lors de la suppression du CV')
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

      <!-- Section Upload CV -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-5 flex items-center gap-2">
          <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
          CV Upload
        </h3>
        
        <!-- Upload CV -->
        <div class="mb-4">
          <label class="block">
            <input type="file" @change="handleCvChange" accept=".pdf" class="hidden" />
            <div class="border-2 border-dashed border-white/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 transition-colors">
              <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              <p class="text-sm text-slate-400">Cliquez pour uploader votre CV (PDF)</p>
              <p class="text-xs text-slate-600 mt-1">Max 5MB</p>
              <p v-if="cvFile" class="text-xs text-primary-light mt-2">{{ cvFile.name }}</p>
            </div>
          </label>
          <button type="button" @click="uploadCv" :disabled="uploading || !cvFile" class="mt-4 w-full btn-gradient py-2 rounded-xl font-medium disabled:opacity-50">
            {{ uploading ? 'Upload...' : 'Uploader mon CV' }}
          </button>
          <button type="button" @click="deleteCv" class="mt-2 w-full bg-red-500/20 hover:bg-red-500/30 text-red-400 py-2 rounded-xl font-medium transition-colors text-sm">
            Supprimer mon CV
          </button>
        </div>
        
        <!-- Compétences extraites -->
        <div v-if="extractedSkills.length" class="mt-4 p-3 bg-primary/10 rounded-xl">
          <p class="text-xs text-primary-light mb-2">✨ Compétences extraites de votre CV :</p>
          <div class="flex flex-wrap gap-2">
            <span v-for="skill in extractedSkills" :key="skill" class="px-2 py-1 text-xs rounded-full bg-primary/20 text-primary-light">
              {{ skill }}
            </span>
          </div>
        </div>
      </div>

      <!-- Section Lettre de motivation -->
      <div class="glass rounded-2xl p-6">
        <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-5 flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
          Lettre de motivation
        </h3>
        
        <label class="block">
          <input type="file" @change="handleCoverLetterChange" accept=".pdf" class="hidden" />
          <div class="border-2 border-dashed border-white/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 transition-colors">
            <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <p class="text-sm text-slate-400">Téléchargez votre lettre de motivation (PDF)</p>
            <p class="text-xs text-slate-600 mt-1">Max 5MB</p>
            <p v-if="coverLetterFile" class="text-xs text-primary-light mt-2">{{ coverLetterFile.name }}</p>
          </div>
        </label>
        <button type="button" @click="uploadCoverLetter" :disabled="uploading || !coverLetterFile" class="mt-4 w-full bg-white/10 hover:bg-white/20 py-2 rounded-xl font-medium transition-colors disabled:opacity-50">
          {{ uploading ? 'Upload...' : 'Uploader ma lettre' }}
        </button>
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
      <div class="flex justify-end gap-3">
        <button type="submit" :disabled="loading" class="btn-gradient px-8 py-3 rounded-xl text-sm flex items-center gap-2 disabled:opacity-60 cursor-pointer">
          <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
          Save Changes
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.btn-gradient {
  background: linear-gradient(135deg, #6366f1, #06b6d4);
  transition: all 0.3s ease;
}

.btn-gradient:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.glass {
  background: rgba(15, 25, 35, 0.7);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.input-dark {
  background: rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.input-dark:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: #6366f1;
  outline: none;
}
</style>