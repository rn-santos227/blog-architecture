<template>
  <button
    :type="type"
    class="ui-button"
    :class="[variantClass, { 'is-disabled': disabled }]"
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

const variantClass = computed(() => `ui-button--${props.variant}`);
</script>

<style scoped>
.ui-button {
  @apply inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold transition;
}

.ui-button--primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}

.ui-button--secondary {
  @apply border border-slate-300 bg-white text-slate-700 hover:bg-slate-50;
}

.ui-button--ghost {
  @apply text-slate-600 hover:bg-slate-100;
}

.ui-button.is-disabled {
  @apply cursor-not-allowed opacity-60;
}
</style>
