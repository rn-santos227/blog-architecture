<template>
  <div class="inline-flex items-center justify-center" role="status" aria-live="polite">
    <svg
      class="animate-spin text-blue-600"
      :class="sizeClass"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
      />
    </svg>
    <span class="sr-only">{{ label }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

type SpinnerSize = 'sm' | 'md' | 'lg';

const props = withDefaults(
  defineProps<{
    size?: SpinnerSize;
    label?: string;
  }>(),
  {
    size: 'md',
    label: 'Loading',
  },
);

const sizeClass = computed(() => {
  if (props.size === 'sm') return 'h-4 w-4';
  if (props.size === 'lg') return 'h-10 w-10';
  return 'h-6 w-6';
});
</script>
