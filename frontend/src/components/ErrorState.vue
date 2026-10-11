<script setup>
defineProps({
  message: { type: String, required: true },
  retryable: { type: Boolean, default: false },
})
defineEmits(['retry'])
</script>

<template>
  <div role="alert" class="rounded-lg border-2 border-blood bg-paper px-4 py-3 text-sm font-medium text-blood">
    <p>{{ message }}</p>
    <button
      v-if="retryable"
      type="button"
      @click="$emit('retry')"
      class="mt-2 font-bold underline hover:no-underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame"
    >
      Tentar de novo
    </button>
  </div>
</template>
