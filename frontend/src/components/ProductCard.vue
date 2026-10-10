<script setup>
import { RouterLink } from 'vue-router'
import { formatPrice } from '../lib/products'

defineProps({ product: { type: Object, required: true } })
</script>

<template>
  <article class="group overflow-hidden rounded-xl border-2 border-ink bg-paper transition-shadow hover:shadow-[6px_6px_0_var(--color-ink)]">
    <RouterLink
      :to="{ name: 'product-detail', params: { id: product.id } }"
      class="block overflow-hidden border-b-2 border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
      :aria-label="`Ver ${product.name}`"
    >
      <img
        :src="product.image"
        :alt="product.name"
        width="600"
        height="600"
        loading="lazy"
        class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
      />
    </RouterLink>
    <div class="flex min-w-0 flex-col gap-1.5 p-4">
      <p class="w-fit rounded-full bg-cream px-2.5 py-0.5 text-xs font-semibold">{{ product.category }}</p>
      <RouterLink
        :to="{ name: 'product-detail', params: { id: product.id } }"
        class="truncate text-lg font-bold hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
      >
        {{ product.name }}
      </RouterLink>
      <p class="font-display text-2xl tabular-nums">{{ formatPrice(product.price) }}</p>
      <p v-if="Number(product.stock) > 0" class="text-sm font-medium text-leaf">{{ product.stock }} em estoque</p>
      <p v-else class="text-sm font-bold text-blood">Esgotado</p>
    </div>
  </article>
</template>
