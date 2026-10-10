import axios from 'axios'

// Chave única da sessão no localStorage (lida aqui e no store de auth).
export const STORAGE_KEY = 'chicorei.auth'

export function readToken() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return ''
    return JSON.parse(raw).token || ''
  } catch {
    return ''
  }
}

export function clearSession() {
  localStorage.removeItem(STORAGE_KEY)
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  timeout: 15000,
})

api.interceptors.request.use((config) => {
  const token = readToken()
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Sessão morta em qualquer chamada: limpa e volta ao login.
    if (error.response?.status === 401) {
      clearSession()
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  },
)

export default api
