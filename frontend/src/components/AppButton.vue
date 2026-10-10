<script setup>
defineProps({
  type: { type: String, default: 'submit' },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex w-full items-center justify-center rounded-lg border-2 border-ink bg-ink px-5 py-2.5 text-base font-bold text-paper transition-colors hover:border-flame hover:bg-flame hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame active:translate-y-px disabled:cursor-not-allowed disabled:opacity-60"
  >
    <slot />
  </button>
</template>
