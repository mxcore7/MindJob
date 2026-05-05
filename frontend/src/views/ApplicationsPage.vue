<script setup>
import { onMounted, ref } from 'vue'
import { useJobsStore } from '../stores/jobs'

const jobsStore = useJobsStore()
const selectedApp = ref(null)
const showDetailsModal = ref(false)
const showInterviewModal = ref(false)
const showRejectionModal = ref(false)

onMounted(() => jobsStore.fetchApplications())

const withdraw = async (id) => {
  if (confirm('Retirer cette candidature ?')) {
    await jobsStore.withdrawApplication(id)
  }
}

// Ouvrir les détails (clic sur le titre)
const openDetails = (app) => {
  console.log('🔵 Clic détecté sur:', app?.job?.title)
  selectedApp.value = app
  showDetailsModal.value = true
}

const openInterview = (app) => {
  selectedApp.value = app
  showInterviewModal.value = true
}

const openRejection = (app) => {
  selectedApp.value = app
  showRejectionModal.value = true
}

const closeModals = () => {
  showDetailsModal.value = false
  showInterviewModal.value = false
  showRejectionModal.value = false
  selectedApp.value = null
}

// Statut en français
const getStatusLabel = (status) => {
  const labels = {
    pending: { text: 'En cours', color: 'bg-amber-500/15 text-amber-400 border-amber-500/20' },
    accepted: { text: 'Acceptée', color: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20' },
    rejected: { text: 'Refusée', color: 'bg-red-500/15 text-red-400 border-red-500/20' },
    applied: { text: 'Envoyée', color: 'bg-blue-500/15 text-blue-400 border-blue-500/20' }
  }
  return labels[status] || { text: status, color: 'bg-slate-500/15 text-slate-400' }
}

// Fonction pour extraire les compétences (que ce soit string ou array)
const getSkillsList = (skills) => {
  if (!skills) return []
  if (Array.isArray(skills)) return skills.slice(0, 5)
  if (typeof skills === 'string') return skills.split(',').slice(0, 5).map(s => s.trim())
  return []
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 animate-fade-in-up">
      <h1 class="text-2xl font-bold text-white">Mes candidatures</h1>
      <p class="text-slate-400 text-sm mt-1">Suivez l'état de vos candidatures.</p>
    </div>

    <!-- Empty -->
    <div v-if="!jobsStore.applications.length" class="glass rounded-2xl flex flex-col items-center justify-center py-20 text-center animate-fade-in-up">
      <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-primary/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <p class="text-sm text-slate-400 mb-1">Aucune candidature</p>
      <p class="text-xs text-slate-600">Parcourez les offres et postulez pour commencer.</p>
      <RouterLink to="/jobs" class="mt-4 btn-gradient px-4 py-2 rounded-lg text-xs">Voir les offres</RouterLink>
    </div>

    <!-- Applications List -->
    <div v-else class="space-y-4">
      <div v-for="(app, i) in jobsStore.applications" :key="app.id" class="glass rounded-2xl p-5 hover:bg-white/[0.03] transition-all animate-fade-in-up" :style="{ animationDelay: `${i * 0.05}s` }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <!-- Clic sur le titre pour ouvrir les détails -->
          <div class="flex items-center gap-4 flex-1 cursor-pointer" @click="openDetails(app)">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-primary-light font-bold text-lg border border-primary/10 flex-shrink-0">
              {{ app.job?.company?.charAt(0) || '?' }}
            </div>
            <div class="flex-1">
              <h3 class="text-base font-semibold text-slate-200 hover:text-primary-light transition-colors">{{ app.job?.title }}</h3>
              <p class="text-sm text-slate-500">{{ app.job?.company }} · {{ app.job?.location || 'Remote' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <span :class="['px-3 py-1 rounded-lg text-xs font-bold border capitalize', getStatusLabel(app.status).color]">
              {{ getStatusLabel(app.status).text }}
            </span>
            <span class="text-xs text-slate-600">{{ new Date(app.applied_at).toLocaleDateString() }}</span>
            <button @click="withdraw(app.id)" class="p-2 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer" title="Retirer">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Bouton conditionnel selon le statut -->
        <div class="mt-4 pt-4 border-t border-white/5">
          <button 
            v-if="app.status === 'rejected'"
            @click="openRejection(app)" 
            class="px-4 py-2 text-xs font-medium rounded-lg transition-colors flex items-center gap-2 bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Voir le motif du refus
          </button>

          <button 
            v-else-if="app.status === 'accepted'"
            @click="openInterview(app)" 
            class="px-4 py-2 text-xs font-medium rounded-lg transition-colors flex items-center gap-2 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Voir les informations d'entretien
          </button>

          <div v-else class="text-xs text-slate-500 italic flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Candidature en cours de traitement
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Détails candidature -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-[99999] p-4" @click.self="closeModals">
      <div class="glass rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 animate-fade-in-up">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-white">Détails de la candidature</h3>
          <button @click="closeModals" class="text-slate-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="space-y-4">
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">Poste</p>
            <p class="text-white font-medium">{{ selectedApp?.job?.title }}</p>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">Entreprise</p>
            <p class="text-white font-medium">{{ selectedApp?.job?.company }}</p>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">Date de candidature</p>
            <p class="text-white font-medium">{{ new Date(selectedApp?.applied_at).toLocaleDateString() }}</p>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">Statut actuel</p>
            <span :class="['px-3 py-1 rounded-lg text-xs font-bold border capitalize inline-block', getStatusLabel(selectedApp?.status).color]">
              {{ getStatusLabel(selectedApp?.status).text }}
            </span>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">Description du poste</p>
            <p class="text-sm text-slate-300">{{ selectedApp?.job?.description?.substring(0, 300) }}...</p>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">Compétences requises</p>
            <div class="flex flex-wrap gap-2 mt-2">
              <span v-for="skill in getSkillsList(selectedApp?.job?.skills_required)" :key="skill" class="px-2 py-1 text-xs rounded-full bg-primary/20 text-primary-light">
                {{ skill }}
              </span>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button @click="closeModals" class="btn-gradient px-4 py-2 rounded-lg text-sm">Fermer</button>
        </div>
      </div>
    </div>

    <!-- Modal Informations entretien -->
    <div v-if="showInterviewModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeModals">
      <div class="glass rounded-2xl max-w-lg w-full p-6 animate-fade-in-up">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-white">Informations entretien</h3>
          <button @click="closeModals" class="text-slate-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="space-y-4">
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">🏢 Entreprise</p>
            <p class="text-white font-medium">{{ selectedApp?.job?.company }}</p>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">📍 Lieu de l'entretien</p>
            <p class="text-white font-medium">{{ selectedApp?.job?.location || 'En ligne (visio)' }}</p>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">📞 Téléphone à contacter</p>
            <p class="text-white font-medium">+33 1 23 45 67 89</p>
            <p class="text-xs text-slate-500 mt-1">Contacter Mme. Martin, RH</p>
          </div>
          <div class="p-4 bg-white/5 rounded-xl">
            <p class="text-sm text-slate-400">✉️ Email de contact</p>
            <p class="text-white font-medium">recrutement@{{ selectedApp?.job?.company?.toLowerCase().replace(/\s/g, '') }}.fr</p>
          </div>
          <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl">
            <p class="text-sm text-amber-400">📌 À préparer</p>
            <p class="text-xs text-slate-300 mt-1">Présentez votre parcours, vos projets techniques, et vos motivations.</p>
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button @click="closeModals" class="btn-gradient px-4 py-2 rounded-lg text-sm">Fermer</button>
        </div>
      </div>
    </div>

    <!-- Modal Refus -->
    <div v-if="showRejectionModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeModals">
      <div class="glass rounded-2xl max-w-lg w-full p-6 animate-fade-in-up">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-white">✉️ Réponse de l'entreprise</h3>
          <button @click="closeModals" class="text-slate-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-center">
          <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-400 font-medium mb-2">Candidature non retenue</p>
          <p class="text-sm text-slate-300">Nous vous remercions pour votre candidature au poste de <strong>{{ selectedApp?.job?.title }}</strong>.</p>
          <p class="text-sm text-slate-300 mt-3">Après étude approfondie de votre profil, nous avons sélectionné d'autres candidats dont les compétences correspondaient davantage au poste.</p>
          <p class="text-sm text-slate-300 mt-3">Nous vous souhaitons bonne chance dans vos recherches et vous encourageons à postuler à nouveau chez nous.</p>
          <p class="text-xs text-slate-500 mt-4">Cordialement,<br>L'équipe RH de {{ selectedApp?.job?.company }}</p>
        </div>
        <div class="mt-6 flex justify-end">
          <button @click="closeModals" class="btn-gradient px-4 py-2 rounded-lg text-sm">Compris</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.4s ease-out;
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
</style>