import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAnalyticsStore = defineStore('analytics', {
  state: () => ({
    locations: null,
    salaries: null,
    skills: null,
    dashboard: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchLocations(period = 'all') {
      try {
        const response = await api.get(`/analytics/locations?period=${period}`)
        this.locations = response.data
      } catch (error) {
        console.error('Error fetching locations:', error)
        this.error = error.message
      }
    },

    async fetchSalaries(period = 'all') {
      try {
        const response = await api.get(`/analytics/salaries?period=${period}`)
        this.salaries = response.data
      } catch (error) {
        console.error('Error fetching salaries:', error)
        this.error = error.message
      }
    },

    async fetchSkills(period = 'all') {
      try {
        const response = await api.get(`/analytics/skills?period=${period}`)
        this.skills = response.data
      } catch (error) {
        console.error('Error fetching skills:', error)
        this.error = error.message
      }
    },

    async fetchDashboard(period = 'all') {
      try {
        const response = await api.get(`/analytics/dashboard?period=${period}`)
        this.dashboard = response.data
      } catch (error) {
        console.error('Error fetching dashboard:', error)
        this.error = error.message
      }
    }
  }
})