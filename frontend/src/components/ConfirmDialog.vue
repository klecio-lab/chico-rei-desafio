<script setup>
import { ref, watch, nextTick } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, required: true },
  message: { type: String, required: true },
  confirmLabel: { type: String, default: 'Confirmar' },
  busy: { type: Boolean, default: false },
})

const emit = defineEmits(['confirm', 'cancel'])

const cancelButton = ref(null)

watch(
  () => props.open,
  async (isOpen) => {
    if (isOpen) {
      await nextTick()
      cancelButton.value?.focus()
    }
  },
)

function onKeydown(event) {
  if (event.key === 'Escape') emit('cancel')
}
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-40 flex items-center justify-center bg-ink/60 p-4"
    @click.self="emit('cancel')"
    @keydown="onKeydown"
  >
    <div
      role="dialog"
      aria-modal="true"
      :aria-label="title"
      class="w-full max-w-md rounded-xl border-2 border-ink bg-paper p-6 shadow-[8px_8px_0_var(--color-ink)]"
      style="overscroll-behavior: contain"
    >
      <p class="font-display text-2xl">{{ title }}</p>
      <p class="mt-2 text-ink-soft">{{ message }}</p>
      <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <button
          ref="cancelButton"
          type="button"
          :disabled="busy"
          @click="emit('cancel')"
          class="rounded-lg border-2 border-ink px-4 py-2 font-bold hover:bg-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame disabled:cursor-not-allowed disabled:opacity-50"
        >
          Cancelar
        </button>
        <button
          type="button"
          :disabled="busy"
          @click="emit('confirm')"
          class="rounded-lg border-2 border-blood bg-blood px-4 py-2 font-bold text-white hover:brightness-110 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-flame disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ busy ? 'Excluindo…' : confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
