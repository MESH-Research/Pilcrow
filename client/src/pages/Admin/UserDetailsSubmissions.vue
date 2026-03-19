<template>
  <QueryTable
    ref="queryTableRef"
    :refresh-btn="false"
    :query="getUserSubmissionsDocument"
    t-prefix="admin.users.details.submissions"
    :variables="{
      id: id,
      status: statusFilter,
      roles: roleFilter,
      publication: publicationFilter ? [publicationFilter] : undefined
    }"
    field="user.submissions"
    :columns="columns"
    :grid="$q.screen.lt.md"
    :dense="dense"
    sync-url
  >
    <template #top-extra>
      <SubmissionsFilterPanel
        v-model:status-filter="statusFilter"
        v-model:role-filter="roleFilter"
        v-model:publication-filter="publicationFilter"
        :dense="dense"
      />
    </template>
    <template #no-data>
      <UserSubmissionsNoDataSlot
        :dense="dense"
        :status-filter="statusFilter"
        :role-filter="roleFilter"
      />
    </template>
    <template #body-cell-actions="scope">
      <q-td :props="scope" :dense="scope.dense">
        <q-btn
          color="primary"
          size="sm"
          :dense="scope.dense"
          :to="{
            name: 'submission:details',
            params: { id: scope.row.submission.id }
          }"
          :label="$t('admin.users.details.submissions.actions.view')"
        />
      </q-td>
    </template>
    <template #body-cell-title="scope">
      <WithAsideCell :scope="scope" style="white-space: normal">
        <template #aside>
          Publication: {{ scope.row.submission.publication.name }}
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
import SubmissionsFilterPanel from "./components/SubmissionsFilterPanel.vue"
import UserSubmissionsNoDataSlot from "./components/UserSubmissionsNoDataSlot.vue"
import WithAsideCell from "src/components/tables/common/WithAsideCell.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import { getUserSubmissionsDocument } from "src/graphql/generated/graphql"
import { ref, watch } from "vue"
import { useI18n } from "vue-i18n"
import { useQuasar } from "quasar"
import { useRoute, useRouter } from "vue-router"
import { defaultOptions as defaultStatusOptions } from "./components/SubmissionsFilterPanelStatus.vue"
import { defaultOptions as defaultRoleOptions } from "./components/SubmissionsFilterPanelRoles.vue"

const $q = useQuasar()
const { t } = useI18n()

interface Props {
  id: string
  dense?: boolean
}

withDefaults(defineProps<Props>(), {
  dense: false
})

const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

const columns: QueryTableColumn[] = [
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
    style: "white-space: normal",
    label: "Title"
  },
  {
    name: "role",
    align: "left",
    field: (row) => t(`admin.users.details.roles.${row.role}`),
    label: "Role"
  },
  {
    name: "status",
    align: "left",
    field: (row) => t(`submission.status.${row.submission.status}`),
    sortable: true,
    label: "Status"
  },
  {
    name: "created_at",
    align: "left",
    field: (row) => row.submission.created_at,
    sortable: true,
    component: DateTimeCell,
    label: "Created"
  },
  {
    name: "actions",
    align: "left",
    field: "id",
    label: "Actions"
  }
]

const route = useRoute()
const router = useRouter()

function parseList(value: string | string[] | undefined): string[] {
  if (!value) return []
  const str = Array.isArray(value) ? value[0] : value
  if (!str) return []
  const inner = str.startsWith("[") ? str.slice(1, -1) : str
  return inner ? inner.split(",") : []
}

function formatList(values: string[]): string {
  return `[${values.join(",")}]`
}

const statusFilter = ref<string[]>(parseList(route.query.status as string))
const roleFilter = ref<string[]>(parseList(route.query.roles as string))
const publicationFilter = ref<string | null>(
  (route.query.publication as string) || null
)

watch(
  [statusFilter, roleFilter, publicationFilter],
  ([status, roles, publication]) => {
    if (queryTableRef.value) {
      queryTableRef.value.page = 1
    }

    const query: Record<string, string> = { ...route.query } as Record<
      string,
      string
    >

    const isDefaultStatus =
      status.length === defaultStatusOptions.length &&
      status.every((s) => defaultStatusOptions.includes(s))
    if (!isDefaultStatus) query.status = formatList(status)
    else delete query.status

    const isDefaultRoles =
      roles.length === defaultRoleOptions.length &&
      roles.every((r) => defaultRoleOptions.includes(r))
    if (!isDefaultRoles) query.roles = formatList(roles)
    else delete query.roles

    if (publication) query.publication = publication
    else delete query.publication

    router.replace({ query })
  }
)
</script>
