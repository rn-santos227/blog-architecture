<template>
  <label class="flex flex-col gap-2 text-sm text-slate-700">
    <span v-if="label" class="font-medium">{{ label }}</span>
    <select
      class="rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
      :value="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <option v-if="placeholder" disabled value="">
        {{ placeholder }}
      </option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  </label>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string;
    label?: string;
    placeholder?: string;
    options: Array<{ label: string; value: string }>;
    disabled?: boolean;
  }>(),
  {
    label: '',
    placeholder: '',
    disabled: false,
  },
);

defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();
</script>
