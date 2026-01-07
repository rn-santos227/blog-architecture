<template>
  <button
    :type="type"
    class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold transition"
    :class="[variantClass, { 'cursor-not-allowed opacity-60': disabled }]"
    :disabled="disabled"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';

type ButtonVariant = 'primary' | 'secondary' | 'ghost';

const props = withDefaults(
  defineProps<{
    type?: 'button' | 'submit' | 'reset';
    variant?: ButtonVariant;
    disabled?: boolean;
  }>(),
  {
    type: 'button',
    variant: 'primary',
    disabled: false,
  },
);

const variantClass = computed(() => {
  if (props.variant === 'secondary') {
    return 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50';
  }

  if (props.variant === 'ghost') {
    return 'text-slate-600 hover:bg-slate-100';
  }

  return 'bg-blue-600 text-white hover:bg-blue-700';
});
</script>