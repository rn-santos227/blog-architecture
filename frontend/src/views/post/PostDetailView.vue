<template>
  <main class="mx-auto w-full max-w-4xl px-6 py-10">
    <div
      v-if="isLoading"
      class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-500"
    >
      <UiSpinner size="lg" label="Loading post" />
      <span>Loading post...</span>
    </div>
    <div v-else-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700">
      {{ errorMessage }}
    </div>
    <article v-else-if="post" class="space-y-6">
      <div class="space-y-2">
        <RouterLink to="/" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
          ← Back to all posts
        </RouterLink>
        <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl">{{ post.title }}</h1>
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
          <span class="font-medium text-slate-700">{{ post.user.name }}</span>
          <span v-if="formattedDate">• {{ formattedDate }}</span>
        </div>
        <div class="flex flex-wrap gap-2" v-if="post.tags?.length">
          <UiChip v-for="tag in post.tags" :key="tag.id">
            {{ tag.name }}
          </UiChip>
        </div>
      </div>
      <div class="prose prose-slate max-w-none whitespace-pre-line">
        {{ post.body }}
      </div>
    </article>
  </main>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import UiChip from '@/components/ui/UiChip.vue';
import UiSpinner from '@/components/ui/UiSpinner.vue';
import { useBlogStore } from '@/stores/blog';

const route = useRoute();
const blogStore = useBlogStore();

const post = computed(() => blogStore.selectedPost);
const isLoading = computed(() => blogStore.isDetailLoading);
const errorMessage = computed(() => blogStore.detailErrorMessage);

const formattedDate = computed(() => {
  if (!post.value?.published_at) return '';
  const date = new Date(post.value.published_at);
  if (Number.isNaN(date.valueOf())) return '';
  return date.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
});

const fetchPost = () => {
  const id = route.params.id;
  if (!id) return;
  blogStore.fetchPost(String(id));
};

onMounted(fetchPost);
watch(
  () => route.params.id,
  () => {
    blogStore.clearSelectedPost();
    fetchPost();
  },
);
</script>
