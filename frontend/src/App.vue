<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const auth = useAuthStore()

async function onLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}

function navClass(isActive) {
  return [
    'rounded-md px-2 py-1 font-semibold hover:text-flame focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame',
    isActive ? 'text-flame' : '',
  ]
}
</script>

<template>
  <div class="flex min-h-screen flex-col">
    <header class="border-b-2 border-ink">
      <nav class="mx-auto flex w-full max-w-6xl flex-wrap items-center gap-x-5 gap-y-2 px-4 py-3" aria-label="Navegação principal">
        <RouterLink to="/" class="font-display text-xl tracking-tight focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame" aria-label="Chico Rei — início">
          Chico<span class="text-flame">★</span>Rei
        </RouterLink>
        <RouterLink to="/" :class="navClass($route.name === 'home')" exact-active-class="">
          Início
        </RouterLink>
        <RouterLink to="/produtos" :class="navClass($route.name === 'products' || $route.name === 'product-detail')">
          Produtos
        </RouterLink>
        <RouterLink
          v-if="auth.isAuthenticated"
          to="/app"
          :class="navClass($route.name === 'app')"
        >
          Minha área
        </RouterLink>
        <span class="flex-1"></span>
        <RouterLink
          v-if="!auth.isAuthenticated"
          to="/login"
          class="rounded-lg border-2 border-ink px-4 py-1 font-bold hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Entrar
        </RouterLink>
        <button
          v-else
          type="button"
          @click="onLogout"
          class="rounded-md px-2 py-1 font-semibold hover:text-flame focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Sair ({{ auth.user?.name ?? auth.user?.email }})
        </button>
      </nav>
    </header>

    <main class="flex-1">
      <RouterView />
    </main>

    <footer class="mt-16 border-t-2 border-ink">
      <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center gap-2 px-4 py-5 text-sm text-ink-soft">
        <span class="font-display text-ink">Chico<span class="text-flame">★</span>Rei</span>
        <span>— loja demo do desafio, dados fictícios.</span>
      </div>
    </footer>
  </div>
</template>
