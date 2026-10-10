<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <section class="mx-auto w-full max-w-xl px-4 py-16 text-center">
    <h1>Chico Rei — Desafio</h1>
    <p class="mt-4">Aplicação de demonstração com autenticação consumindo a API Laravel.</p>
    <RouterLink
      to="/login"
      class="mt-8 inline-flex items-center justify-center rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600"
    >
      Ir para o login
    </RouterLink>
  </section>
</template>
