<template>
  <q-td :props="scope" :dense="scope.dense" class="text-right">
    <span v-if="count > threshold" class="text-body2 text-weight-medium">
      {{ count }}
    </span>
    <span
      v-else-if="count > 0"
      class="row items-center justify-end q-gutter-xs"
      :aria-label="`${count} submissions`"
    >
      <q-icon
        v-for="i in count"
        :key="i"
        :name="icon"
        size="sm"
        :color="color"
      />
    </span>
    <span v-else class="text-grey-5" aria-label="no submissions">—</span>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import type { QTableBodyCellScope, QueryTableColumn } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}
const props = defineProps<Props>()

const count = computed(() => Number(props.scope.value ?? 0))

const column = computed(() => props.scope.col as QueryTableColumn)

// Column authors can tune both via QueryTableColumn extras.
const threshold = computed(() => column.value.iconThreshold ?? 5)
const icon = computed(() => column.value.icon ?? "description")
const color = computed(() => column.value.iconColor ?? "grey-7")
</script>
