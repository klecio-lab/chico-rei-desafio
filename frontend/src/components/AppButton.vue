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
    class="inline-flex w-full items-center justify-center rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600 disabled:cursor-not-allowed disabled:opacity-60"
  >
    <slot />
  </button>
</template>
