<template>
  <q-td :props="scope">
    <div class="column">
      <div>
        <slot name="value" v-bind="scope">
          {{ scope.value }}
        </slot>
      </div>
      <div class="text-caption text-grey-8">
        <slot name="aside" v-bind="scope">
          {{ asideLabel }}: {{ asideValue }}
        </slot>
      </div>
    </div>
  </q-td>
</template>

<script lang="ts">
import type { QTableBodyCellScope, QueryTableColumn } from "../QueryTable.vue"

export interface WithAsideColumn extends QueryTableColumn {
  aside?: string | ((row: Record<string, unknown>) => string)
  asideLabel?: string | ((row: Record<string, unknown>) => string)
}
</script>

<script setup lang="ts">
import { computed } from "vue"

interface Props {
  scope: QTableBodyCellScope<WithAsideColumn>
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
