import api from './api'

const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

// Trava nº 1 do handoff (fatia 2 back): price serializa STRING ("10.00").
// Sempre converter com Number() antes de formatar ou comparar.
export function formatPrice(value) {
  const n = Number(value)
  return Number.isFinite(n) ? money.format(n) : '—'
}

export function formatDateTime(value) {
  if (!value) return '—'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return '—'
  return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(d)
}

// Categorias do seed (backend/database/seeders/ProductSeeder.php).
export const CATEGORIES = ['camisetas', 'canecas', 'moletons', 'acessorios']

export const SORT_OPTIONS = [
  { value: '-created_at', label: 'Mais recentes' },
  { value: 'price', label: 'Menor preço' },
  { value: '-price', label: 'Maior preço' },
  { value: 'name', label: 'Nome (A–Z)' },
  { value: '-stock', label: 'Maior estoque' },
]

// Retorna o corpo do envelope: { data: [...], meta: {...} }.
export async function listProducts(params) {
  const { data } = await api.get('/api/products', { params })
  return data
}

// Retorna o produto: { ..., logs: [] }.
export async function getProduct(id) {
  const { data } = await api.get(`/api/products/${id}`)
  return data.data
}
