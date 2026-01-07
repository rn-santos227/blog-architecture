<template>
  <UiModal
    :is-open="isOpenValue"
    title="Login Account"
    description="Sign in to manage your blog content."
    @close="handleClose"
  >
    <form class="space-y-4" @submit.prevent="handleSubmit">
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
        placeholder="Enter your password"
        revealable
        :error-message="fieldErrors.password"
      />

      <UiDialog
        v-if="dialogState"
        :variant="dialogState.variant"
        :title="dialogState.title"
        :message="dialogState.message"
      />

      <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <UiButton type="button" variant="ghost" @click="handleClose">Cancel</UiButton>
        <UiButton type="submit" :disabled="isLoading">
          <span v-if="isLoading">Signing in...</span>
          <span v-else>Sign in</span>
        </UiButton>
      </div>
    </form>
  </UiModal>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import UiModal from '@/components/ui/UiModal.vue'
import UiTextField from '@/components/ui/UiTextField.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiDialog from '@/components/ui/UiDialog.vue'
import { useAuthStore } from '@/stores/auth'
import { loginSchema } from '@/schemas/auth'
import { useModal } from '@/providers/modal'

const authStore = useAuthStore()
const { isOpen, close } = useModal()
const router = useRouter()

const email = ref('')
const password = ref('')
const fieldErrors = ref<{ email?: string; password?: string }>({})

const dialogState = ref<null | {
  variant: 'success' | 'warning' | 'error' | 'info'
  title: string
  message: string
}>(null)

const isLoading = computed(() => authStore.isLoading)
const isOpenValue = computed(() => isOpen?.value ?? false)

const resetForm = () => {
  email.value = ''
  password.value = ''
  fieldErrors.value = {}
  dialogState.value = null
}

const handleClose = () => {
  resetForm()
  close()
}

const handleSubmit = async () => {
  const result = loginSchema.safeParse({
    email: email.value,
    password: password.value,
  })

  if (!result.success) {
    const flattened = result.error.flatten().fieldErrors
    fieldErrors.value = {
      email: flattened.email?.[0],
      password: flattened.password?.[0],
    }
    dialogState.value = {
      variant: 'warning',
      title: 'Check your details',
      message: result.error.issues[0]?.message ?? 'Please check your input.',
    }
    return
  }

  fieldErrors.value = {}
  dialogState.value = {
    variant: 'info',
    title: 'Signing you in',
    message: 'We are verifying your credentials.',
  }

  const success = await authStore.login(email.value, password.value)

  if (success) {
    dialogState.value = {
      variant: 'success',
      title: 'Signed in',
      message: 'You are now logged in.',
    }
    setTimeout(() => {
      handleClose()
      router.push('/posts/mine')
    }, 800)
  } else {
    dialogState.value = {
      variant: 'error',
      title: 'Login failed',
      message: authStore.errorMessage || 'Unable to sign in with those credentials.',
    }
  }
}
</script>
