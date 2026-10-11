<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { listProducts, CATEGORIES, SORT_OPTIONS } from '../lib/products'
import ProductCard from '../components/ProductCard.vue'
import LoadingState from '../components/LoadingState.vue'
import EmptyState from '../components/EmptyState.vue'
import ErrorState from '../components/ErrorState.vue'

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

const inputClass = 'w-full rounded-lg border-2 border-ink bg-paper px-3 py-2 text-base focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-flame'

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
    <p class="font-display text-lg text-flame">Catálogo</p>
    <h1 class="mt-2">Produtos</h1>

    <form @submit.prevent="apply" class="mt-6 grid grid-cols-1 gap-3 rounded-xl border-2 border-ink bg-cream p-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="lg:col-span-2">
        <label for="q" class="mb-1 block text-sm font-bold">Buscar</label>
        <input
          id="q"
          v-model="filters.q"
          type="search"
          name="q"
          autocomplete="off"
          placeholder="O que você procura?…"
          :class="inputClass"
        />
      </div>

      <div>
        <label for="category" class="mb-1 block text-sm font-bold">Categoria</label>
        <select id="category" v-model="filters.category" name="category" :class="inputClass">
          <option value="">Todas</option>
          <option v-for="c in CATEGORIES" :key="c" :value="c">{{ c }}</option>
        </select>
      </div>

      <div>
        <label for="in_stock" class="mb-1 block text-sm font-bold">Disponibilidade</label>
        <select id="in_stock" v-model="filters.in_stock" name="in_stock" :class="inputClass">
          <option value="">Tudo</option>
          <option value="1">Em estoque</option>
          <option value="0">Esgotados</option>
        </select>
      </div>

      <div>
        <label for="min_price" class="mb-1 block text-sm font-bold">Preço mín. (R$)</label>
        <input
          id="min_price"
          v-model="filters.min_price"
          type="number"
          name="min_price"
          inputmode="decimal"
          min="0"
          step="0.01"
          placeholder="0,00…"
          :class="inputClass"
        />
      </div>

      <div>
        <label for="max_price" class="mb-1 block text-sm font-bold">Preço máx. (R$)</label>
        <input
          id="max_price"
          v-model="filters.max_price"
          type="number"
          name="max_price"
          inputmode="decimal"
          min="0"
          step="0.01"
          placeholder="999,99…"
          :class="inputClass"
        />
      </div>

      <div>
        <label for="sort" class="mb-1 block text-sm font-bold">Ordenar</label>
        <select id="sort" v-model="filters.sort" name="sort" :class="inputClass">
          <option v-for="o in SORT_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
        </select>
      </div>

      <div class="flex items-end">
        <button
          type="submit"
          :disabled="loading"
          class="inline-flex w-full items-center justify-center rounded-lg border-2 border-ink bg-ink px-4 py-2 text-base font-bold text-paper transition-colors hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ loading ? 'Buscando…' : 'Buscar' }}
        </button>
      </div>
    </form>

    <ErrorState v-if="error" :message="error" retryable @retry="fetchProducts" class="mt-4" />

    <LoadingState v-else-if="loading" message="Carregando produtos…" class="mt-8" />

    <template v-else>
      <p class="mt-6 font-semibold" role="status">
        {{ meta.total }} {{ meta.total === 1 ? 'peça' : 'peças' }}
        <span class="font-normal text-ink-soft">· página {{ meta.current_page }} de {{ meta.last_page }}</span>
      </p>

      <EmptyState
        v-if="!products.length"
        title="Nada por aqui."
        message="Ajuste os filtros para ver mais peças do varal."
        class="mt-4"
      />

      <div v-else class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>

      <nav v-if="meta.last_page > 1" class="mt-10 flex items-center justify-center gap-4" aria-label="Paginação">
        <button
          type="button"
          :disabled="filters.page <= 1 || loading"
          @click="gotoPage(filters.page - 1)"
          class="rounded-lg border-2 border-ink px-4 py-2 font-bold hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame disabled:cursor-not-allowed disabled:opacity-40"
        >
          ← Anterior
        </button>
        <span class="font-semibold tabular-nums" aria-current="page">{{ meta.current_page }} / {{ meta.last_page }}</span>
        <button
          type="button"
          :disabled="filters.page >= meta.last_page || loading"
          @click="gotoPage(filters.page + 1)"
          class="rounded-lg border-2 border-ink px-4 py-2 font-bold hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame disabled:cursor-not-allowed disabled:opacity-40"
        >
          Próxima →
        </button>
      </nav>
    </template>
  </section>
</template>
