<template>
  <UiAccordion :default-open="false">
    <template #title>
      <span class="text-sm font-semibold uppercase tracking-wide text-blue-600">Advanced search</span>
      <h2 class="text-lg font-semibold text-slate-900">Find published posts</h2>
    </template>

    <form class="space-y-4" @submit.prevent="onSubmit">
      <div class="grid gap-4 md:grid-cols-2">
        <UiTextField v-model="query" label="Search keywords" placeholder="Laravel, caching, performance..." />
        <UiComboBox
          v-model="tags"
          label="Tags"
          placeholder="Type to search tags"
          :options="tagOptions"
          multiple
          allow-custom
          @search="handleTagSearch"
        />
      </div>
      <div class="grid gap-4 md:grid-cols-3">
        <UiTextField v-model="authorId" label="Author ID" placeholder="42" />
        <label class="flex flex-col gap-2 text-sm text-slate-700">
          <span class="font-medium">From date</span>
          <input
            v-model="fromDate"
            type="date"
            class="rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
          />
        </label>
        <label class="flex flex-col gap-2 text-sm text-slate-700">
          <span class="font-medium">To date</span>
          <input
            v-model="toDate"
            type="date"
            class="rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
          />
        </label>
      </div>
      <div class="flex flex-wrap items-end gap-3">
        <label class="flex flex-col gap-2 text-sm text-slate-700">
          <span class="font-medium">Limit</span>
          <input
            v-model.number="limit"
            type="number"
            min="1"
            max="50"
            class="w-24 rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
          />
        </label>
        <UiButton type="submit" :disabled="isLoading">
          {{ isLoading ? 'Searching...' : 'Search Posts' }}
        </UiButton>
        <UiButton variant="secondary" type="button" :disabled="isLoading" @click="resetFilters">
          Reset Filters
        </UiButton>
      </div>
    </form>

    <div class="mt-6 space-y-4">
      <div v-if="errorMessage" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
        {{ errorMessage }}
      </div>
    </div>
  </UiAccordion>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiAccordion from '@/components/ui/UiAccordion.vue';
import UiComboBox from '@/components/ui/UiComboBox.vue';
import UiTextField from '@/components/ui/UiTextField.vue';
import { useBlogStore } from '@/stores/blog';
import { useTagsStore } from '@/stores/tags';

const query = ref('');
const tags = ref<string[]>([]);
const authorId = ref('');
const fromDate = ref('');
const toDate = ref('');
const limit = ref(10);

const errorMessage = ref('');
const lastTagQuery = ref('');

const blogStore = useBlogStore();
const tagsStore = useTagsStore();
const tagOptions = computed(() =>
  tagsStore.tags.map((tag) => ({
    label: tag.name,
    value: tag.name,
  })),
);

const isLoading = computed(() => blogStore.isLoading);

type SearchParams = {
  q?: string;
  tags?: string[];
  author_id?: number;
  from?: string;
  to?: string;
  limit?: number;
};

const buildParams = (): SearchParams => {
  const params: SearchParams = {
    limit: limit.value,
  };

  const trimmedQuery = query.value.trim();
  if (trimmedQuery.length >= 2) {
    params.q = trimmedQuery;
  }

  if (tags.value.length) {
    params.tags = tags.value;
  }

  const trimmedAuthorId = authorId.value.trim();
  if (trimmedAuthorId) {
    params.author_id = Number(trimmedAuthorId);
  }

  if (fromDate.value) {
    params.from = fromDate.value;
  }

  if (toDate.value) {
    params.to = toDate.value;
  }

  return params;
};

const validateSearch = () => {
  errorMessage.value = '';
  const trimmedQuery = query.value.trim();
  if (!trimmedQuery && !tags.value.length) {
    errorMessage.value = 'Enter a search term or at least one tag to begin.';
    return false;
  }

  if (trimmedQuery && trimmedQuery.length < 2) {
    errorMessage.value = 'Search keywords must be at least 2 characters.';
    return false;
  }

  return true;
};

const onSubmit = () => {
  if (!validateSearch()) return;
  blogStore.searchPosts(buildParams());
};

const resetFilters = () => {
  query.value = '';
  tags.value = [];
  authorId.value = '';
  fromDate.value = '';
  toDate.value = '';
  limit.value = 10;
  errorMessage.value = '';
  blogStore.resetSearch();
};

onMounted(() => {
  tagsStore.fetchTags();
});

const handleTagSearch = (input: string) => {
  if (tagsStore.isLoading) return;
  const trimmed = input.trim();
  if (!trimmed) {
    if (lastTagQuery.value) {
      lastTagQuery.value = '';
      tagsStore.fetchTags();
    }
    return;
  }
  if (trimmed === lastTagQuery.value) return;
  lastTagQuery.value = trimmed;
  tagsStore.fetchTags(trimmed);
};
</script>
