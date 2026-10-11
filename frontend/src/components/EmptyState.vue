<script setup>
defineProps({
  title: { type: String, required: true },
  message: { type: String, default: '' },
})
</script>

<template>
  <div class="rounded-xl border-2 border-dashed border-line px-4 py-12 text-center">
    <p class="font-display text-xl">{{ title }}</p>
    <p v-if="message" class="mx-auto mt-2 max-w-md text-ink-soft">{{ message }}</p>
    <div v-if="$slots.action" class="mt-6 flex justify-center">
      <slot name="action" />
    </div>
  </div>
</template>
