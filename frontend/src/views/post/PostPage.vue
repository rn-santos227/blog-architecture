<template>
  <main class="mx-auto w-full max-w-6xl px-6 py-10">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
      <div>
        <div>
          <h1 class="text-3xl font-semibold text-slate-900">My Posts</h1>
          <p class="mt-2 text-sm text-slate-500">
            Review the posts you have created and track their publishing status.
          </p>
        </div>
      </div>
      <UiButton @click="openCreateModal">Create Post</UiButton>
    </div>

    <div class="mb-6">
      <PostSearchComponent
        :is-loading="isLoading"
        @search="handleSearch"
        @clear="clearSearch"
      />
    </div>

    <div
      v-if="isLoading"
      class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-500"
    >
      <UiSpinner size="lg" label="Loading your posts" />
      <span>Loading your posts...</span>
    </div>

    <div
      v-else-if="errorMessage"
      class="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-700"
    >
      {{ errorMessage }}
    </div>

    <UiDataTable
      v-else
      :columns="columns"
      :rows="posts"
      row-key="id"
      :empty-message="emptyMessage"
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
          {{ row.status ?? 'draft' }}
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
          <span v-if="row.tags.length === 0" class="text-xs text-slate-400">
            No tags
          </span>
        </div>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex justify-end gap-2">
          <UiButton
            variant="ghost"
            class="px-3 py-1 text-xs"
            @click="openUpdateModal(row)"
          >
            Edit
          </UiButton>
          <UiButton
            variant="secondary"
            class="px-3 py-1 text-xs text-red-600 hover:bg-red-50"
            @click="openDeleteModal(row)"
          >
            Delete
          </UiButton>
        </div>
      </template>
    </UiDataTable>

    <PostCreateComponent
      :is-open="isCreateModalOpen"
      :tag-options="tagOptions"
      @close="closeCreateModal"
      @created="handlePostCreated"
      @search-tags="fetchTags"
    />

    <PostUpdateComponent
      :is-open="isUpdateModalOpen"
      :post="activePost"
      :tag-options="tagOptions"
      @close="closeUpdateModal"
      @updated="handlePostUpdated"
      @search-tags="fetchTags"
    />

    <PostDeleteModal
      :is-open="isDeleteModalOpen"
      :post="activePost"
      @close="closeDeleteModal"
      @deleted="handlePostDeleted"
    />
  </main>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import UiChip from '@/components/ui/UiChip.vue'
import UiDataTable from '@/components/ui/UiDataTable.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiSpinner from '@/components/ui/UiSpinner.vue'
import PostCreateComponent from '@/components/post/PostCreateComponent.vue'
import PostSearchComponent from '@/components/post/PostSearchComponent.vue'
import PostUpdateComponent from '@/components/post/PostUpdateComponent.vue'
import PostDeleteModal from '@/components/post/PostDeleteComponent.vue'
import api from '@/services/api'
import type { CursorPagination, Post, Tag } from '@/@types/blog'

const posts = ref<Post[]>([])
const isLoading = ref<boolean>(false)
const errorMessage = ref<string>('')
const isSearching = ref<boolean>(false)
const hasSearched = ref<boolean>(false)

const nextCursor = ref<string | null>(null)
const prevCursor = ref<string | null>(null)

const tagOptions = ref<Array<{ label: string; value: string }>>([])

const isCreateModalOpen = ref<boolean>(false)
const isUpdateModalOpen = ref<boolean>(false)
const isDeleteModalOpen = ref<boolean>(false)

const activePost = ref<Post | null>(null)

type DataTableColumn = {
  key: string
  label: string
  headerClassName?: string
  className?: string
}

const columns: DataTableColumn[] = [
  { key: 'title', label: 'Title' },
  { key: 'status', label: 'Status' },
  { key: 'published_at', label: 'Published' },
  { key: 'tags', label: 'Tags' },
  {
    key: 'actions',
    label: 'Actions',
    headerClassName: 'text-right',
    className: 'text-right',
  },
]

const formatDate = (value: string | null): string => {
  if (!value) return 'Not published'
  const date = new Date(value)
  return Number.isNaN(date.valueOf())
    ? 'Not published'
    : date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
}

const statusClass = (status?: string): string => {
  if (status === 'published') return 'bg-emerald-100 text-emerald-700'
  if (status === 'archived') return 'bg-slate-200 text-slate-600'
  return 'bg-amber-100 text-amber-700'
}

const fetchMyPosts = async (cursor: string | null = null): Promise<void> => {
  isLoading.value = true
  errorMessage.value = ''
  isSearching.value = false
  hasSearched.value = false

  try {
    const response = await api.get<CursorPagination<Post>>('/posts/mine', {
      params: cursor ? { cursor } : undefined,
    })

    posts.value = response.data.data ?? []
    nextCursor.value = response.data.next_cursor ?? null
    prevCursor.value = response.data.prev_cursor ?? null
  } catch (err) {
    console.error(err)
    errorMessage.value =
      'Unable to load your posts right now. Please try again later.'
    nextCursor.value = null
    prevCursor.value = null
  } finally {
    isLoading.value = false
  }
}

const searchMyPosts = async (query: string): Promise<void> => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get<Post[]>('/posts/mine/search', {
      params: {
        q: query,
        limit: 25,
      },
    })

    posts.value = response.data ?? []
    nextCursor.value = null
    prevCursor.value = null
    isSearching.value = true
    hasSearched.value = true
  } catch (err) {
    console.error(err)
    errorMessage.value =
      'Unable to search your posts right now. Please try again later.'
  } finally {
    isLoading.value = false
  }
}

const fetchTags = async (query = ''): Promise<void> => {
  try {
    const response = await api.get<Tag[]>('/tags', {
      params: {
        q: query || undefined,
        limit: 50,
      },
    })

    tagOptions.value = response.data.map((tag) => ({
      label: tag.name,
      value: tag.name,
    }))
  } catch (err) {
    console.error(err)
  }
}

const handleNext = (): void => {
  if (isSearching.value) return
  if (nextCursor.value) fetchMyPosts(nextCursor.value)
}

const handlePrev = (): void => {
  if (isSearching.value) return
  if (prevCursor.value) fetchMyPosts(prevCursor.value)
}

const paginationState = computed(() =>
  isSearching.value
    ? null
    : {
        hasNext: Boolean(nextCursor.value),
        hasPrev: Boolean(prevCursor.value),
        isLoading: isLoading.value,
        nextLabel: 'Next',
        prevLabel: 'Previous',
      },
)

const emptyMessage = computed(() =>
  isSearching.value || hasSearched.value
    ? 'No posts matched your search.'
    : 'You have not created any posts yet.',
)

const handleSearch = (query: string): void => {
  searchMyPosts(query)
}

const clearSearch = (): void => {
  if (!hasSearched.value && !isSearching.value) return
  fetchMyPosts()
}

const openCreateModal = (): void => {
  isCreateModalOpen.value = true
}

const closeCreateModal = (): void => {
  isCreateModalOpen.value = false
}

const openUpdateModal = (post: Post): void => {
  activePost.value = post
  isUpdateModalOpen.value = true
}

const closeUpdateModal = (): void => {
  isUpdateModalOpen.value = false
  activePost.value = null
}

const openDeleteModal = (post: Post): void => {
  activePost.value = post
  isDeleteModalOpen.value = true
}

const closeDeleteModal = (): void => {
  isDeleteModalOpen.value = false
  activePost.value = null
}

const handlePostCreated = (): void => {
  closeCreateModal()
  fetchMyPosts()
}

const handlePostUpdated = (): void => {
  closeUpdateModal()
  fetchMyPosts()
}

const handlePostDeleted = (): void => {
  closeDeleteModal()
  fetchMyPosts()
}

onMounted(() => {
  fetchMyPosts()
  fetchTags()
})
</script>
