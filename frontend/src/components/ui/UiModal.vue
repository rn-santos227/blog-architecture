<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6"
    >
      <button
        class="absolute inset-0 bg-slate-900/40"
        type="button"
        @click="handleClose"
      ></button>
      <div
        class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p v-if="title" class="text-lg font-semibold text-slate-900">{{ title }}</p>
            <p v-if="description" class="text-sm text-slate-500">{{ description }}</p>
          </div>
          <button
            class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
            type="button"
            aria-label="Close modal"
            @click="handleClose"
          >
            ✕
          </button>
        </div>

        <div class="mt-6">
          <slot />
        </div>

        <div v-if="$slots.footer" class="mt-6 flex justify-end gap-3">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps<{
  isOpen: boolean
  title?: string
  description?: string
}>()

const emit = defineEmits<{
  (event: 'close'): void
}>()

const handleClose = () => {
  if (!props.isOpen) return
  emit('close')
}
</script>
