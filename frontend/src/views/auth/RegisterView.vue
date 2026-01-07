<template>
  <main class="mx-auto flex w-full max-w-4xl flex-col items-center gap-8 px-6 py-12">
    <section class="space-y-2 text-center">
      <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Create your account</p>
      <h1 class="text-3xl font-semibold text-slate-900">Join Blog Architecture</h1>
      <p class="text-sm text-slate-500">
        Set up your profile to start publishing and managing your posts.
      </p>
    </section>

    <section class="w-full max-w-xl">
      <UiCard>
        <template #title>Registration Details</template>
        <form class="space-y-5" @submit.prevent="handleSubmit">
          <UiTextField
            v-model="name"
            label="Full name"
            placeholder="Alex Writer"
            :error-message="fieldErrors.name"
          />
          <UiTextField
            v-model="email"
            type="email"
            label="Email address"
            placeholder="you@example.com"
            :error-message="fieldErrors.email"
          />
          <UiTextField
            v-model="password"
            type="password"
            label="Password"
            placeholder="Minimum 8 characters"
            revealable
            :error-message="fieldErrors.password"
          />
          <UiTextField
            v-model="confirmPassword"
            type="password"
            label="Confirm password"
            placeholder="Re-enter your password"
            revealable
            :error-message="fieldErrors.confirmPassword"
          />

          <div
            v-if="validationError || errorMessage"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700"
          >
            {{ validationError || errorMessage }}
          </div>
        </form>
        <template #footer>
          <UiButton class="w-full" type="submit" :disabled="isLoading">
            <span v-if="isLoading">Creating Account...</span>
            <span v-else>Create Account</span>
          </UiButton>
        </template>
      </UiCard>
    </section>
  </main>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import UiButton from '@/components/ui/UiButton.vue'
import UiCard from '@/components/ui/UiCard.vue'
import UiTextField from '@/components/ui/UiTextField.vue'
import { useAuthStore } from '@/stores/auth'
import { registerSchema } from '@/schemas/auth'

const authStore = useAuthStore()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')

const errorMessage = computed(() => authStore.errorMessage)
const isLoading = computed(() => authStore.isLoading)
const validationError = ref('')
const fieldErrors = ref<{
  name?: string
  email?: string
  password?: string
  confirmPassword?: string
}>({})

const handleSubmit = async () => {
  const result = registerSchema.safeParse({
    name: name.value,
    email: email.value,
    password: password.value,
    confirmPassword: confirmPassword.value,
  })

  if (!result.success) {
    const nextErrors: {
      name?: string
      email?: string
      password?: string
      confirmPassword?: string
    } = {}
    for (const issue of result.error.issues) {
      const field = issue.path[0]
      if (field === 'name' && !nextErrors.name) {
        nextErrors.name = issue.message
      }
      if (field === 'email' && !nextErrors.email) {
        nextErrors.email = issue.message
      }
      if (field === 'password' && !nextErrors.password) {
        nextErrors.password = issue.message
      }
      if (field === 'confirmPassword' && !nextErrors.confirmPassword) {
        nextErrors.confirmPassword = issue.message
      }
    }
    fieldErrors.value = nextErrors
    validationError.value = result.error.issues[0]?.message ?? 'Please check your input.'
    return
  }

  validationError.value = ''
  fieldErrors.value = {}
  const success = await authStore.register(name.value, email.value, password.value)
  if (success) {
    router.push('/')
  }
}
</script>
