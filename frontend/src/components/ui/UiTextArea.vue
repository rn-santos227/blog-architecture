<template>
  <label class="flex flex-col gap-2 text-sm text-slate-700">
    <span v-if="label" class="font-medium">{{ label }}</span>
    <textarea
      class="min-h-[120px] resize-y rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
      :placeholder="placeholder"
      :rows="rows"
      :value="modelValue"
      :disabled="disabled"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
  </label>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string;
    label?: string;
    placeholder?: string;
    rows?: number;
    disabled?: boolean;
  }>(),
  {
    label: '',
    placeholder: '',
    rows: 4,
    disabled: false,
  },
);

defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();
</script>
