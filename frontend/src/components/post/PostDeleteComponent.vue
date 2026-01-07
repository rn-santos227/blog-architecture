<template>
  <UiModal
    :is-open="isOpen"
    title="Delete post"
    description="This action cannot be undone."
    @close="handleClose"
  >
    <div class="space-y-4">
      <p class="text-sm text-slate-600">
        Are you sure you want to delete
        <span class="font-semibold text-slate-900">{{ post?.title }}</span>?
      </p>

      <UiDialog
        v-if="dialogState"
        :variant="dialogState.variant"
        :title="dialogState.title"
        :message="dialogState.message"
      />

      <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <UiButton type="button" variant="ghost" @click="handleClose">Cancel</UiButton>
        <UiButton
          type="button"
          variant="secondary"
          class="border-red-200 text-red-600 hover:bg-red-50"
          :disabled="isDeleting"
          @click="handleDelete"
        >
          <span v-if="isDeleting">Deleting...</span>
          <span v-else>Delete post</span>
        </UiButton>
      </div>
    </div>
  </UiModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiDialog from '@/components/ui/UiDialog.vue'
import api from '@/services/api'
import type { Post } from '@/@types/blog'

const props = defineProps<{
  isOpen: boolean
  post: Post | null
}>()

const emit = defineEmits<{
  (event: 'close'): void
  (event: 'deleted', postId: number): void
}>()

const isDeleting = ref(false)
const dialogState = ref<null | {
  variant: 'success' | 'warning' | 'error' | 'info'
  title: string
  message: string
}>(null)

const isOpen = computed(() => props.isOpen)

const resetState = () => {
  isDeleting.value = false
  dialogState.value = null
}

const handleClose = () => {
  resetState()
  emit('close')
}

const handleDelete = async () => {
  if (!props.post) return
  isDeleting.value = true
  dialogState.value = {
    variant: 'info',
    title: 'Deleting post',
    message: 'Removing your post now.',
  }

  try {
    await api.delete(`/posts/${props.post.id}`)
    dialogState.value = {
      variant: 'success',
      title: 'Post deleted',
      message: 'Your post has been removed.',
    }
    emit('deleted', props.post.id)
    handleClose()
  } catch (error) {
    console.error(error)
    dialogState.value = {
      variant: 'error',
      title: 'Unable to delete post',
      message: 'Please try again in a moment.',
    }
  } finally {
    isDeleting.value = false
  }
}

watch(
  () => props.isOpen,
  (value) => {
    if (value) {
      resetState()
    }
  },
)
</script>
