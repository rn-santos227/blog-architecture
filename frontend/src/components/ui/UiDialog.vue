<template>
  <div class="flex gap-3 rounded-lg border p-4" :class="containerClass">
    <div class="text-xl" :class="iconClass">{{ icon }}</div>
    <div class="space-y-1">
      <p v-if="title" class="text-sm font-semibold" :class="titleClass">{{ title }}</p>
      <p v-if="message" class="text-sm" :class="messageClass">{{ message }}</p>
      <div v-else class="text-sm" :class="messageClass">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type DialogVariant = 'success' | 'warning' | 'error' | 'info'

const props = withDefaults(
  defineProps<{
    variant?: DialogVariant
    title?: string
    message?: string
  }>(),
  {
    variant: 'info',
    title: '',
    message: '',
  },
)

const variantConfig: Record<DialogVariant, { container: string; icon: string; title: string; message: string; symbol: string }> = {
  success: {
    container: 'border-emerald-200 bg-emerald-50',
    icon: 'text-emerald-600',
    title: 'text-emerald-800',
    message: 'text-emerald-700',
    symbol: '✓',
  },
  warning: {
    container: 'border-amber-200 bg-amber-50',
    icon: 'text-amber-600',
    title: 'text-amber-800',
    message: 'text-amber-700',
    symbol: '⚠',
  },
  error: {
    container: 'border-red-200 bg-red-50',
    icon: 'text-red-600',
    title: 'text-red-800',
    message: 'text-red-700',
    symbol: '✕',
  },
  info: {
    container: 'border-sky-200 bg-sky-50',
    icon: 'text-sky-600',
    title: 'text-sky-800',
    message: 'text-sky-700',
    symbol: 'ℹ',
  },
}

const variantState = computed(() => variantConfig[props.variant])
const containerClass = computed(() => variantState.value.container)
const iconClass = computed(() => variantState.value.icon)
const titleClass = computed(() => variantState.value.title)
const messageClass = computed(() => variantState.value.message)
const icon = computed(() => variantState.value.symbol)
</script>