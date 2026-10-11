<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { getProduct, formatPrice, formatDateTime } from '../lib/products'
import LoadingState from '../components/LoadingState.vue'
import EmptyState from '../components/EmptyState.vue'
import ErrorState from '../components/ErrorState.vue'

const props = defineProps({ id: { type: [String, Number], required: true } })

const product = ref(null)
const loading = ref(true)
const notFound = ref(false)
const error = ref('')

const ACTION_LABELS = { created: 'Criado', updated: 'Atualizado', deleted: 'Excluído' }
const FIELD_LABELS = {
  name: 'Nome',
  description: 'Descrição',
  image: 'Imagem',
  price: 'Preço',
  category: 'Categoria',
  stock: 'Estoque',
}
const HIDDEN_CHANGE_KEYS = ['id', 'product_id', 'user_id', 'created_at', 'updated_at']

function displayValue(key, value) {
  if (value === null || value === undefined || value === '') return '—'
  if (key === 'price') return formatPrice(value)
  return String(value)
}

// Normaliza os 3 formatos de changes do handoff (fatia 3 back):
// created → {after}, updated → {before, after}, deleted → {snapshot}.
function changeRows(changes) {
  if (!changes || typeof changes !== 'object') return []
  if (changes.snapshot && typeof changes.snapshot === 'object') {
    return Object.entries(changes.snapshot)
      .filter(([key]) => !HIDDEN_CHANGE_KEYS.includes(key))
      .map(([key, value]) => ({ field: key, before: null, after: value }))
  }
  const before = changes.before && typeof changes.before === 'object' ? changes.before : {}
  const after = changes.after && typeof changes.after === 'object' ? changes.after : {}
  const keys = [...new Set([...Object.keys(before), ...Object.keys(after)])]
    .filter((key) => !HIDDEN_CHANGE_KEYS.includes(key))
  return keys.map((key) => ({ field: key, before: before[key] ?? null, after: after[key] ?? null }))
}

onMounted(async () => {
  try {
    product.value = await getProduct(props.id)
  } catch (e) {
    if (e.response?.status === 404) {
      notFound.value = true
    } else {
      error.value = e.response?.data?.message ?? 'Falha de conexão. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section class="mx-auto w-full max-w-6xl px-4 py-10">
    <RouterLink
      :to="{ name: 'products' }"
      class="font-semibold text-flame-deep hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
    >
      ← Voltar ao catálogo
    </RouterLink>

    <LoadingState v-if="loading" message="Carregando produto…" class="mt-8" />

    <EmptyState
      v-else-if="notFound"
      title="Peça não encontrada."
      message="Ela pode ter saído do ar. Que tal olhar o resto do varal?"
      class="mt-8 max-w-xl"
    >
      <template #action>
        <RouterLink
          :to="{ name: 'products' }"
          class="inline-flex items-center justify-center rounded-lg border-2 border-ink bg-ink px-5 py-2.5 font-bold text-paper transition-colors hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Ver catálogo
        </RouterLink>
      </template>
    </EmptyState>

    <ErrorState v-else-if="error" :message="error" class="mt-8 max-w-xl" />

    <template v-else-if="product">
      <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <img
          v-if="product.image"
          :src="product.image"
          :alt="product.name"
          width="600"
          height="600"
          class="aspect-square w-full rounded-xl border-2 border-ink object-cover"
        />
        <div
          v-else
          aria-hidden="true"
          class="flex aspect-square w-full items-center justify-center rounded-xl border-2 border-ink bg-cream font-display text-9xl text-line"
        >
          {{ (product.name ?? '?').charAt(0).toUpperCase() }}
        </div>
        <div class="min-w-0">
          <p class="w-fit rounded-full bg-cream px-3 py-1 text-sm font-semibold">{{ product.category }}</p>
          <h1 class="mt-3">{{ product.name }}</h1>
          <p class="mt-4 font-display text-4xl tabular-nums text-flame">{{ formatPrice(product.price) }}</p>
          <p v-if="Number(product.stock) > 0" class="mt-2 font-semibold text-leaf">
            {{ product.stock }} {{ Number(product.stock) === 1 ? 'unidade em estoque' : 'unidades em estoque' }}
          </p>
          <p v-else class="mt-2 font-bold text-blood">Esgotado</p>
          <p class="mt-5 max-w-xl break-words text-lg">{{ product.description ?? 'Sem descrição por enquanto.' }}</p>
        </div>
      </div>

      <section class="mt-12 max-w-2xl" aria-labelledby="logs-title">
        <h2 id="logs-title">Movimentações</h2>
        <p v-if="!(product.logs ?? []).length" class="mt-2 text-ink-soft">
          Sem movimentações registradas para esta peça.
        </p>
        <ul v-else class="mt-4 flex flex-col gap-3">
          <li
            v-for="log in product.logs"
            :key="log.id ?? `${log.action}-${log.created_at}`"
            class="rounded-lg border-2 border-line px-4 py-3"
          >
            <div class="flex min-w-0 flex-wrap items-baseline gap-x-3 gap-y-1">
              <span class="rounded-full bg-cream px-2.5 py-0.5 text-xs font-bold">
                {{ ACTION_LABELS[log.action] ?? log.action }}
              </span>
              <time class="text-sm tabular-nums text-ink-soft" :datetime="log.created_at">
                {{ formatDateTime(log.created_at) }}
              </time>
              <span v-if="log.user_id" class="text-sm text-ink-soft">por usuário #{{ log.user_id }}</span>
            </div>
            <ul v-if="changeRows(log.changes).length" class="mt-2 flex flex-col gap-1 text-sm">
              <li v-for="row in changeRows(log.changes)" :key="row.field" class="flex min-w-0 flex-wrap gap-x-2">
                <span class="font-semibold">{{ FIELD_LABELS[row.field] ?? row.field }}:</span>
                <span v-if="row.before !== null" class="break-words text-ink-soft line-through">{{ displayValue(row.field, row.before) }}</span>
                <span v-if="row.before !== null" aria-hidden="true">→</span>
                <span class="break-words font-medium">{{ displayValue(row.field, row.after) }}</span>
              </li>
            </ul>
          </li>
        </ul>
      </section>
    </template>
  </section>
</template>
