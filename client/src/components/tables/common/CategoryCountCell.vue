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

/* Pattern overlay painted on the link itself. Mirrors the a11y
   mixin in app.sass so the texture shows unconditionally on this
   cell type — we're already opting in by using the `bg-{color}`
   chip style, so patterning it matches the dashboard chips' look
   whether or not the app-wide a11y toggle is on. */
.category-count-link[class*="pattern-"] {
  position: relative;
  overflow: hidden;
}
.category-count-link[class*="pattern-"] > * {
  position: relative;
  z-index: 1;
}
.category-count-link[class*="pattern-"]::after {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}
.category-count-link.pattern-diagonal::after {
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 5px,
    rgba(255, 255, 255, 0.3) 5px,
    rgba(255, 255, 255, 0.3) 8px
  );
}
.category-count-link.pattern-zigzag::after {
  background:
    linear-gradient(135deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%) -6px
      0,
    linear-gradient(225deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%) -6px
      0,
    linear-gradient(315deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%),
    linear-gradient(45deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%);
  background-size: 12px 12px;
}
.category-count-link.pattern-dots::after {
  background: radial-gradient(
    circle,
    rgba(255, 255, 255, 0.35) 2px,
    transparent 2px
  );
  background-size: 10px 10px;
}
.category-count-link.pattern-crosshatch::after {
  background:
    repeating-linear-gradient(
      45deg,
      transparent,
      transparent 5px,
      rgba(255, 255, 255, 0.22) 5px,
      rgba(255, 255, 255, 0.22) 8px
    ),
    repeating-linear-gradient(
      -45deg,
      transparent,
      transparent 5px,
      rgba(255, 255, 255, 0.22) 5px,
      rgba(255, 255, 255, 0.22) 8px
    );
}
</style>
