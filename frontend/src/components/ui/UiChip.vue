<template>
  <span
    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
    :class="variantClass"
  >
    <slot />
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

type ChipVariant = 'default' | 'success' | 'warning' | 'danger';

const props = withDefaults(
  defineProps<{
    variant?: ChipVariant;
  }>(),
  {
    variant: 'default',
  },
);

const variantClass = computed(() => {
  if (props.variant === 'success') {
    return 'bg-emerald-100 text-emerald-700';
  }

  if (props.variant === 'warning') {
    return 'bg-amber-100 text-amber-700';
  }

  if (props.variant === 'danger') {
    return 'bg-rose-100 text-rose-700';
  }

  return 'bg-slate-100 text-slate-700';
});
</script>
