import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApi } from '@/api/useApi'

const TOKEN_KEY = 'auth_token'

export const useAuthStore = defineStore('auth', () => {
  // Rehydrate from localStorage on store creation
  const token = ref(localStorage.getItem(TOKEN_KEY) ?? null)
  const user  = ref(null)

  // Convert "token.value" to boolean.
  const isAuthenticated = computed(() => !!token.value)

  function setSession(newToken, newUser) {
    token.value = newToken
    user.value  = newUser

    localStorage.setItem(TOKEN_KEY, newToken)
  }

  function clearSession() {
    token.value = null
    user.value  = null

    localStorage.removeItem(TOKEN_KEY)
  }

  async function fetchMe() {
    const { data, error } = await useApi().get('/auth/me')

    if (data) {
      user.value = data.user
    }

    return { data, error }
  }

  async function login(email, password, deviceName = 'web') {
    const { data, error } = await useApi().post('/auth/login', {
      email,
      password,
      device_name: deviceName,
    })

    if (data) {
      setSession(data.token, data.user)
    }

    return { data, error }
  }

  async function logout() {
    await useApi().post('/auth/logout')

    clearSession()
  }

  return {
    token,
    user,
    isAuthenticated,
    setSession,
    clearSession,
    fetchMe,
    login,
    logout,
  }
})