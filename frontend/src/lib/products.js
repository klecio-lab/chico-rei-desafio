import api from './api'

const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

// Trava nº 1 do handoff (fatia 2 back): price serializa STRING ("10.00").
// Sempre converter com Number() antes de formatar ou comparar.
export function formatPrice(value) {
  const n = Number(value)
  return Number.isFinite(n) ? money.format(n) : '—'
}

// A API às vezes serializa o decimal com ruído de float ("49.900001525878906").
// Normaliza para 2 casas ao pré-preencher o formulário de edição.
export function normalizeDecimal(value) {
  const n = Number(value)
  return Number.isFinite(n) ? String(Math.round(n * 100) / 100) : (value ?? '')
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

// Escrita (exige Bearer — ver handoff fatia 3 back).
// POST/PUT recebem o objeto completo; PUT é atualização total.
// Com arquivo: multipart (só chaves preenchidas + image_file; o file vence a
// URL no servidor — BDR-010). Sem arquivo: JSON com o objeto completo.
export function toProductBody(payload) {
  const { image_file, ...rest } = payload
  if (!(image_file instanceof File)) return rest
  const form = new FormData()
  for (const [key, value] of Object.entries(rest)) {
    if (value !== null && value !== undefined) form.append(key, value)
  }
  form.append('image_file', image_file)
  return form
}

export async function createProduct(payload) {
  const { data } = await api.post('/api/products', toProductBody(payload))
  return data.data
}

export async function updateProduct(id, payload) {
  const { data } = await api.put(`/api/products/${id}`, toProductBody(payload))
  return data.data
}

// DELETE responde 204 (corpo vazio).
export async function deleteProduct(id) {
  await api.delete(`/api/products/${id}`)
}
