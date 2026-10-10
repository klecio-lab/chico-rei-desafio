import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import api, { STORAGE_KEY, clearSession } from '../lib/api'

function loadSession() {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY)) ?? {}
  } catch {
    return {}
  }
}

export const useAuthStore = defineStore('auth', () => {
  const saved = loadSession()

  const user = ref(saved.user ?? null)
  const token = ref(saved.token ?? '')
  const expiresAt = ref(saved.expires_at ?? '')

  const isAuthenticated = computed(() => Boolean(token.value))

  function persist() {
    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify({ user: user.value, token: token.value, expires_at: expiresAt.value }),
    )
  }

  function reset() {
    user.value = null
    token.value = ''
    expiresAt.value = ''
    clearSession()
  }

  // Lê o envelope { message, data: { user, token, expires_at } }.
  async function login(email, password) {
    const { data } = await api.post('/api/login', { email, password })
    user.value = data.data.user
    token.value = data.data.token
    expiresAt.value = data.data.expires_at
    persist()
  }

  async function fetchMe() {
    const { data } = await api.get('/api/me')
    user.value = data.data.user
    persist()
  }

  // Revoga no servidor, mas limpa a sessão local mesmo se a chamada falhar.
  async function logout() {
    try {
      await api.post('/api/logout')
    } finally {
      reset()
    }
  }

  return { user, token, expiresAt, isAuthenticated, login, fetchMe, logout }
})
