<template>
  <main class="mx-auto w-full max-w-6xl px-6 py-10">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 class="text-3xl font-semibold text-slate-900">My Posts</h1>
        <p class="mt-2 text-sm text-slate-500">
          Review the posts you have created and track their publishing status.
        </p>
      </div>
    </div>

    <div v-if="isLoading" class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-500">
      Loading your posts...
    </div>
    <div v-else-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700">
      {{ errorMessage }}
    </div>
    <UiDataTable
      v-else
      :columns="columns"
      :rows="posts"
      row-key="id"
      empty-message="You have not created any posts yet."
      :pagination="paginationState"
      @next="handleNext"
      @prev="handlePrev"
    >
      <template #cell-title="{ row }">
        <div class="font-semibold text-slate-900">{{ row.title }}</div>
      </template>
      <template #cell-status="{ row }">
        <span
          class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
          :class="statusClass(row.status)"
        >
          {{ row.status ? row.status : 'draft' }}
        </span>
      </template>
      <template #cell-published_at="{ row }">
        <span class="text-slate-600">{{ formatDate(row.published_at) }}</span>
      </template>
      <template #cell-tags="{ row }">
        <div class="flex flex-wrap gap-2">
          <UiChip v-for="tag in row.tags" :key="tag.id">
            {{ tag.name }}
          </UiChip>
          <span v-if="!row.tags?.length" class="text-xs text-slate-400">No tags</span>
        </div>
      </template>
      <template #cell-actions="{ row }">
        <RouterLink
          class="text-sm font-semibold text-blue-600 hover:text-blue-700"
          :to="`/posts/${row.id}`"
        >
          View
        </RouterLink>
      </template>
    </UiDataTable>
  </main>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import UiChip from '@/components/ui/UiChip.vue'
import UiDataTable from '@/components/ui/UiDataTable.vue'
import api from '@/services/api'
import type { CursorPagination, Post } from '@/@types/blog'

const posts = ref<Post[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const nextCursor = ref<string | null>(null)
const prevCursor = ref<string | null>(null)

const columns = [
  { key: 'title', label: 'Title' },
  { key: 'status', label: 'Status' },
  { key: 'published_at', label: 'Published' },
  { key: 'tags', label: 'Tags' },
  { key: 'actions', label: 'Actions', headerClassName: 'text-right', className: 'text-right' },
]

const formatDate = (value: string | null) => {
  if (!value) return 'Not published'
  const date = new Date(value)
  if (Number.isNaN(date.valueOf())) return 'Not published'
  return date.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const statusClass = (status?: string) => {
  if (status === 'published') {
    return 'bg-emerald-100 text-emerald-700'
  }

  if (status === 'archived') {
    return 'bg-slate-200 text-slate-600'
  }

  return 'bg-amber-100 text-amber-700'
}

const fetchMyPosts = async (cursor: string | null = null) => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get<CursorPagination<Post>>('/posts/mine', {
      params: cursor ? { cursor } : undefined,
    })
    posts.value = response.data.data ?? []
    nextCursor.value = response.data.next_cursor ?? null
    prevCursor.value = response.data.prev_cursor ?? null
  } catch (error) {
    console.error(error)
    errorMessage.value = 'Unable to load your posts right now. Please try again later.'
    nextCursor.value = null
    prevCursor.value = null
  } finally {
    isLoading.value = false
  }
}

const handleNext = () => {
  if (nextCursor.value) {
    fetchMyPosts(nextCursor.value)
  }
}

const handlePrev = () => {
  if (prevCursor.value) {
    fetchMyPosts(prevCursor.value)
  }
}

const paginationState = computed(() => ({
  hasNext: Boolean(nextCursor.value),
  hasPrev: Boolean(prevCursor.value),
  isLoading: isLoading.value,
  nextLabel: 'Next',
  prevLabel: 'Previous',
}))

onMounted(fetchMyPosts)
</script>
