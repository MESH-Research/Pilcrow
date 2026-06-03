<template>
  <QueryTable
    ref="queryTableRef"
    :refresh-btn="false"
    :query="getUserSubmissionsDocument"
    t-prefix="admin.users.details.submissions"
    :variables="{
      id,
      status: statusFilter,
      roles: roleFilter,
      publication: publicationFilter ? [publicationFilter] : undefined
    }"
    field="user.submissions"
    :columns="columns"
    sync-url
    @row-click="onRowClick"
  >
    <template #top-after>
      <SubmissionsFilterPanel
        v-model:status-filter="statusFilter"
        v-model:role-filter="roleFilter"
        v-model:publication-filter="publicationFilter"
      />
    </template>
    <template #no-data>
      <UserSubmissionsNoDataSlot
        :status-filter="statusFilter"
        :role-filter="roleFilter"
      />
    </template>
    <template #body-cell-title="scope">
      <WithAsideCell :scope="scope" style="white-space: normal">
        <template #aside>
          {{ $t("admin.users.details.submissions.aside.publication") }}:
          {{ scope.row.submission.publication.name }}
        </template>
      </WithAsideCell>
    </template>
  </QueryTable>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query getUserSubmissions(
    $id: ID!
    $page: Int!
    $first: Int!
    $search: String
    $roles: [SubmissionUserRoles!]
    $status: [SubmissionStatus!]
    $publication: [ID!]
    $orderBy: [SubmissionAssignmentOrderBy!]
  ) {
    user(id: $id) {
      submissions(
        page: $page
        first: $first
        search: $search
        roles: $roles
        status: $status
        publication: $publication
        orderBy: $orderBy
      ) {
        ...QueryTable
        data {
          id
          role
          submission {
            id
            title
            status
            created_at
            updated_at
            publication {
              id
              name
            }
          }
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import SubmissionsFilterPanel from "src/pages/Admin/components/SubmissionsFilterPanel.vue"
import UserSubmissionsNoDataSlot from "src/pages/Admin/components/UserSubmissionsNoDataSlot.vue"
import WithAsideCell, {
  type WithAsideColumn
} from "src/components/tables/common/WithAsideCell.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import { getUserSubmissionsDocument } from "src/graphql/generated/graphql"
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"
import { useRoute, useRouter } from "vue-router"
import { useSubmissionFilters } from "src/use/submissionFilters"
import { defaultOptions as defaultStatusOptions } from "src/pages/Admin/components/SubmissionsFilterPanelStatus.vue"
import { defaultOptions as defaultRoleOptions } from "src/pages/Admin/components/SubmissionsFilterPanelRoles.vue"

definePage({
  name: "user_details:submissions",
  meta: {
    crumb: { label: "breadcrumbs.admin.submissions" }
  }
})

const { t } = useI18n()

const route = useRoute("user_details:submissions")
const router = useRouter()
const id = computed(() => route.params.id as string)

const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

const columns: (QueryTableColumn | WithAsideColumn)[] = [
  {
    name: "title",
    required: true,
    align: "left",
    field: (row) => row.submission.title,
    sortable: true,
    aside: (row: Record<string, unknown>) => {
      const sub = row.submission as { publication: { name: string } }
      return sub.publication.name
    },
    asideLabel: "Publication",
    component: WithAsideCell,
    style: "white-space: normal"
  },
  {
    name: "role",
    align: "left",
    field: (row) => t(`admin.users.details.roles.${row.role}`)
  },
  {
    name: "status",
    align: "left",
    field: (row) => t(`submission.status.${row.submission.status}`),
    sortable: true
  },
  {
    name: "created_at",
    align: "left",
    field: (row) => row.submission.created_at,
    sortable: true,
    component: DateTimeCell
  },
  {
    name: "updated_at",
    align: "left",
    field: (row) => row.submission.updated_at,
    sortable: true,
    component: DateTimeCell
  }
]

function onRowClick(_evt: Event, row: { submission: { id: string } }) {
  router.push({
    name: "submission:details",
    params: { id: row.submission.id }
  })
}

const { statusFilter, roleFilter, publicationFilter } = useSubmissionFilters({
  defaultStatuses: defaultStatusOptions,
  defaultRoles: defaultRoleOptions,
  tableRef: queryTableRef
})
</script>
