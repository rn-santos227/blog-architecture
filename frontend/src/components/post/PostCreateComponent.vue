<template>
  <UiModal
    :is-open="isOpen"
    title="Create post"
    description="Share a new story with your audience."
    @close="handleClose"
  >
    <form class="space-y-4" @submit.prevent="handleSubmit">
      <UiTextField
        v-model="title"
        label="Title"
        placeholder="Give your post a headline"
        :error-message="fieldErrors.title"
      />
      <UiTextArea
        v-model="body"
        label="Body"
        placeholder="Write your post content"
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
          <UiRadioButton v-model="status" name="post-status" value="published">
            Publish now
          </UiRadioButton>
          <UiRadioButton v-model="status" name="post-status" value="draft">
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
          <span v-if="isSubmitting">Creating...</span>
          <span v-else>Create post</span>
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
  tagOptions: TagOption[]
}>()

const emit = defineEmits<{
  (event: 'close'): void
  (event: 'created', post: Post): void
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

const handleClose = () => {
  resetForm()
  emit('close')
}

const handleSearch = (query: string) => {
  emit('search-tags', query)
}

const handleSubmit = async () => {
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
    title: 'Creating post',
    message: 'Saving your draft. Please wait.',
  }

  try {
    const response = await api.post<Post>('/posts', {
      title: result.data.title,
      body: result.data.body,
      status: result.data.status,
      tags: result.data.tags ?? [],
    })
    dialogState.value = {
      variant: 'success',
      title: 'Post created',
      message: 'Your post has been saved successfully.',
    }
    emit('created', response.data)
    handleClose()
  } catch (error) {
    console.error(error)
    dialogState.value = {
      variant: 'error',
      title: 'Unable to create post',
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
    }
  },
)
</script>
