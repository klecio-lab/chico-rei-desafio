<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const auth = useAuthStore()

// Sessão restaurada do localStorage traz o token mas pode estar sem o
// usuário (ex.: home não chama fetchMe) — completa ao abrir o app.
onMounted(() => {
  if (auth.isAuthenticated && !auth.user) auth.fetchMe()
})

const menuOpen = ref(false)
const menuPanel = ref(null)
const avatarButton = ref(null)

const initial = computed(() => (auth.user?.name ?? auth.user?.email ?? '?').charAt(0).toUpperCase())

async function onLogout() {
  closeMenu()
  await auth.logout()
  router.push({ name: 'login' })
}

function toggleMenu() {
  menuOpen.value = !menuOpen.value
  if (menuOpen.value) {
    nextTick(() => menuPanel.value?.querySelector('a, button')?.focus())
  }
}

function closeMenu(returnFocus = false) {
  menuOpen.value = false
  if (returnFocus) avatarButton.value?.focus()
}

function onDocumentClick(event) {
  if (menuOpen.value && !event.target.closest('[data-account-menu]')) {
    closeMenu()
  }
}

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))

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
        <RouterLink to="/" :class="navClass($route.name === 'home')">
          Início
        </RouterLink>
        <RouterLink to="/produtos" :class="navClass($route.name === 'products' || $route.name === 'product-detail')">
          Produtos
        </RouterLink>
        <span class="flex-1"></span>
        <RouterLink
          v-if="!auth.isAuthenticated"
          to="/login"
          class="rounded-lg border-2 border-ink px-4 py-1 font-bold hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
        >
          Entrar
        </RouterLink>
        <div v-else data-account-menu class="relative" @keydown.escape="closeMenu(true)">
          <button
            ref="avatarButton"
            type="button"
            :aria-expanded="menuOpen"
            aria-haspopup="menu"
            aria-label="Abrir menu da conta"
            @click="toggleMenu"
            class="flex size-9 items-center justify-center rounded-full border-2 border-ink bg-cream font-display text-base hover:border-flame hover:text-flame focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
          >
            {{ initial }}
          </button>
          <div
            v-if="menuOpen"
            ref="menuPanel"
            role="menu"
            aria-label="Conta"
            class="absolute right-0 z-50 mt-2 w-64 rounded-xl border-2 border-ink bg-paper p-2 shadow-[6px_6px_0_var(--color-ink)]"
          >
            <p class="truncate px-3 pb-1 pt-2 font-bold">{{ auth.user?.name }}</p>
            <p class="truncate px-3 pb-2 text-sm text-ink-soft">{{ auth.user?.email }}</p>
            <div class="border-t-2 border-line">
              <RouterLink
                to="/app"
                role="menuitem"
                @click="closeMenu()"
                class="mt-1 block rounded-lg px-3 py-2 font-semibold hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
              >
                Minha área
              </RouterLink>
              <RouterLink
                to="/app/produtos"
                role="menuitem"
                @click="closeMenu()"
                class="block rounded-lg px-3 py-2 font-semibold hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
              >
                Gerenciar produtos
              </RouterLink>
              <button
                type="button"
                role="menuitem"
                @click="onLogout"
                class="block w-full rounded-lg px-3 py-2 text-left font-semibold text-blood hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
              >
                Sair
              </button>
            </div>
          </div>
        </div>
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
