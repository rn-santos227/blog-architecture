<template>
  <form class="flex flex-wrap items-end gap-3" @submit.prevent="submitSearch">
    <UiTextField
      v-model="query"
      label="Search your posts"
      type="search"
      placeholder="Search by keyword or content"
      :disabled="isLoading"
    />
    <div class="flex flex-wrap gap-2">
      <UiButton
        type="submit"
        :disabled="isLoading || query.trim().length < 2"
      >
        {{ isLoading ? 'Searching...' : 'Search' }}
      </UiButton>
      <UiButton
        type="button"
        variant="secondary"
        :disabled="isLoading || !query"
        @click="clearSearch"
      >
        Clear
      </UiButton>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiTextField from '@/components/ui/UiTextField.vue'

const props = withDefaults(
  defineProps<{
    isLoading?: boolean
  }>(),
  {
    isLoading: false,
  },
)

const emit = defineEmits<{
  (event: 'search', query: string): void
  (event: 'clear'): void
}>()

const query = ref<string>('')

const submitSearch = (): void => {
  const value = query.value.trim()
  if (value.length < 2) return
  emit('search', value)
}

const clearSearch = (): void => {
  query.value = ''
  emit('clear')
}
</script>
