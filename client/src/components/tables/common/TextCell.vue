<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item class="q-pa-none">
      <q-item-section>
        <q-item-label>{{ scope.value }}</q-item-label>
      </q-item-section>
    </q-item>
  </q-td>
</template>

<script setup lang="ts">
import type { QTableBodyCellScope } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}

defineProps<Props>()
</script>
