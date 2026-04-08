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
      try {
        const response = await apiClient.get('/applications')
        this.applications = response.data
      } catch (err) {
        console.error('Failed to fetch applications', err)
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
      try {
        await apiClient.delete(`/applications/${id}`)
        this.applications = this.applications.filter(a => a.id !== id)
        return true
      } catch (err) {
        console.error('Failed to withdraw', err)
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
