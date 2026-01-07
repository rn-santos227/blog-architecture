<template>
  <main class="mx-auto flex w-full max-w-4xl flex-col gap-8 px-6 py-12">
    <section class="space-y-2">
      <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Create your account</p>
      <h1 class="text-3xl font-semibold text-slate-900">Join Blog Architecture</h1>
      <p class="text-sm text-slate-500">
        Set up your profile to start publishing and managing your posts.
      </p>
    </section>

    <section class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <form class="space-y-5" @submit.prevent="handleSubmit">
        <UiTextField v-model="name" label="Full name" placeholder="Alex Writer" />
        <UiTextField v-model="email" type="email" label="Email address" placeholder="you@example.com" />
        <UiTextField v-model="password" type="password" label="Password" placeholder="Minimum 8 characters" />

        <div v-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
          {{ errorMessage }}
        </div>

        <UiButton class="w-full" type="submit" :disabled="isLoading">
          <span v-if="isLoading">Creating account...</span>
          <span v-else>Create account</span>
        </UiButton>
      </form>
    </section>
  </main>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import UiButton from '@/components/ui/UiButton.vue'
import UiTextField from '@/components/ui/UiTextField.vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')

const errorMessage = computed(() => authStore.errorMessage)
const isLoading = computed(() => authStore.isLoading)

const handleSubmit = async () => {
  const success = await authStore.register(name.value, email.value, password.value)
  if (success) {
    router.push('/')
  }
}
</script>
