<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { getProduct, createProduct, updateProduct, CATEGORIES, normalizeDecimal } from '../lib/products'
import AppButton from '../components/AppButton.vue'
import FieldError from '../components/FieldError.vue'
import LoadingState from '../components/LoadingState.vue'
import ErrorState from '../components/ErrorState.vue'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => Boolean(route.params.id))
const loadingPage = ref(false)
const notFound = ref(false)
const loading = ref(false)
const fieldErrors = ref({})
const generalError = ref('')

const form = ref({
  name: '',
  category: '',
  description: '',
  image: '',
  price: '',
  stock: '',
})

const inputClass = 'w-full rounded-lg border-2 border-ink bg-paper px-3 py-2 text-base focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-flame'

onMounted(async () => {
  if (!isEdit.value) return
  loadingPage.value = true
  try {
    const product = await getProduct(route.params.id)
    form.value = {
      name: product.name ?? '',
      category: product.category ?? '',
      description: product.description ?? '',
      image: product.image ?? '',
      price: normalizeDecimal(product.price),
      stock: product.stock ?? '',
    }
  } catch (e) {
    if (e.response?.status === 404) {
      notFound.value = true
    } else {
      generalError.value = e.response?.data?.message ?? 'Falha de conexão. Tente novamente.'
    }
  } finally {
    loadingPage.value = false
  }
})

// PUT é total: manda o objeto completo. price/stock vão como número.
function buildPayload() {
  return {
    name: form.value.name.trim(),
    category: form.value.category.trim(),
    description: form.value.description.trim() || null,
    image: form.value.image.trim() || null,
    price: form.value.price === '' ? '' : Number(form.value.price),
    stock: form.value.stock === '' ? '' : Number(form.value.stock),
  }
}

async function onSubmit() {
  if (loading.value) return
  loading.value = true
  fieldErrors.value = {}
  generalError.value = ''
  try {
    if (isEdit.value) {
      const saved = await updateProduct(route.params.id, buildPayload())
      router.push({ name: 'product-detail', params: { id: saved.id } })
    } else {
      const created = await createProduct(buildPayload())
      router.push({ name: 'product-detail', params: { id: created.id } })
    }
  } catch (error) {
    const payload = error.response?.data
    fieldErrors.value = payload?.errors ?? {}
    generalError.value = payload?.message ?? 'Falha de conexão. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="mx-auto w-full max-w-2xl px-4 py-10">
    <p class="font-display text-lg text-flame">Minha área</p>
    <h1 class="mt-2">{{ isEdit ? 'Editar produto' : 'Novo produto' }}</h1>

    <LoadingState v-if="loadingPage" message="Carregando produto…" class="mt-8" />

    <div v-else-if="notFound" role="alert" class="mt-8 rounded-xl border-2 border-ink bg-cream p-8">
      <p class="font-display text-2xl">Peça não encontrada.</p>
      <p class="mt-2 text-ink-soft">Ela pode ter saído do ar. Volte ao gerenciamento.</p>
    </div>

    <div v-else class="mt-6 rounded-xl border-2 border-ink bg-paper p-6 shadow-[6px_6px_0_var(--color-ink)]">
      <ErrorState v-if="generalError" :message="generalError" />

      <form @submit.prevent="onSubmit" novalidate class="mt-4 flex flex-col gap-4">
        <div>
          <label for="name" class="mb-1 block text-sm font-bold">Nome</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            name="name"
            autocomplete="off"
            minlength="3"
            maxlength="255"
            placeholder="Camiseta Estampa Maré…"
            :aria-invalid="Boolean(fieldErrors.name?.length)"
            :aria-describedby="fieldErrors.name?.length ? 'name-error' : undefined"
            :class="inputClass"
          />
          <FieldError :messages="fieldErrors.name ?? []" error-id="name-error" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label for="category" class="mb-1 block text-sm font-bold">Categoria</label>
            <input
              id="category"
              v-model="form.category"
              type="text"
              name="category"
              autocomplete="off"
              maxlength="100"
              list="category-options"
              placeholder="camisetas…"
              :aria-invalid="Boolean(fieldErrors.category?.length)"
              :aria-describedby="fieldErrors.category?.length ? 'category-error' : undefined"
              :class="inputClass"
            />
            <datalist id="category-options">
              <option v-for="c in CATEGORIES" :key="c" :value="c" />
            </datalist>
            <FieldError :messages="fieldErrors.category ?? []" error-id="category-error" />
          </div>

          <div>
            <label for="stock" class="mb-1 block text-sm font-bold">Estoque</label>
            <input
              id="stock"
              v-model="form.stock"
              type="number"
              name="stock"
              inputmode="numeric"
              min="0"
              step="1"
              placeholder="10…"
              :aria-invalid="Boolean(fieldErrors.stock?.length)"
              :aria-describedby="fieldErrors.stock?.length ? 'stock-error' : undefined"
              :class="inputClass"
            />
            <FieldError :messages="fieldErrors.stock ?? []" error-id="stock-error" />
          </div>
        </div>

        <div>
          <label for="price" class="mb-1 block text-sm font-bold">Preço (R$)</label>
          <input
            id="price"
            v-model="form.price"
            type="number"
            name="price"
            inputmode="decimal"
            min="0"
            step="0.01"
            placeholder="59,90…"
            :aria-invalid="Boolean(fieldErrors.price?.length)"
            :aria-describedby="fieldErrors.price?.length ? 'price-error' : undefined"
            :class="inputClass"
          />
          <FieldError :messages="fieldErrors.price ?? []" error-id="price-error" />
        </div>

        <div>
          <label for="description" class="mb-1 block text-sm font-bold">Descrição <span class="font-normal text-ink-soft">(opcional)</span></label>
          <textarea
            id="description"
            v-model="form.description"
            name="description"
            rows="3"
            placeholder="Tecido, modelagem, caimento…"
            :aria-invalid="Boolean(fieldErrors.description?.length)"
            :aria-describedby="fieldErrors.description?.length ? 'description-error' : undefined"
            :class="inputClass"
          />
          <FieldError :messages="fieldErrors.description ?? []" error-id="description-error" />
        </div>

        <div>
          <label for="image" class="mb-1 block text-sm font-bold">Imagem (URL) <span class="font-normal text-ink-soft">(opcional)</span></label>
          <input
            id="image"
            v-model="form.image"
            type="url"
            name="image"
            autocomplete="off"
            :spellcheck="false"
            maxlength="2048"
            placeholder="https://…"
            :aria-invalid="Boolean(fieldErrors.image?.length)"
            :aria-describedby="fieldErrors.image?.length ? 'image-error' : undefined"
            :class="inputClass"
          />
          <FieldError :messages="fieldErrors.image ?? []" error-id="image-error" />
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
          <RouterLink
            :to="isEdit ? { name: 'product-detail', params: { id: route.params.id } } : { name: 'manage-products' }"
            class="inline-flex items-center justify-center rounded-lg border-2 border-ink px-5 py-2.5 font-bold hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
          >
            Cancelar
          </RouterLink>
          <div class="sm:w-56">
            <AppButton :loading="loading">
              {{ loading ? 'Salvando…' : isEdit ? 'Salvar mudanças' : 'Criar produto' }}
            </AppButton>
          </div>
        </div>
      </form>
    </div>
  </section>
</template>
