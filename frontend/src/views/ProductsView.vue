<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { listProducts, formatPrice, CATEGORIES, SORT_OPTIONS } from '../lib/products'

const route = useRoute()
const router = useRouter()

const PER_PAGE = 12

const filters = ref({
  q: '',
  category: '',
  min_price: '',
  max_price: '',
  in_stock: '',
  sort: '-created_at',
  page: 1,
})
const products = ref([])
const meta = ref({ current_page: 1, last_page: 1, per_page: PER_PAGE, total: 0 })
const loading = ref(false)
const error = ref('')

function filtersFromQuery(query) {
  return {
    q: query.q ?? '',
    category: query.category ?? '',
    min_price: query.min_price ?? '',
    max_price: query.max_price ?? '',
    in_stock: query.in_stock ?? '',
    sort: query.sort ?? '-created_at',
    page: Number(query.page) > 0 ? Number(query.page) : 1,
  }
}

function queryFromFilters(f) {
  const query = {}
  if (f.q) query.q = f.q
  if (f.category) query.category = f.category
  if (f.min_price !== '') query.min_price = f.min_price
  if (f.max_price !== '') query.max_price = f.max_price
  if (f.in_stock !== '') query.in_stock = f.in_stock
  if (f.sort && f.sort !== '-created_at') query.sort = f.sort
  if (f.page > 1) query.page = String(f.page)
  return query
}

function paramsFromFilters(f) {
  const params = { per_page: PER_PAGE, page: f.page, sort: f.sort || '-created_at' }
  if (f.q) params.q = f.q
  if (f.category) params.category = f.category
  if (f.min_price !== '') params.min_price = f.min_price
  if (f.max_price !== '') params.max_price = f.max_price
  if (f.in_stock !== '') params.in_stock = f.in_stock
  return params
}

async function fetchProducts() {
  loading.value = true
  error.value = ''
  try {
    const body = await listProducts(paramsFromFilters(filters.value))
    products.value = body.data ?? []
    meta.value = body.meta ?? meta.value
  } catch (e) {
    products.value = []
    const payload = e.response?.data
    const details = Object.values(payload?.errors ?? {}).flat().join(' ')
    error.value = [payload?.message, details].filter(Boolean).join(' ') || 'Falha de conexão. Tente novamente.'
  } finally {
    loading.value = false
  }
}

// Fonte única de verdade: a URL. replace() dispara o watcher, que ignora
// por já estar sincronizado (lastQuery); back/forward sincroniza e busca.
let lastQuery = ''
function pushFilters() {
  lastQuery = JSON.stringify(queryFromFilters(filters.value))
  router.replace({ name: 'products', query: queryFromFilters(filters.value) })
  fetchProducts()
}

function apply() {
  filters.value.page = 1
  pushFilters()
}

function gotoPage(page) {
  if (page < 1 || page > meta.value.last_page || page === filters.value.page) return
  filters.value.page = page
  pushFilters()
}

watch(
  () => route.query,
  async () => {
    const key = JSON.stringify(route.query)
    if (key === lastQuery) return
    lastQuery = key
    filters.value = filtersFromQuery(route.query)
    await fetchProducts()
  },
  { immediate: true },
)
</script>

<template>
  <section class="mx-auto w-full max-w-6xl px-4 py-10">
    <h1>Produtos</h1>

    <form @submit.prevent="apply" class="mt-6 grid grid-cols-1 gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="lg:col-span-2">
        <label for="q" class="mb-1 block text-sm font-medium">Buscar</label>
        <input
          id="q"
          v-model="filters.q"
          type="search"
          name="q"
          autocomplete="off"
          placeholder="Nome do produto…"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-violet-600"
        />
      </div>

      <div>
        <label for="category" class="mb-1 block text-sm font-medium">Categoria</label>
        <select
          id="category"
          v-model="filters.category"
          name="category"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-violet-600"
        >
          <option value="">Todas</option>
          <option v-for="c in CATEGORIES" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>

      <div>
        <label for="in_stock" class="mb-1 block text-sm font-medium">Estoque</label>
        <select
          id="in_stock"
          v-model="filters.in_stock"
          name="in_stock"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-violet-600"
        >
          <option value="">Todos</option>
          <option value="1">Em estoque</option>
          <option value="0">Esgotados</option>
        </select>
      </div>

      <div>
        <label for="min_price" class="mb-1 block text-sm font-medium">Preço mín. (R$)</label>
        <input
          id="min_price"
          v-model="filters.min_price"
          type="number"
          name="min_price"
          inputmode="decimal"
          min="0"
          step="0.01"
          placeholder="0,00…"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-violet-600"
        />
      </div>

      <div>
        <label for="max_price" class="mb-1 block text-sm font-medium">Preço máx. (R$)</label>
        <input
          id="max_price"
          v-model="filters.max_price"
          type="number"
          name="max_price"
          inputmode="decimal"
          min="0"
          step="0.01"
          placeholder="999,99…"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-violet-600"
        />
      </div>

      <div>
        <label for="sort" class="mb-1 block text-sm font-medium">Ordenar</label>
        <select
          id="sort"
          v-model="filters.sort"
          name="sort"
          class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-violet-600"
        >
          <option v-for="o in SORT_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
        </select>
      </div>

      <div class="flex items-end">
        <button
          type="submit"
          :disabled="loading"
          class="inline-flex w-full items-center justify-center rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ loading ? 'Buscando…' : 'Buscar' }}
        </button>
      </div>
    </form>

    <p v-if="error" role="alert" class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
      {{ error }}
    </p>

    <p v-else-if="loading" class="mt-8 text-center" role="status">Carregando produtos…</p>

    <template v-else>
      <p class="mt-6 text-sm text-gray-600" role="status">
        {{ meta.total }} {{ meta.total === 1 ? 'produto' : 'produtos' }} · página {{ meta.current_page }} de {{ meta.last_page }}
      </p>

      <p v-if="!products.length" class="mt-8 rounded-md border border-dashed border-gray-300 px-4 py-10 text-center">
        Nenhum produto encontrado. Ajuste os filtros e tente de novo.
      </p>

      <ul v-else class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <li v-for="p in products" :key="p.id" class="overflow-hidden rounded-lg border border-gray-200 bg-white">
          <RouterLink :to="{ name: 'product-detail', params: { id: p.id } }" class="block focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600">
            <img
              :src="p.image"
              :alt="p.name"
              width="600"
              height="600"
              loading="lazy"
              class="aspect-square w-full object-cover"
            />
          </RouterLink>
          <div class="flex min-w-0 flex-col gap-1 p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">{{ p.category }}</p>
            <RouterLink
              :to="{ name: 'product-detail', params: { id: p.id } }"
              class="truncate font-semibold hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600"
            >
              {{ p.name }}
            </RouterLink>
            <p class="text-lg font-bold tabular-nums">{{ formatPrice(p.price) }}</p>
            <p v-if="Number(p.stock) > 0" class="text-sm text-green-700">{{ p.stock }} em estoque</p>
            <p v-else class="text-sm font-medium text-red-600">Esgotado</p>
          </div>
        </li>
      </ul>

      <nav v-if="meta.last_page > 1" class="mt-8 flex items-center justify-center gap-3" aria-label="Paginação">
        <button
          type="button"
          :disabled="filters.page <= 1 || loading"
          @click="gotoPage(filters.page - 1)"
          class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600 disabled:cursor-not-allowed disabled:opacity-50"
        >
          ← Anterior
        </button>
        <span class="text-sm tabular-nums" aria-current="page">Página {{ meta.current_page }} de {{ meta.last_page }}</span>
        <button
          type="button"
          :disabled="filters.page >= meta.last_page || loading"
          @click="gotoPage(filters.page + 1)"
          class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600 disabled:cursor-not-allowed disabled:opacity-50"
        >
          Próxima →
        </button>
      </nav>
    </template>
  </section>
</template>
