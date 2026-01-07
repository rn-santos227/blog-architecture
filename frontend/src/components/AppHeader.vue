<template>
  <header
    class="sticky top-0 z-50 border-b border-slate-200 bg-white"
  >
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
      <RouterLink class="flex items-center gap-3" to="/">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white">
          <span class="text-lg font-semibold">BA</span>
        </div>
        <div>
          <p class="text-lg font-semibold text-slate-900">Blog Architecture</p>
          <p class="text-sm text-slate-500">Build modular publishing experiences</p>
        </div>
      </RouterLink>
      <AuthActions />
    </div>
  </header>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AuthActions from './auth/AuthActions.vue'
</script>
