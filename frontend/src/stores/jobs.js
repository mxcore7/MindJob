import { defineStore } from 'pinia'
import apiClient from '../api/axios'

export const useJobsStore = defineStore('jobs', {
  state: () => ({
    jobs: [],
    recommendedJobs: [],
    dashboardStats: null,
    applications: [],
    loading: false,
    error: null,
    currentPage: 1,
    totalPages: 1
  }),
  
  actions: {
    async fetchJobs(params = {}) {
      this.loading = true
      try {
        const response = await apiClient.get('/jobs', { params })
        this.jobs = response.data.data
        this.currentPage = response.data.current_page
        this.totalPages = response.data.last_page
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch jobs'
      } finally {
        this.loading = false
      }
    },
    
    async fetchRecommendedJobs() {
      try {
        const response = await apiClient.get('/recommendations')
        this.recommendedJobs = response.data
      } catch (err) {
        console.error('Failed to fetch recommendations', err)
      }
    },

    async fetchDashboardStats() {
      try {
        const response = await apiClient.get('/dashboard')
        this.dashboardStats = response.data
      } catch (err) {
        console.error('Failed to fetch dashboard stats', err)
      }
    },

    async fetchApplications() {
      console.log('🔵 Récupération des candidatures...')
      try {
        const response = await apiClient.get('/applications')
        console.log('🔵 Candidatures reçues:', response.data)
        this.applications = response.data
      } catch (err) {
        console.error('🔴 Erreur fetchApplications:', err)
      }
    },

    async applyToJob(jobId) {
      try {
        const response = await apiClient.post('/applications', { job_id: jobId })
        this.applications.unshift(response.data)
        return true
      } catch (err) {
        if (err.response?.status === 409) return 'already_applied'
        console.error('Failed to apply', err)
        return false
      }
    },

    async withdrawApplication(id) {
      console.log('🔵 Tentative suppression ID:', id)
      console.log('🔵 Token:', localStorage.getItem('auth_token'))
      
      try {
        const response = await apiClient.delete(`/applications/${id}`)
        console.log('🔵 Réponse:', response)
        console.log('🔵 Statut:', response.status)
        this.applications = this.applications.filter(a => a.id !== id)
        alert('✅ Candidature retirée avec succès !')
        return true
      } catch (err) {
        console.error('🔴 ERREUR COMPLÈTE:', err)
        console.error('🔴 Message:', err.message)
        console.error('🔴 Réponse du serveur:', err.response?.data)
        console.error('🔴 Statut HTTP:', err.response?.status)
        alert('❌ Erreur: ' + (err.response?.data?.message || err.message))
        return false
      }
    },

    isApplied(jobId) {
      return this.applications.some(a => a.job_id === jobId)
    },

    getApplication(jobId) {
      return this.applications.find(a => a.job_id === jobId)
    }
  }
})