import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginPage.vue'),
      meta: { guestOnly: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/auth/RegisterPage.vue'),
      meta: { guestOnly: true }
    },
    {
      path: '/',
      component: () => import('../layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('../views/DashboardPage.vue')
        },
        {
          path: 'jobs',
          name: 'jobs',
          component: () => import('../views/JobsPage.vue')
        },
        {
          path: 'jobs/:id',
          name: 'job-detail',
          component: () => import('../views/JobDetailPage.vue')
        },
        {
          path: 'applications',
          name: 'applications',
          component: () => import('../views/ApplicationsPage.vue')
        },
        {
          path: 'profile',
          name: 'profile',
          component: () => import('../views/ProfilePage.vue')
        }
      ]
    }
  ],
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuth = authStore.isAuthenticated

  if (to.meta.requiresAuth && !isAuth) {
    next({ name: 'login' })
  } else if (to.meta.guestOnly && isAuth) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
