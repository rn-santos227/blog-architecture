import { inject, provide, ref } from 'vue'

interface ModalContext {
  isOpen: ReturnType<typeof ref<boolean>>
  open: () => void
  close: () => void
}

const modalKey = Symbol('modal')

export const provideModal = () => {
  const isOpen = ref(false)

  const open = () => {
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
  }

  provide(modalKey, {
    isOpen,
    open,
    close,
  })

  return {
    isOpen,
    open,
    close,
  }
}

export const useModal = () => {
  const context = inject<ModalContext | null>(modalKey, null)

  if (!context) {
    throw new Error('useModal must be used within a ModalProvider')
  }

  return context
}
