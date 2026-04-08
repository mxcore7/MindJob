<script setup>
import { RouterView } from 'vue-router'
import Navbar from '../components/Navbar.vue'
import Sidebar from '../components/Sidebar.vue'
import { useAuthStore } from '../stores/auth'
import { onMounted } from 'vue'

const authStore = useAuthStore()

onMounted(() => {
  authStore.fetchUser()
})
</script>

<template>
  <div class="min-h-screen bg-dark">
    <Sidebar />
    <div class="sm:pl-64 flex flex-col min-h-screen">
      <Navbar />
      <main class="flex-1">
        <RouterView />
      </main>
    </div>
  </div>
</template>
