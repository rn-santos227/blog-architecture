<template>
  <label class="flex flex-col gap-2 text-sm text-slate-700">
    <span v-if="label" class="font-medium">{{ label }}</span>
    <template v-if="multiple">
      <div class="space-y-2">
        <input
          v-model="inputValue"
          :list="listId"
          type="text"
          class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
          :placeholder="placeholder"
          :disabled="disabled"
          @keydown="handleKeydown"
          @input="handleInput"
          @blur="commitInput"
        />
        <datalist :id="listId">
          <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </datalist>
        <div v-if="selectedValues.length" class="flex flex-wrap gap-2">
          <span
            v-for="value in selectedValues"
            :key="value"
            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
          >
            <span>{{ labelForValue(value) }}</span>
            <button
              type="button"
              class="text-slate-500 transition hover:text-slate-700"
              @click="removeValue(value)"
            >
              ×
            </button>
          </span>
        </div>
      </div>
    </template>
    <select
      v-else
      class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
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
import { computed, ref } from 'vue';

const listId = `combo-${Math.random().toString(36).slice(2, 9)}`;

const props = withDefaults(
  defineProps<{
    modelValue: string | string[];
    label?: string;
    placeholder?: string;
    options: Array<{ label: string; value: string }>;
    disabled?: boolean;
    multiple?: boolean;
    allowCustom?: boolean;
  }>(),
  {
    label: '',
    placeholder: '',
    disabled: false,
    multiple: false,
    allowCustom: false,
  },
);

const emit = defineEmits<{
  (event: 'update:modelValue', value: string | string[]): void;
  (event: 'search', value: string): void;
}>();

const inputValue = ref('');

const selectedValues = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue;
  }

  return props.modelValue ? [props.modelValue] : [];
});

const labelForValue = (value: string) => {
  const match = props.options.find((option) => option.value === value);
  return match?.label ?? value;
};

const updateValues = (values: string[]) => {
  if (props.multiple) {
    emit('update:modelValue', values);
    return;
  }

  emit('update:modelValue', values[0] ?? '');
};

const addValue = (value: string) => {
  const trimmed = value.trim();
  if (!trimmed) return;

  const existsInOptions = props.options.some((option) => option.value === trimmed);
  if (!props.allowCustom && !existsInOptions) {
    inputValue.value = '';
    return;
  }

  if (selectedValues.value.includes(trimmed)) {
    inputValue.value = '';
    return;
  }

  updateValues([...selectedValues.value, trimmed]);
  inputValue.value = '';
};

const removeValue = (value: string) => {
  updateValues(selectedValues.value.filter((item) => item !== value));
};

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter' || event.key === ',') {
    event.preventDefault();
    addValue(inputValue.value.replace(',', ''));
  }
};

const handleInput = () => {
  emit('search', inputValue.value);
};

const commitInput = () => {
  addValue(inputValue.value);
};

</script>
