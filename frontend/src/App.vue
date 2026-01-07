<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import ModalProvider from './providers/ModalProvider.vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
onMounted(() => {
  authStore.loadFromStorage()
})
</script>

<template>
  <div id="app" class="min-h-screen bg-slate-50 text-slate-900">
    <ModalProvider>
      <AppHeader />
      <RouterView />
    </ModalProvider>
  </div>
</template>

<style>
/* Global app-level styles only if needed */
</style>
