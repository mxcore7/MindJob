import { defineStore } from 'pinia'
import apiClient from '../api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  
  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await apiClient.post('/login', credentials)
        this.token = response.data.access_token
        this.user = response.data.user
        localStorage.setItem('auth_token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
        return true
      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed'
        return false
      } finally {
        this.loading = false
      }
    },
    
    async register(data) {
      this.loading = true
      this.error = null
      try {
        const response = await apiClient.post('/register', data)
        this.token = response.data.access_token
        this.user = response.data.user
        localStorage.setItem('auth_token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
        return true
      } catch (err) {
        this.error = err.response?.data?.message || 'Registration failed'
        return false
      } finally {
        this.loading = false
      }
    },
    
    async logout() {
      try {
        if (this.token) {
          await apiClient.post('/logout')
        }
      } catch (e) {
        console.error('Logout error', e)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      }
    },

    async fetchUser() {
      if (!this.token) return
      try {
        const response = await apiClient.get('/user')
        this.user = response.data
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch (error) {
        if (error.response?.status === 401) {
          this.logout()
        }
      }
    },
    
    init() {
      const storedUser = localStorage.getItem('user')
      if (storedUser && storedUser !== 'undefined') {
        try {
          this.user = JSON.parse(storedUser)
        } catch (e) {
            localStorage.removeItem('user')
        }
      }
    }
  }
})
