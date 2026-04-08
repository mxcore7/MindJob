<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { ref } from 'vue'

const authStore = useAuthStore()
const router = useRouter()
const showDropdown = ref(false)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <nav class="glass sticky top-0 z-30 border-b border-white/5">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo (mobile) -->
        <div class="flex items-center sm:hidden">
          <span class="text-lg font-bold text-gradient">JI</span>
        </div>

        <!-- Spacer -->
        <div class="flex-1"></div>

        <!-- Right side -->
        <div class="flex items-center gap-3">
          <!-- Notification bell -->
          <button class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 transition-all cursor-pointer">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-secondary rounded-full"></span>
          </button>

          <!-- User dropdown -->
          <div class="relative">
            <button @click="showDropdown = !showDropdown" class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-white/5 transition-all cursor-pointer">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-sm font-bold">
                {{ authStore.user?.full_name?.charAt(0) || 'U' }}
              </div>
              <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-slate-200">{{ authStore.user?.full_name || 'User' }}</p>
                <p class="text-xs text-slate-500">{{ authStore.user?.email }}</p>
              </div>
              <svg class="w-4 h-4 text-slate-500 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div v-if="showDropdown" @click="showDropdown = false" class="absolute right-0 mt-2 w-56 glass rounded-xl shadow-2xl py-1 border border-white/10 z-50">
                <div class="px-4 py-3 border-b border-white/5">
                  <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Account</p>
                  <p class="text-sm text-slate-200 truncate mt-1">{{ authStore.user?.email }}</p>
                </div>
                <RouterLink to="/profile" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-colors">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                  Your Profile
                </RouterLink>
                <button @click="handleLogout" class="flex items-center gap-2 w-full text-left px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-colors cursor-pointer">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                  Sign out
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
