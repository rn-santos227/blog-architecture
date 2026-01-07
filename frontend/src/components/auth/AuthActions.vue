<template>
  <div class="flex items-center gap-3">
    <UiButton variant="ghost" @click="router.push('/register')">
      Register
    </UiButton>
    <UiButton variant="secondary" @click="openLoginModal">
      Login
    </UiButton>
  </div>
  <LoginComponent />
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import UiButton from '@/components/ui/UiButton.vue'
import LoginComponent from './LoginComponent.vue'
import { useModal } from '@/providers/modal'

const router = useRouter()
const { open: openLoginModal } = useModal()
</script>
