<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppButton from '../components/AppButton.vue'

const router = useRouter()
const auth = useAuthStore()

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
  <section class="mx-auto w-full max-w-xl px-4 py-16">
    <h1>Minha área</h1>
    <p class="mt-4">
      Logado como <strong>{{ auth.user?.name }} ({{ auth.user?.email }})</strong>
    </p>
    <div class="mt-8 max-w-xs">
      <AppButton type="button" @click="onLogout">Sair</AppButton>
    </div>
  </section>
</template>
