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
          placeholder="Type and press enter to add"
          :options="tagOptions"
          multiple
          allow-custom
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
          {{ isLoading ? 'Searching...' : 'Search posts' }}
        </UiButton>
        <UiButton variant="secondary" type="button" :disabled="isLoading" @click="resetFilters">
          Reset filters
        </UiButton>
      </div>
    </form>

    <div class="mt-6 space-y-4">
      <div v-if="errorMessage" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
        {{ errorMessage }}
      </div>
      <div v-else-if="hasSearched && results.length === 0" class="rounded-md border border-slate-200 p-4 text-sm text-slate-500">
        No posts matched your search. Try adjusting your filters.
      </div>
      <div v-else-if="results.length" class="space-y-4">
        <div class="grid gap-6 md:grid-cols-2">
          <BlogCard v-for="post in results" :key="post.id" :post="post" />
        </div>
        <div class="flex items-center gap-3 text-sm text-slate-500">
          <UiButton
            v-if="canLoadMore"
            variant="ghost"
            type="button"
            :disabled="isLoadingMore"
            @click="loadMore"
          >
            {{ isLoadingMore ? 'Loading more...' : 'Load more results' }}
          </UiButton>
          <span v-else-if="hasSearched">No more results.</span>
        </div>
      </div>
    </div>
  </UiAccordion>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import api from '@/services/api';
import type { Post } from '@/@types/blog';
import BlogCard from '@/components/blog/BlogCard.vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiAccordion from '@/components/ui/UiAccordion.vue';
import UiComboBox from '@/components/ui/UiComboBox.vue';
import UiTextField from '@/components/ui/UiTextField.vue';
import { useTagsStore } from '@/stores/tags';

const query = ref('');
const tags = ref<string[]>([]);
const authorId = ref('');
const fromDate = ref('');
const toDate = ref('');
const limit = ref(10);
const page = ref(1);

const results = ref<Post[]>([]);
const isLoading = ref(false);
const isLoadingMore = ref(false);
const errorMessage = ref('');
const hasSearched = ref(false);
const lastFetchCount = ref(0);

const tagsStore = useTagsStore();
const tagOptions = computed(() =>
  tagsStore.tags.map((tag) => ({
    label: tag.name,
    value: tag.name,
  })),
);

const canLoadMore = computed(() => hasSearched.value && lastFetchCount.value >= limit.value);

const buildParams = (pageValue: number) => {
  const params: Record<string, string | number | string[]> = {
    page: pageValue,
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

const fetchResults = async (nextPage: number, append = false) => {
  if (!append) {
    isLoading.value = true;
  } else {
    isLoadingMore.value = true;
  }

  errorMessage.value = '';

  try {
    const response = await api.get<Post[]>('/posts/search', {
      params: buildParams(nextPage),
    });

    const fetchedPosts = response.data ?? [];
    lastFetchCount.value = fetchedPosts.length;
    results.value = append ? [...results.value, ...fetchedPosts] : fetchedPosts;
    page.value = nextPage;
    hasSearched.value = true;
  } catch (error) {
    console.error(error);
    errorMessage.value = 'Unable to search posts right now. Please try again later.';
  } finally {
    isLoading.value = false;
    isLoadingMore.value = false;
  }
};

const onSubmit = () => {
  if (!validateSearch()) return;
  fetchResults(1, false);
};

const loadMore = () => {
  if (isLoadingMore.value || isLoading.value || !canLoadMore.value) return;
  fetchResults(page.value + 1, true);
};

const resetFilters = () => {
  query.value = '';
  tags.value = [];
  authorId.value = '';
  fromDate.value = '';
  toDate.value = '';
  limit.value = 10;
  page.value = 1;
  results.value = [];
  lastFetchCount.value = 0;
  hasSearched.value = false;
  errorMessage.value = '';
};

onMounted(() => {
  tagsStore.fetchTags();
});
</script>
