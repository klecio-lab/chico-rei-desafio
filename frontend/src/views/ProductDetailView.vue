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
  <section class="mx-auto w-full max-w-4xl px-4 py-10">
    <RouterLink
      :to="{ name: 'products' }"
      class="text-sm font-medium text-violet-700 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600"
    >
      ← Voltar aos produtos
    </RouterLink>

    <p v-if="loading" class="mt-8" role="status">Carregando produto…</p>

    <p v-else-if="notFound" role="alert" class="mt-8 rounded-md border border-red-200 bg-red-50 px-4 py-6 text-center">
      Produto não encontrado.
    </p>

    <p v-else-if="error" role="alert" class="mt-8 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
      {{ error }}
    </p>

    <template v-else-if="product">
      <div class="mt-6 grid grid-cols-1 gap-8 md:grid-cols-2">
        <img
          :src="product.image"
          :alt="product.name"
          width="600"
          height="600"
          class="aspect-square w-full rounded-lg border border-gray-200 object-cover"
        />
        <div class="min-w-0">
          <p class="text-xs uppercase tracking-wide text-gray-500">{{ product.category }}</p>
          <h1 class="mt-1 text-balance">{{ product.name }}</h1>
          <p class="mt-3 text-3xl font-bold tabular-nums">{{ formatPrice(product.price) }}</p>
          <p v-if="Number(product.stock) > 0" class="mt-2 text-sm text-green-700">
            {{ product.stock }} {{ Number(product.stock) === 1 ? 'unidade em estoque' : 'unidades em estoque' }}
          </p>
          <p v-else class="mt-2 text-sm font-medium text-red-600">Esgotado</p>
          <p class="mt-4 break-words">{{ product.description ?? 'Sem descrição.' }}</p>
        </div>
      </div>

      <section class="mt-10" aria-labelledby="logs-title">
        <h2 id="logs-title">Movimentações</h2>
        <p v-if="!(product.logs ?? []).length" class="mt-2 text-sm text-gray-600">
          Sem movimentações registradas.
        </p>
        <ul v-else class="mt-3 flex flex-col gap-2">
          <li
            v-for="log in product.logs"
            :key="log.id ?? `${log.action}-${log.created_at}`"
            class="flex min-w-0 items-baseline justify-between gap-4 rounded-md border border-gray-200 px-4 py-2 text-sm"
          >
            <span class="truncate font-medium">{{ log.action }}</span>
            <time class="shrink-0 tabular-nums text-gray-500" :datetime="log.created_at">
              {{ formatDateTime(log.created_at) }}
            </time>
          </li>
        </ul>
      </section>
    </template>
  </section>
</template>
