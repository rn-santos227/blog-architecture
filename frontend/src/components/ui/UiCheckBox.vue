<template>
  <label
    class="inline-flex items-center gap-2 text-sm text-slate-700"
    :class="{ 'cursor-not-allowed text-slate-400': disabled }"
  >
    <input
      class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed"
      type="checkbox"
      :checked="modelValue"
      :disabled="disabled"
      @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
    />
    <span class="select-none">
      <slot>{{ label }}</slot>
    </span>
  </label>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: boolean;
    label?: string;
    disabled?: boolean;
  }>(),
  {
    label: '',
    disabled: false,
  },
);

defineEmits<{
  (event: 'update:modelValue', value: boolean): void;
}>();
</script>
