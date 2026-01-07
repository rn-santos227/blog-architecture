<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
</script>

<template>
  <div id="app" class="min-h-screen bg-slate-50 text-slate-900">
    <AppHeader />
    <RouterView />
  </div>
</template>

<style>
/* Global app-level styles only if needed */
</style>
