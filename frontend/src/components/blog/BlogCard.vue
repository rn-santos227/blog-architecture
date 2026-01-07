<template>
  <UiCard class="flex h-full flex-col transition hover:-translate-y-1 hover:shadow-md">
    <template #title>
      <div class="flex flex-col gap-2">
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
          <span class="font-medium text-slate-700">{{ post.user.name }}</span>
          <span v-if="formattedDate">• {{ formattedDate }}</span>
        </div>
        <RouterLink :to="`/posts/${post.id}`" class="text-xl font-semibold text-slate-900 hover:text-blue-700">
          {{ post.title }}
        </RouterLink>
      </div>
    </template>

    <div class="space-y-4">
      <p class="text-sm text-slate-600">
        {{ previewText }}
      </p>
      <div class="flex flex-wrap gap-2" v-if="post.tags?.length">
        <UiChip v-for="tag in post.tags" :key="tag.id">
          {{ tag.name }}
        </UiChip>
      </div>
    </div>

    <template #footer>
      <RouterLink
        :to="`/posts/${post.id}`"
        class="text-sm font-semibold text-blue-600 hover:text-blue-700"
      >
        Read more →
      </RouterLink>
    </template>
  </UiCard>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import UiCard from '../ui/UiCard.vue';
import UiChip from '../ui/UiChip.vue';
import type { Post } from '../../@types/blog';

const props = defineProps<{
  post: Post;
}>();

const previewText = computed(() => {
  const text = props.post.body ?? '';
  const trimmed = text.replace(/\s+/g, ' ').trim();
  return trimmed.length > 200 ? `${trimmed.slice(0, 200)}...` : trimmed;
});

const formattedDate = computed(() => {
  if (!props.post.published_at) return '';
  const date = new Date(props.post.published_at);
  if (Number.isNaN(date.valueOf())) return '';
  return date.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
});
</script>
