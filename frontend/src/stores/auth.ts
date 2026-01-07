import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import type { User } from '@/@types/user'
import { loginUser, registerUser } from '@/services/auth'
import { setAuthToken } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const isLoading = ref(false)
  const errorMessage = ref('')

  const isAuthenticated = computed(() => Boolean(token.value))

  const persist = () => {
    if (token.value) {
      localStorage.setItem('auth_token', token.value)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  const setSession = (payload: { user: User; token: string }) => {
    user.value = payload.user
    token.value = payload.token
    setAuthToken(payload.token)
    persist()
  }

  const loadFromStorage = () => {
    const storedToken = localStorage.getItem('auth_token')
    if (storedToken) {
      token.value = storedToken
      setAuthToken(storedToken)
    }
  }

  const login = async (email: string, password: string) => {
    isLoading.value = true
    errorMessage.value = ''

    try {
      const payload = await loginUser(email, password)
      setSession(payload)
      return true
    } catch (error) {
      console.error(error)
      errorMessage.value = 'Unable to sign in with those credentials.'
      return false
    } finally {
      isLoading.value = false
    }
  }

  const register = async (name: string, email: string, password: string) => {
    isLoading.value = true
    errorMessage.value = ''

    try {
      const payload = await registerUser(name, email, password)
      setSession(payload)
      return true
    } catch (error) {
      console.error(error)
      errorMessage.value = 'Unable to create your account right now.'
      return false
    } finally {
      isLoading.value = false
    }
  }

  const logout = () => {
    user.value = null
    token.value = null
    setAuthToken(null)
    persist()
  }

  return {
    user,
    token,
    isLoading,
    errorMessage,
    isAuthenticated,
    loadFromStorage,
    login,
    register,
    logout,
  }
})
