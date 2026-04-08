import axios from 'axios'

// Configure Axios for Laravel Sanctum
// Assume backend is on localhost:8000
const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// Set up CSRF token interceptor for Sanctum
apiClient.interceptors.request.use(async config => {
  // We might only need this if using stateful Sanctum (cookies).
  // But since we are using plainTextToken (Bearer), we just need to set the Authorization header.
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
})

export default apiClient
