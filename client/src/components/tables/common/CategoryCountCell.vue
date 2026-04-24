<template>
  <q-td :props="scope" :dense="scope.dense" class="category-count-cell">
    <router-link
      v-if="link && hasCount"
      :to="link"
      :class="[
        'category-count-link',
        `bg-${category?.color ?? ''}`,
        category?.textClass ?? '',
        category?.pattern ?? ''
      ]"
      :aria-label="ariaLabel"
    >
      <span class="pattern-text-mask">{{ value }}</span>
    </router-link>
    <span
      v-else-if="hasCount"
      :class="[
        'category-count-link',
        `bg-${category?.color ?? ''}`,
        category?.textClass ?? '',
        category?.pattern ?? ''
      ]"
      :aria-label="ariaLabel"
    >
      <span class="pattern-text-mask">{{ value }}</span>
    </span>
    <span v-else class="category-count--zero">{{ value }}</span>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import type { QTableBodyCellScope, QueryTableColumn } from "../QueryTable.vue"
import {
  statusCategories,
  workflowStages
} from "src/pages/Publication/components/statusCategories"

// Column spec carries a `category` key (e.g. "needs_action") that
// resolves to the shared category style (color, textClass, pattern).
// When the cell's value is a non-zero count we render a patterned
// bg chip so the a11y pattern overlay can paint its texture (matches
// the dashboard's chip palette). Zero counts stay neutral so empty
// rows don't turn into a rainbow.
interface Props {
  scope: QTableBodyCellScope
}
const props = defineProps<Props>()

const value = computed(() => props.scope.value)

const link = computed(() => {
  const col = props.scope.col as QueryTableColumn
  return col.linkTo ? col.linkTo(props.scope.row) : null
})

const category = computed(() => {
  const col = props.scope.col as QueryTableColumn & { category?: string }
  if (!col.category) return null
  return (
    statusCategories.find((c) => c.key === col.category) ??
    workflowStages.find((s) => s.key === col.category) ??
    null
  )
})

const hasCount = computed(() => {
  const raw = value.value
  const n = typeof raw === "number" ? raw : Number(raw)
  return Number.isFinite(n) && n > 0
})

const ariaLabel = computed(() => {
  const col = props.scope.col as QueryTableColumn
  const label = typeof col.label === "string" ? col.label : ""
  return `${value.value} ${label}`.trim()
})
</script>

<script lang="ts">
// Extend QueryTableColumn with an optional category key consumed by
// this cell to pull color, textClass, and pattern from the shared
// statusCategories map.
declare module "../QueryTable.vue" {
  interface QueryTableColumn {
    category?: string
  }
}
</script>

<style scoped>
/* Zero the td's own padding so the colored link below fills the
   cell edge-to-edge; the td's default ::before/::after hover overlays
   stay on the td (they're at z-index 0) and the link floats above. */
.category-count-cell {
  padding: 0 !important;
}
.category-count-link {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 8px 16px;
  min-height: 40px;
  text-decoration: none;
  color: inherit;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  font-size: 1rem;
  position: relative;
  /* Sit above the td's Quasar hover overlays (tbody td::before/::after
     at default z-index). Without this the colored bg can look washed
     out by the 0.03/0.06 black overlays Quasar paints on every cell. */
  z-index: 1;
}
a.category-count-link:hover {
  filter: brightness(1.08);
}
.category-count--zero {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 8px 16px;
  color: rgba(0, 0, 0, 0.35);
  font-variant-numeric: tabular-nums;
  min-height: 40px;
}
.body--dark .category-count--zero {
  color: rgba(255, 255, 255, 0.3);
}

/* Pattern overlay is handled by the global `.a11y-patterns` /
   `prefers-contrast: more` rules in app.sass, so the chip reads
   color-only by default and picks up its texture when the user
   (or their OS) opts into higher contrast. The inner number sits
   inside a `pattern-text-mask` span so the digit keeps a solid
   halo over the pattern when it does appear. */
.category-count-link[class*="pattern-"] {
  overflow: hidden;
}
</style>
