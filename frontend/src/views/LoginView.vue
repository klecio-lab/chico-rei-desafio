<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppButton from '../components/AppButton.vue'
import FieldError from '../components/FieldError.vue'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const fieldErrors = ref({})
const generalError = ref('')
const loading = ref(false)

async function onSubmit() {
  if (loading.value) return
  loading.value = true
  fieldErrors.value = {}
  generalError.value = ''
  try {
    await auth.login(email.value, password.value)
    router.push({ name: 'app' })
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
  <section class="mx-auto w-full max-w-md px-4 py-14">
    <p class="font-display text-lg text-flame">Bom te ver de novo</p>
    <h1 class="mt-2">Entrar</h1>

    <div class="mt-6 rounded-xl border-2 border-ink bg-paper p-6 shadow-[6px_6px_0_var(--color-ink)]">
      <p
        v-if="generalError"
        role="alert"
        class="rounded-lg border-2 border-blood bg-paper px-4 py-2 text-sm font-medium text-blood"
      >
        {{ generalError }}
      </p>

      <form @submit.prevent="onSubmit" novalidate class="mt-4 flex flex-col gap-4">
        <div>
          <label for="email" class="mb-1 block text-sm font-bold">E-mail</label>
          <input
            id="email"
            v-model="email"
            type="email"
            name="email"
            autocomplete="email"
            :spellcheck="false"
            placeholder="voce@exemplo.com…"
            :aria-invalid="Boolean(fieldErrors.email?.length)"
            :aria-describedby="fieldErrors.email?.length ? 'email-error' : undefined"
            class="w-full rounded-lg border-2 border-ink bg-paper px-3 py-2 text-base focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-flame"
          />
          <FieldError :messages="fieldErrors.email ?? []" error-id="email-error" />
        </div>

        <div>
          <label for="password" class="mb-1 block text-sm font-bold">Senha</label>
          <input
            id="password"
            v-model="password"
            type="password"
            name="password"
            autocomplete="current-password"
            placeholder="Sua senha…"
            :aria-invalid="Boolean(fieldErrors.password?.length)"
            :aria-describedby="fieldErrors.password?.length ? 'password-error' : undefined"
            class="w-full rounded-lg border-2 border-ink bg-paper px-3 py-2 text-base focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-flame"
          />
          <FieldError :messages="fieldErrors.password ?? []" error-id="password-error" />
        </div>

        <AppButton :loading="loading">
          {{ loading ? 'Entrando…' : 'Entrar' }}
        </AppButton>
      </form>
    </div>
  </section>
</template>
