<template>
  <div class="flex items-center gap-3">
    <template v-if="isAuthenticated">
      <UiButton variant="ghost" @click="router.push('/posts/mine')">
        My Posts
      </UiButton>
      <UiButton variant="secondary" @click="handleLogout">
        Logout
      </UiButton>
    </template>
    <template v-else>
      <UiButton variant="ghost" @click="router.push('/register')">
        Register
      </UiButton>
      <UiButton variant="secondary" @click="openLoginModal">
        Login
      </UiButton>
    </template>
  </div>
  <LoginComponent />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import UiButton from '@/components/ui/UiButton.vue'
import LoginComponent from './LoginComponent.vue'
import { useModal } from '@/providers/modal'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const { open: openLoginModal } = useModal()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)

const handleLogout = () => {
  authStore.logout()
  router.push('/')
}
</script>
