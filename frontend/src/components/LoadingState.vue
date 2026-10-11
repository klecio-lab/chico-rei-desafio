<script setup>
defineProps({ message: { type: String, default: 'Carregando…' } })
</script>

<template>
  <p role="status" class="flex items-center gap-3">
    <span aria-hidden="true" class="inline-block size-5 animate-spin rounded-full border-2 border-line border-t-flame" />
    {{ message }}
  </p>
</template>
