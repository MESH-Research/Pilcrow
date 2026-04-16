<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item class="q-pa-none">
      <q-item-section>
        <q-item-label>
          <router-link v-if="link" :to="link" class="text-primary" @click.stop>
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
</script>
