<template>
  <slot />
</template>

<script setup lang="ts">
import { provideModal } from './modal'

provideModal()
</script>