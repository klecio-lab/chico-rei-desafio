<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { getProduct, formatPrice, formatDateTime } from '../lib/products'

const props = defineProps({ id: { type: [String, Number], required: true } })

const product = ref(null)
const loading = ref(true)
const notFound = ref(false)
const error = ref('')

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

    <p v-if="loading" class="mt-8" role="status">Carregando produto…</p>

    <div v-else-if="notFound" role="alert" class="mt-8 max-w-xl rounded-xl border-2 border-ink bg-cream p-8">
      <p class="font-display text-2xl">Peça não encontrada.</p>
      <p class="mt-2 text-ink-soft">Ela pode ter saído do ar. Que tal olhar o resto do varal?</p>
    </div>

    <p v-else-if="error" role="alert" class="mt-8 rounded-lg border-2 border-blood bg-paper px-4 py-2 text-sm font-medium text-blood">
      {{ error }}
    </p>

    <template v-else-if="product">
      <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <img
          :src="product.image"
          :alt="product.name"
          width="600"
          height="600"
          class="aspect-square w-full rounded-xl border-2 border-ink object-cover"
        />
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
        <ul v-else class="mt-4 flex flex-col gap-2">
          <li
            v-for="log in product.logs"
            :key="log.id ?? `${log.action}-${log.created_at}`"
            class="flex min-w-0 items-baseline justify-between gap-4 rounded-lg border-2 border-line px-4 py-2"
          >
            <span class="truncate font-semibold">{{ log.action }}</span>
            <time class="shrink-0 text-sm tabular-nums text-ink-soft" :datetime="log.created_at">
              {{ formatDateTime(log.created_at) }}
            </time>
          </li>
        </ul>
      </section>
    </template>
  </section>
</template>
