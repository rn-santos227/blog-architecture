<template>
  <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-3 text-base font-semibold text-slate-800">
      <slot name="title" />
    </div>
    <div class="p-4">
      <slot />
    </div>
    <div class="border-t border-slate-100 px-4 py-3 text-sm text-slate-600">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts"></script>
