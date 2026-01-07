<template>
  <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <button
      type="button"
      class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left"
      @click="toggle"
    >
      <div class="flex flex-col gap-1">
        <slot name="title" />
      </div>
      <span class="text-sm font-semibold text-slate-500">
        {{ isOpen ? 'Hide' : 'Show' }}
      </span>
    </button>
    <div v-show="isOpen" class="border-t border-slate-100 px-4 py-4">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = withDefaults(
  defineProps<{
    defaultOpen?: boolean;
  }>(),
  {
    defaultOpen: false,
  },
);

const isOpen = ref(props.defaultOpen);

const toggle = () => {
  isOpen.value = !isOpen.value;
};
</script>
