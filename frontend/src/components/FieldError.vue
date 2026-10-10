<script setup>
defineProps({
  messages: { type: Array, default: () => [] },
  errorId: { type: String, default: undefined },
})
</script>

<template>
  <p v-if="messages.length" :id="errorId" role="alert" class="mt-1 text-sm font-medium text-blood">
    {{ messages[0] }}
  </p>
</template>
