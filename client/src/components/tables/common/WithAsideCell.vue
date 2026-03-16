<template>
  <q-td :props="scope">
    <q-item class="q-pa-none">
      <q-item-section>
        <q-item-label>
          <slot name="value" v-bind="scope">
            {{ scope.value }}
          </slot>
        </q-item-label>
        <q-item-label caption>
          <slot name="aside" v-bind="scope">
            {{ asideLabel }}: {{ asideValue }}
          </slot>
        </q-item-label>
      </q-item-section>
    </q-item>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import type { QTableBodyCellScope } from "../types"

interface ColumnWithAside {
  aside?: string | ((row: Record<string, unknown>) => string)
  asideLabel?: string | ((row: Record<string, unknown>) => string)
}

interface Props {
  scope: QTableBodyCellScope & { col: ColumnWithAside }
}

const props = defineProps<Props>()

const asideValue = computed(() => {
  const aside = props.scope.col.aside
  if (typeof aside === "function") {
    return aside(props.scope.row)
  } else if (aside) {
    return aside
      .split(".")
      .reduce<
        Record<string, unknown>
      >((o, i) => (o[i] as Record<string, unknown>) ?? {}, props.scope.row) as unknown as string
  }
  return ""
})

const asideLabel = computed(() => {
  const label = props.scope.col.asideLabel
  if (typeof label === "function") {
    return label(props.scope.row)
  } else if (label) {
    return label
  }
  return ""
})
</script>
