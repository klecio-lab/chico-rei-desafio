<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const auth = useAuthStore()

async function onLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <header class="border-b border-gray-200">
    <nav class="mx-auto flex w-full max-w-4xl items-center gap-4 px-4 py-3 text-sm font-medium" aria-label="Navegação principal">
      <RouterLink to="/" class="hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600">
        Início
      </RouterLink>
      <RouterLink
        v-if="auth.isAuthenticated"
        to="/app"
        class="hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600"
      >
        Minha área
      </RouterLink>
      <span class="flex-1"></span>
      <RouterLink
        v-if="!auth.isAuthenticated"
        to="/login"
        class="hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600"
      >
        Entrar
      </RouterLink>
      <button
        v-else
        type="button"
        @click="onLogout"
        class="rounded-md px-2 py-1 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600"
      >
        Sair ({{ auth.user?.name ?? auth.user?.email }})
      </button>
    </nav>
  </header>

  <main>
    <RouterView />
  </main>
</template>
