import { defineStore } from 'pinia'
import axios from 'axios'

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
    async fetchLocations(period = '2025') {
      try {
        const response = await axios.get(`/api/analytics/locations?period=${period}`)
        this.locations = response.data
      } catch (error) {
        console.error('Error fetching locations:', error)
        this.error = error.message
      }
    },

    async fetchSalaries(period = '2025') {
      try {
        const response = await axios.get(`/api/analytics/salaries?period=${period}`)
        this.salaries = response.data
      } catch (error) {
        console.error('Error fetching salaries:', error)
        this.error = error.message
      }
    },

    async fetchSkills(period = '2025') {
      try {
        const response = await axios.get(`/api/analytics/skills?period=${period}`)
        this.skills = response.data
      } catch (error) {
        console.error('Error fetching skills:', error)
        this.error = error.message
      }
    },

    async fetchDashboard(period = '2025') {
      try {
        const response = await axios.get(`/api/analytics/dashboard?period=${period}`)
        this.dashboard = response.data
      } catch (error) {
        console.error('Error fetching dashboard:', error)
        this.error = error.message
      }
    }
  }
})