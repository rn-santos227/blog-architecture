<template>
  <UiModal
    :is-open="isOpen"
    title="Update post"
    description="Refresh your content before publishing."
    @close="handleClose"
  >
    <form class="space-y-4" @submit.prevent="handleSubmit">
      <UiTextField
        v-model="title"
        label="Title"
        placeholder="Update your headline"
        :error-message="fieldErrors.title"
      />
      <UiTextArea
        v-model="body"
        label="Body"
        placeholder="Update your post content"
        :error-message="fieldErrors.body"
        :rows="6"
      />
      <UiComboBox
        v-model="tags"
        label="Tags"
        placeholder="Type a tag and press enter"
        :options="tagOptions"
        :allow-custom="true"
        multiple
        @search="handleSearch"
      />
      <div class="space-y-2">
        <p class="text-sm font-medium text-slate-700">Status</p>
        <div class="flex flex-wrap gap-4">
          <UiRadioButton v-model="status" name="update-post-status" value="published">
            Publish now
          </UiRadioButton>
          <UiRadioButton v-model="status" name="update-post-status" value="draft">
            Save as draft
          </UiRadioButton>
        </div>
      </div>

      <UiDialog
        v-if="dialogState"
        :variant="dialogState.variant"
        :title="dialogState.title"
        :message="dialogState.message"
      />

      <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <UiButton type="button" variant="ghost" @click="handleClose">Cancel</UiButton>
        <UiButton type="submit" :disabled="isSubmitting">
          <span v-if="isSubmitting">Saving...</span>
          <span v-else>Save changes</span>
        </UiButton>
      </div>
    </form>
  </UiModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import UiModal from '@/components/ui/UiModal.vue'
import UiTextField from '@/components/ui/UiTextField.vue'
import UiTextArea from '@/components/ui/UiTextArea.vue'
import UiComboBox from '@/components/ui/UiComboBox.vue'
import UiRadioButton from '@/components/ui/UiRadioButton.vue'
import UiButton from '@/components/ui/UiButton.vue'
import UiDialog from '@/components/ui/UiDialog.vue'
import api from '@/services/api'
import { postSchema } from '@/schemas/post'
import type { Post } from '@/@types/blog'

interface TagOption {
  label: string
  value: string
}

const props = defineProps<{
  isOpen: boolean
  post: Post | null
  tagOptions: TagOption[]
}>()

const emit = defineEmits<{
  (event: 'close'): void
  (event: 'updated', post: Post): void
  (event: 'search-tags', query: string): void
}>()

const title = ref('')
const body = ref('')
const tags = ref<string[]>([])
const status = ref<'draft' | 'published'>('draft')
const isSubmitting = ref(false)
const fieldErrors = ref<{ title?: string; body?: string }>({})

const dialogState = ref<null | {
  variant: 'success' | 'warning' | 'error' | 'info'
  title: string
  message: string
}>(null)

const isOpen = computed(() => props.isOpen)

const resetForm = () => {
  title.value = ''
  body.value = ''
  tags.value = []
  status.value = 'draft'
  fieldErrors.value = {}
  dialogState.value = null
  isSubmitting.value = false
}

const hydrateFromPost = (post: Post | null) => {
  if (!post) return
  title.value = post.title
  body.value = post.body
  tags.value = post.tags?.map((tag) => tag.name) ?? []
  status.value = post.status === 'published' ? 'published' : 'draft'
}

const handleClose = () => {
  resetForm()
  emit('close')
}

const handleSearch = (query: string) => {
  emit('search-tags', query)
}

const handleSubmit = async () => {
  if (!props.post) return

  const result = postSchema.safeParse({
    title: title.value,
    body: body.value,
    status: status.value,
    tags: tags.value,
  })

  if (!result.success) {
    const flattened = result.error.flatten().fieldErrors
    fieldErrors.value = {
      title: flattened.title?.[0],
      body: flattened.body?.[0],
    }
    dialogState.value = {
      variant: 'warning',
      title: 'Check your post details',
      message: result.error.issues[0]?.message ?? 'Please update the highlighted fields.',
    }
    return
  }

  isSubmitting.value = true
  dialogState.value = {
    variant: 'info',
    title: 'Saving changes',
    message: 'Updating your post content.',
  }

  try {
    const response = await api.put<Post>(`/posts/${props.post.id}`, {
      title: result.data.title,
      body: result.data.body,
      status: result.data.status,
      tags: result.data.tags ?? [],
    })
    dialogState.value = {
      variant: 'success',
      title: 'Post updated',
      message: 'Your post changes are now saved.',
    }
    emit('updated', response.data)
    handleClose()
  } catch (error) {
    console.error(error)
    dialogState.value = {
      variant: 'error',
      title: 'Unable to update post',
      message: 'Please try again in a moment.',
    }
  } finally {
    isSubmitting.value = false
  }
}

watch(
  () => props.isOpen,
  (value) => {
    if (value) {
      resetForm()
      hydrateFromPost(props.post)
    }
  },
)

watch(
  () => props.post,
  (post) => {
    if (props.isOpen) {
      resetForm()
      hydrateFromPost(post)
    }
  },
)
</script>
