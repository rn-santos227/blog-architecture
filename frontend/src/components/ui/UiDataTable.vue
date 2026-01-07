<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th
              v-for="column in columns"
              :key="String(column.key)"
              scope="col"
              class="px-4 py-3 font-semibold"
              :class="column.headerClassName"
            >
              {{ column.label }}
            </th>
          </tr>
        </thead>
        <tbody v-if="rows.length" class="divide-y divide-slate-100">
          <tr v-for="row in rows" :key="String(row[rowKey])">
            <td
              v-for="column in columns"
              :key="String(column.key)"
              class="px-4 py-3 text-slate-700"
              :class="column.className"
            >
              <slot
                :name="`cell-${String(column.key)}`"
                :row="row"
                :value="getValue(row, column.key)"
                :column="column"
              >
                {{ getValue(row, column.key) ?? '—' }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="!rows.length" class="px-6 py-10 text-center text-sm text-slate-500">
      {{ emptyMessage }}
    </div>

    <div
      v-if="pagination"
      class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 text-sm text-slate-500"
    >
      <span class="text-xs uppercase tracking-wide">Pagination</span>
      <div class="flex items-center gap-2">
        <UiButton
          variant="secondary"
          :disabled="pagination.isLoading || !pagination.hasPrev"
          @click="$emit('prev')"
        >
          {{ pagination.prevLabel }}
        </UiButton>
        <UiButton
          variant="secondary"
          :disabled="pagination.isLoading || !pagination.hasNext"
          @click="$emit('next')"
        >
          {{ pagination.nextLabel }}
        </UiButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts" generic="T extends Record<string, unknown>">
import UiButton from '@/components/ui/UiButton.vue'

export interface DataTableColumn<T> {
  key: keyof T | string
  label: string
  className?: string
  headerClassName?: string
}

export interface DataTablePagination {
  hasNext: boolean
  hasPrev: boolean
  isLoading?: boolean
  nextLabel?: string
  prevLabel?: string
}

withDefaults(
  defineProps<{
    columns: DataTableColumn<T>[]
    rows: T[]
    rowKey: {
      [K in keyof T]: T[K] extends string | number ? K : never
    }[keyof T]
    emptyMessage?: string
    pagination?: DataTablePagination | null
  }>(),
  {
    emptyMessage: 'No records available.',
    pagination: null,
  },
)

defineEmits<{
  (event: 'next'): void
  (event: 'prev'): void
}>()

defineSlots<{
  [key: `cell-${string}`]: (props: {
    row: T
    value: unknown
    column: DataTableColumn<T>
  }) => unknown
}>()

const getValue = (row: T, key: keyof T | string): unknown => {
  if (typeof key === 'string') {
    return (row as Record<string, unknown>)[key]
  }
  return row[key]
}
</script>
