<template>
  <main class="mx-auto w-full max-w-6xl px-6 py-10">
    <section class="space-y-3">
      <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Published Stories</p>
      <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl">Latest Blog Previews</h1>
    </section>

    <section class="mt-8">
      <AdvanceBlogSearch />
    </section>

    <section class="mt-8">
      <div v-if="isLoading" class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-500">
        Loading published posts...
      </div>
      <div v-else-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700">
        {{ errorMessage }}
      </div>
      <div v-else-if="posts.length === 0" class="rounded-2xl border border-slate-200 p-6 text-slate-500">
       <span v-if="isSearchMode && hasSearched">No posts matched your search. Try adjusting your filters.</span>
        <span v-else>No published posts yet. Check back soon!</span>
      </div>
      <div v-else class="space-y-8">
        <div class="grid gap-6 md:grid-cols-2">
          <BlogCard v-for="post in posts" :key="post.id" :post="post" />
        </div>
        <div class="flex flex-col items-center gap-2 text-sm text-slate-500">
          <div v-if="isLoadingMore" class="rounded-full border border-slate-200 px-4 py-2">
            Loading more posts...
          </div>
          <div v-else-if="hasMore" ref="loadMoreTrigger" class="h-10 w-full"></div>
          <div v-else class="text-slate-400">You've reached the end.</div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import AdvanceBlogSearch from '@/components/blog/AdvanceBlogSearch.vue';
import BlogCard from '@/components/blog/BlogCard.vue';
import { useBlogStore } from '@/stores/blog';

const blogStore = useBlogStore();
const loadMoreTrigger = ref<HTMLElement | null>(null);

const posts = computed(() => blogStore.posts);
const isLoading = computed(() => blogStore.isLoading);
const isLoadingMore = computed(() => blogStore.isLoadingMore);
const errorMessage = computed(() => blogStore.errorMessage);
const hasMore = computed(() => blogStore.hasMore);
const isSearchMode = computed(() => blogStore.isSearchMode);
const hasSearched = computed(() => blogStore.hasSearched);

let observer: IntersectionObserver | null = null;

const setupObserver = () => {
  if (!loadMoreTrigger.value) return;

  observer?.disconnect();
  observer = new IntersectionObserver(
    (entries) => {
      const firstEntry = entries[0];
      if (firstEntry?.isIntersecting) {
        if (blogStore.isSearchMode) {
          blogStore.fetchMoreSearchPosts();
        } else {
          blogStore.fetchMorePosts();
        }
      }
    },
    {
      rootMargin: '200px',
    },
  );

  observer.observe(loadMoreTrigger.value);
};

onMounted(() => {
  blogStore.fetchPosts();
});

watch(loadMoreTrigger, () => {
  setupObserver();
});

onUnmounted(() => {
  observer?.disconnect();
});
</script>
