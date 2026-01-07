<template>
  <label class="flex flex-col gap-2 text-sm text-slate-700">
    <span v-if="label" class="font-medium">{{ label }}</span>
    <div class="relative">
      <input
        class="w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
        :type="resolvedType"
        :placeholder="placeholder"
        :value="modelValue"
        :disabled="disabled"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      />
      <button
        v-if="showToggle"
        class="absolute inset-y-0 right-3 text-xs font-semibold uppercase tracking-wide text-slate-500 hover:text-slate-700"
        type="button"
        @click="toggleVisibility"
      >
        {{ isVisible ? 'Hide' : 'Show' }}
      </button>
    </div>
  </label>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    modelValue: string;
    label?: string;
    placeholder?: string;
    type?: 'text' | 'email' | 'password' | 'search' | 'tel' | 'url';
    disabled?: boolean;
    revealable?: boolean;
  }>(),
  {
    label: '',
    placeholder: '',
    type: 'text',
    disabled: false,
    revealable: false,
  },
);

defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();

const isVisible = ref(false);

const showToggle = computed(() => props.type === 'password' && props.revealable);
const resolvedType = computed(() => {
  if (props.type !== 'password') return props.type;
  return isVisible.value ? 'text' : 'password';
});

const toggleVisibility = () => {
  isVisible.value = !isVisible.value;
};
</script>
