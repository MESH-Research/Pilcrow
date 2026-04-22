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
      <q-badge
        v-if="role"
        outline
        :color="role === 'publication_admin' ? 'primary' : 'secondary'"
        class="role-badge"
      >
        {{ $t(`publication.manage.dashboard.role.${role}`) }}
      </q-badge>
    </div>
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

const name = computed(() => {
  const row = props.scope.row as { name?: string }
  return row.name ?? ""
})

const role = computed(() => {
  const row = props.scope.row as { effective_role?: string | null }
  return row.effective_role ?? null
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
.role-badge {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  flex: 0 0 auto;
}
</style>
