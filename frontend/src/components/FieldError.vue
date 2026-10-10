<script setup>
defineProps({
  messages: { type: Array, default: () => [] },
  errorId: { type: String, default: undefined },
})
</script>

<template>
  <p v-if="messages.length" :id="errorId" role="alert" class="mt-1 text-sm text-red-600">
    {{ messages[0] }}
  </p>
</template>
