<script setup>
import { computed, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppButton from '../components/AppButton.vue'

const router = useRouter()
const auth = useAuthStore()

const initial = computed(() => (auth.user?.name ?? auth.user?.email ?? '?').charAt(0).toUpperCase())

// Revalida o token e recarrega o usuário a cada entrada na área logada.
// Se o token morreu, o 401 limpa a sessão e volta ao login (ver lib/api.js).
onMounted(() => {
  auth.fetchMe()
})

async function onLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <section class="mx-auto w-full max-w-xl px-4 py-14">
    <p class="font-display text-lg text-flame">Minha área</p>
    <div class="mt-4 rounded-xl border-2 border-ink bg-paper p-6 shadow-[6px_6px_0_var(--color-ink)]">
      <div class="flex items-center gap-4">
        <span aria-hidden="true" class="flex size-14 shrink-0 items-center justify-center rounded-full border-2 border-ink bg-cream font-display text-2xl">
          {{ initial }}
        </span>
        <div class="min-w-0">
          <p class="truncate text-xl font-bold">{{ auth.user?.name }}</p>
          <p class="truncate text-sm text-ink-soft">{{ auth.user?.email }}</p>
        </div>
      </div>
      <div class="mt-6 flex flex-col gap-2">
        <RouterLink
          :to="{ name: 'manage-products' }"
          class="inline-flex items-center justify-center rounded-lg border-2 border-ink bg-ink px-5 py-2.5 font-bold text-paper transition-colors hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Gerenciar produtos
        </RouterLink>
        <RouterLink
          :to="{ name: 'products' }"
          class="inline-flex items-center justify-center rounded-lg border-2 border-ink px-5 py-2.5 font-bold hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Ver catálogo
        </RouterLink>
        <AppButton type="button" @click="onLogout">Sair</AppButton>
      </div>
    </div>
  </section>
</template>
