<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { listProducts, deleteProduct, formatPrice } from '../lib/products'
import ConfirmDialog from '../components/ConfirmDialog.vue'

const products = ref([])
const loading = ref(true)
const error = ref('')
const notice = ref('')
const pendingDelete = ref(null)
const deleting = ref(false)

async function fetchAll() {
  loading.value = true
  error.value = ''
  try {
    const body = await listProducts({ per_page: 100, sort: 'name' })
    products.value = body.data ?? []
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Falha de conexão. Tente novamente.'
  } finally {
    loading.value = false
  }
}

function askDelete(product) {
  pendingDelete.value = product
}

async function confirmDelete() {
  if (!pendingDelete.value) return
  deleting.value = true
  try {
    await deleteProduct(pendingDelete.value.id)
    notice.value = `“${pendingDelete.value.name}” excluída do catálogo.`
    products.value = products.value.filter((p) => p.id !== pendingDelete.value.id)
  } catch (e) {
    if (e.response?.status === 404) {
      notice.value = 'Essa peça já tinha saído do catálogo.'
      await fetchAll()
    } else {
      error.value = e.response?.data?.message ?? 'Falha de conexão. Tente novamente.'
    }
  } finally {
    deleting.value = false
    pendingDelete.value = null
  }
}

onMounted(fetchAll)
</script>

<template>
  <section class="mx-auto w-full max-w-6xl px-4 py-10">
    <p class="font-display text-lg text-flame">Minha área</p>
    <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
      <h1>Gerenciar produtos</h1>
      <RouterLink
        :to="{ name: 'product-new' }"
        class="inline-flex items-center justify-center rounded-lg border-2 border-ink bg-ink px-5 py-2.5 font-bold text-paper transition-colors hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
      >
        + Novo produto
      </RouterLink>
    </div>

    <p v-if="notice" role="status" class="mt-4 rounded-lg border-2 border-leaf bg-paper px-4 py-2 text-sm font-medium text-leaf">
      {{ notice }}
    </p>

    <p v-if="error" role="alert" class="mt-4 rounded-lg border-2 border-blood bg-paper px-4 py-2 text-sm font-medium text-blood">
      {{ error }}
    </p>

    <p v-if="loading" class="mt-8" role="status">Carregando produtos…</p>

    <div v-else-if="!products.length" class="mt-6 rounded-xl border-2 border-dashed border-line px-4 py-12 text-center">
      <p class="font-display text-xl">Varal vazio.</p>
      <p class="mx-auto mt-2 text-ink-soft">Cadastre a primeira peça para começar.</p>
    </div>

    <div v-else class="mt-6 overflow-x-auto rounded-xl border-2 border-ink">
      <table class="w-full min-w-[640px] border-collapse bg-paper text-left text-sm">
        <thead>
          <tr class="border-b-2 border-ink bg-cream">
            <th scope="col" class="px-4 py-3 font-bold">Peça</th>
            <th scope="col" class="px-4 py-3 font-bold">Categoria</th>
            <th scope="col" class="px-4 py-3 text-right font-bold tabular-nums">Preço</th>
            <th scope="col" class="px-4 py-3 text-right font-bold tabular-nums">Estoque</th>
            <th scope="col" class="px-4 py-3 text-right font-bold">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in products" :key="p.id" class="border-b border-line last:border-0">
            <td class="px-4 py-3">
              <RouterLink
                :to="{ name: 'product-detail', params: { id: p.id } }"
                class="font-bold hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
              >
                {{ p.name }}
              </RouterLink>
            </td>
            <td class="px-4 py-3">{{ p.category }}</td>
            <td class="px-4 py-3 text-right tabular-nums">{{ formatPrice(p.price) }}</td>
            <td class="px-4 py-3 text-right tabular-nums">
              <span v-if="Number(p.stock) > 0">{{ p.stock }}</span>
              <span v-else class="font-bold text-blood">0</span>
            </td>
            <td class="px-4 py-3 text-right">
              <RouterLink
                :to="{ name: 'product-edit', params: { id: p.id } }"
                class="mr-3 font-semibold text-flame-deep hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
              >
                Editar
              </RouterLink>
              <button
                type="button"
                @click="askDelete(p)"
                class="font-semibold text-blood hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
              >
                Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmDialog
      :open="pendingDelete !== null"
      title="Excluir produto?"
      :message="pendingDelete ? `“${pendingDelete.name}” sai do catálogo na hora. Essa ação não tem volta.` : ''"
      confirm-label="Excluir"
      :busy="deleting"
      @confirm="confirmDelete"
      @cancel="pendingDelete = null"
    />
  </section>
</template>
