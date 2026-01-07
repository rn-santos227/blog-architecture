<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="px-4 py-3 font-semibold"
              :class="column.headerClassName"
            >
              {{ column.label }}
            </th>
          </tr>
        </thead>
        <tbody v-if="rows.length" class="divide-y divide-slate-100">
          <tr v-for="row in rows" :key="row[rowKey]">
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-4 py-3 text-slate-700"
              :class="column.className"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="row[column.key]"
                :column="column"
              >
                {{ row[column.key] ?? '—' }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="!rows.length" class="px-6 py-10 text-center text-sm text-slate-500">
      {{ emptyMessage }}
    </div>
  </div>
</template>

<script setup lang="ts">
interface DataTableColumn {
  key: string
  label: string
  className?: string
  headerClassName?: string
}

withDefaults(
  defineProps<{
    columns: DataTableColumn[]
    rows: Record<string, any>[]
    rowKey?: string
    emptyMessage?: string
  }>(),
  {
    rowKey: 'id',
    emptyMessage: 'No records available.',
  },
)
</script>
