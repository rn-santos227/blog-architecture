<template>
  <label
    class="inline-flex items-center gap-2 text-sm text-slate-700"
    :class="{ 'cursor-not-allowed text-slate-400': disabled }"
  >
    <input
      class="h-4 w-4 text-blue-600 focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed"
      type="radio"
      :name="name"
      :value="value"
      :checked="modelValue === value"
      :disabled="disabled"
      @change="$emit('update:modelValue', value)"
    />
    <span class="select-none">
      <slot>{{ label }}</slot>
    </span>
  </label>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string;
    value: string;
    name?: string;
    label?: string;
    disabled?: boolean;
  }>(),
  {
    name: '',
    label: '',
    disabled: false,
  },
);

defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>();
</script>
