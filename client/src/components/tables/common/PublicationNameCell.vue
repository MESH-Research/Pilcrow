<template>
  <q-td :props="scope" :dense="scope.dense">
    <div class="row items-center no-wrap q-gutter-xs">
      <router-link
        v-if="link"
        :to="link"
        class="text-primary publication-name"
        :title="name"
        @click.stop
      >
        {{ name }}
      </router-link>
      <template v-else>
        <span class="publication-name" :title="name">{{ name }}</span>
      </template>
      <RoleBadge v-if="role" :role="role" />
    </div>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import RoleBadge from "src/components/atoms/RoleBadge.vue"
import type { QTableBodyCellScope, QueryTableColumn } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}
const props = defineProps<Props>()

const link = computed(() => {
  const col = props.scope.col as QueryTableColumn
  return col.linkTo ? col.linkTo(props.scope.row) : null
})

// Support both shapes the cell is used with:
//   - flat Publication rows (row.name, row.effective_role)
//   - PublicationAssignment rows (row.publication.name, row.role)
// The assignment shape is preferred when present so callers can
// switch queries without swapping the cell.
const name = computed(() => {
  const row = props.scope.row as {
    name?: string
    publication?: { name?: string }
  }
  return row.publication?.name ?? row.name ?? ""
})

const role = computed(() => {
  const row = props.scope.row as {
    effective_role?: string | null
    role?: string | null
  }
  return row.role ?? row.effective_role ?? null
})
</script>

<style scoped>
.publication-name {
  font-weight: 500;
  text-decoration: none;
  overflow-wrap: anywhere;
  word-break: break-word;
}
.publication-name:hover {
  text-decoration: underline;
}
</style>
