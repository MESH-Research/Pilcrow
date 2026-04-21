<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item class="q-pa-none">
      <q-item-section>
        <q-item-label
          v-if="captionAbove"
          caption
          class="text-grey-7 text-caption-above"
        >
          {{ captionAbove }}
        </q-item-label>
        <q-item-label>
          <router-link
            v-if="link"
            :to="link"
            class="text-primary"
            :title="titleAttr"
            @click.stop
          >
            {{ scope.value }}
          </router-link>
          <template v-else>{{ scope.value }}</template>
        </q-item-label>
      </q-item-section>
    </q-item>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import type { QTableBodyCellScope, QueryTableColumn } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()

const link = computed(() => {
  const col = props.scope.col as QueryTableColumn
  return col.linkTo ? col.linkTo(props.scope.row) : null
})

// If the cell value is clamped/ellipsized by the column's styles, a
// hover tooltip is the only way to see the full text. The attribute
// is harmless when the text fits entirely — the browser just shows
// the same content that's already visible.
const titleAttr = computed(() =>
  typeof props.scope.value === "string" ? props.scope.value : undefined
)

// Optional tiny caption rendered above the main value — e.g. an
// "#id" prefix — supplied by the column config via `captionAbove`.
const captionAbove = computed(() => {
  const col = props.scope.col as QueryTableColumn
  return col.captionAbove ? col.captionAbove(props.scope.row) : null
})
</script>

<style scoped>
.text-caption-above {
  font-size: 0.7rem;
  line-height: 1.2;
  font-variant-numeric: tabular-nums;
}
</style>
