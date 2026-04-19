<template>
  <QueryTable
    :query="GetPublicationUsersDocument"
    field="publication.users"
    t-prefix="publication.manage.users"
    :columns="columns"
    :variables="{
      id,
      roles: ['reviewer', 'review_coordinator'],
      staged: false
    }"
    sync-url
    :default-sort="{ sortBy: 'name' }"
    @row-click="onRowClick"
  >
    <template #header="headerProps">
      <q-tr class="team-group-header">
        <q-th class="team-group-spacer" />
        <q-th class="team-group-spacer" />
        <q-th
          colspan="2"
          class="text-center team-group-label bg-accent text-white"
        >
          {{ $t("publication.manage.users.groups.active") }}
        </q-th>
        <q-th
          colspan="2"
          class="text-center team-group-label bg-accent text-white"
        >
          {{ $t("publication.manage.users.groups.completed") }}
        </q-th>
      </q-tr>
      <q-tr :props="headerProps" class="bg-accent text-white">
        <q-th key="name" :props="headerProps" class="text-left">
          {{ $t("publication.manage.users.headers.name") }}
        </q-th>
        <q-th key="email" :props="headerProps" class="text-left">
          {{ $t("publication.manage.users.headers.email") }}
        </q-th>
        <q-th
          v-for="col in countHeaderCols(headerProps.cols)"
          :key="col.name"
          :props="headerProps"
          class="text-right"
        >
          {{ $t(`publication.manage.users.headers.${col.name}`) }}
        </q-th>
      </q-tr>
    </template>
  </QueryTable>
</template>

<script setup lang="ts">
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import { useRouter } from "vue-router"
import { GetPublicationUsersDocument } from "src/graphql/generated/graphql"

definePage({
  name: "manage:publication:team",
  props: true,
  meta: {
    crumb: {
      label: "Review Team"
    }
  }
})

interface Props {
  id: string
}
const props = defineProps<Props>()
const router = useRouter()

function onRowClick(_evt: Event, row: { id: string }) {
  router.push({
    name: "manage:publication:user",
    params: { id: props.id, userId: row.id }
  })
}

function countHeaderCols(cols: { name: string }[]) {
  return cols.filter((c) => c.name.endsWith("_count"))
}

// Column order is intentional: group Active (reviewer + coordinator)
// first, then Completed (reviewer + coordinator). The grouped header
// above depends on this ordering.
const columns: QueryTableColumn[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row,
    component: NameAvatarCell,
    sortable: true,
    label: "publication.manage.users.headers.name"
  },
  {
    name: "email",
    align: "left",
    field: "email",
    sortable: true,
    label: "publication.manage.users.headers.email"
  },
  {
    name: "as_reviewer_active_count",
    align: "right",
    field: "as_reviewer_active_count",
    sortable: true,
    label: "publication.manage.users.headers.as_reviewer_active_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  },
  {
    name: "as_coordinator_active_count",
    align: "right",
    field: "as_coordinator_active_count",
    sortable: true,
    label: "publication.manage.users.headers.as_coordinator_active_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  },
  {
    name: "as_reviewer_completed_count",
    align: "right",
    field: "as_reviewer_completed_count",
    sortable: true,
    label: "publication.manage.users.headers.as_reviewer_completed_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  },
  {
    name: "as_coordinator_completed_count",
    align: "right",
    field: "as_coordinator_completed_count",
    sortable: true,
    label: "publication.manage.users.headers.as_coordinator_completed_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  }
]
</script>

<style scoped>
/* Visual grouping for the count block:
   - col 3 starts the count section (after Email)
   - col 5 separates the Active and Completed groups */
:deep(th:nth-child(3)),
:deep(td:nth-child(3)),
:deep(th:nth-child(5)),
:deep(td:nth-child(5)) {
  border-left: 1px solid rgba(0, 0, 0, 0.12);
}
.body--dark :deep(th:nth-child(3)),
.body--dark :deep(td:nth-child(3)),
.body--dark :deep(th:nth-child(5)),
.body--dark :deep(td:nth-child(5)) {
  border-left-color: rgba(255, 255, 255, 0.18);
}
.team-group-header .team-group-label {
  font-weight: 600;
  letter-spacing: 0.02em;
}
.team-group-header .team-group-spacer {
  background: transparent;
  border: none;
  padding: 0;
}
/* Drop the table's own top & left borders (they'd wrap the empty spacer
   area) and redraw them only where wanted: above the group labels and
   above the main header row. */
:deep(.q-table--bordered) {
  border-top: 0;
  border-left: 0;
}
.team-group-header .team-group-label {
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}
.team-group-header .team-group-label:first-of-type {
  border-left: 1px solid rgba(0, 0, 0, 0.12);
}
.team-group-header + tr > th {
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}
.team-group-header + tr > th:first-child {
  border-left: 1px solid rgba(0, 0, 0, 0.12);
}
.body--dark .team-group-header .team-group-label,
.body--dark .team-group-header .team-group-label:first-of-type,
.body--dark .team-group-header + tr > th,
.body--dark .team-group-header + tr > th:first-child {
  border-color: rgba(255, 255, 255, 0.18);
}
</style>
