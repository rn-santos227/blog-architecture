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
    <transition name="accordion">
      <div v-show="isOpen" class="accordion-content border-t border-slate-100 px-4 py-4">
        <slot />
      </div>
    </transition>
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

<style scoped>
.accordion-content {
  overflow: hidden;
}

.accordion-enter-active,
.accordion-leave-active {
  transition: max-height 0.3s ease, opacity 0.3s ease;
}

.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}

.accordion-enter-to,
.accordion-leave-from {
  max-height: 800px;
  opacity: 1;
}
</style>
