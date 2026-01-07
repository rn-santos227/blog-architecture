<template>
  <label class="flex flex-col gap-2 text-sm text-slate-700">
    <span v-if="label" class="font-medium">{{ label }}</span>
    <textarea
      :class="[
        'min-h-[120px] resize-y rounded-md border px-3 py-2 text-slate-900 shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400',
        errorMessage
          ? 'border-red-400 focus:border-red-500 focus:ring-red-200'
          : 'border-slate-300 focus:border-blue-500 focus:ring-blue-200',
      ]"
      :placeholder="placeholder"
      :rows="rows"
      :value="modelValue"
      :disabled="disabled"
      :aria-invalid="Boolean(errorMessage)"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
    <p v-if="errorMessage" class="text-xs text-red-600">{{ errorMessage }}</p>
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
    errorMessage?: string;
  }>(),
  {
    label: '',
    placeholder: '',
    rows: 4,
    disabled: false,
    errorMessage: '',
  },
);

defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();
</script>
