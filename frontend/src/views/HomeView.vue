<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { listProducts } from '../lib/products'
import ProductCard from '../components/ProductCard.vue'

const highlights = ref([])

onMounted(async () => {
  try {
    const body = await listProducts({ per_page: 4, sort: '-created_at' })
    highlights.value = body.data ?? []
  } catch {
    highlights.value = []
  }
})
</script>

<template>
  <div>
    <section class="mx-auto w-full max-w-6xl px-4 pb-10 pt-14">
      <p class="font-display text-lg text-flame">Camisetas, moletons, canecas e mais</p>
      <h1 class="mt-3 max-w-4xl">Vista a sua próxima peça favorita.</h1>
      <p class="mt-5 max-w-xl text-lg text-ink-soft">
        Peças com atitude para o dia a dia. Escolha no catálogo, entre na sua conta e acompanhe tudo por aqui.
      </p>
      <div class="mt-8 flex flex-wrap gap-3">
        <RouterLink
          to="/produtos"
          class="inline-flex items-center justify-center rounded-lg border-2 border-ink bg-ink px-6 py-3 text-base font-bold text-paper transition-colors hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Ver produtos
        </RouterLink>
        <RouterLink
          to="/login"
          class="inline-flex items-center justify-center rounded-lg border-2 border-ink bg-transparent px-6 py-3 text-base font-bold transition-colors hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Entrar na conta
        </RouterLink>
      </div>
    </section>

    <div class="marquee border-y-2 border-ink bg-flame py-2.5 text-white" aria-hidden="true">
      <span class="marquee-track font-display text-lg">
        <span>algodão 100% ★ estampas exclusivas ★ moletons ★ canecas ★ acessórios ★ algodão 100% ★ estampas exclusivas ★ moletons ★ canecas ★ acessórios ★&nbsp;</span><span aria-hidden="true">algodão 100% ★ estampas exclusivas ★ moletons ★ canecas ★ acessórios ★ algodão 100% ★ estampas exclusivas ★ moletons ★ canecas ★ acessórios ★&nbsp;</span>
      </span>
    </div>

    <section class="mx-auto w-full max-w-6xl px-4 py-12" aria-labelledby="destaques">
      <div class="flex flex-wrap items-end justify-between gap-3">
        <h2 id="destaques">Novidades no varal</h2>
        <RouterLink
          to="/produtos"
          class="font-semibold text-flame-deep hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Ver catálogo completo
        </RouterLink>
      </div>
      <div v-if="highlights.length" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <ProductCard v-for="p in highlights" :key="p.id" :product="p" />
      </div>
    </section>
  </div>
</template>
