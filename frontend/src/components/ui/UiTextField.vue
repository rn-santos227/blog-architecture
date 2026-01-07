<template>
  <label class="flex flex-col gap-2 text-sm text-slate-700">
    <span v-if="label" class="font-medium">{{ label }}</span>
    <input
      class="rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      :disabled="disabled"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
  </label>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string;
    label?: string;
    placeholder?: string;
    type?: 'text' | 'email' | 'password' | 'search' | 'tel' | 'url';
    disabled?: boolean;
  }>(),
  {
    label: '',
    placeholder: '',
    type: 'text',
    disabled: false,
  },
);

defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();
</script>
