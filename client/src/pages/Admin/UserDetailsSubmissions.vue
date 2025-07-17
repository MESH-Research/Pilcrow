<template>
  <QueryTable
    ref="queryTableRef"
    :refresh-btn="false"
    :query="GET_USER_SUBMISSIONS"
    t-prefix="admin.users.details.submissions"
    :variables="{
      id: id,
      status: statusFilter,
      roles: roleFilter,
      publication: publicationFilter ? [publicationFilter] : undefined
    }"
    field="user.assigned_submissions"
    :columns="columns"
    :grid="$q.screen.lt.md"
    :dense="dense"
  >
    <template #top>
      <UserSubmissionsTopSlot
        ref="topSlotRef"
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
    <template #item="{ row }">
      <UserSubmissionsItemSlot :row="row" :dense="dense" />
    </template>
    <!-- Body slots start here -->
    <template #body-cell-actions="scope">
      <q-td :props="scope" :dense="scope.dense">
        <q-btn
          color="primary"
          size="sm"
          :dense="scope.dense"
          :to="{ name: 'submission:details', params: { id: scope.row.id } }"
          :label="$t('admin.users.details.submissions.actions.view')"
        />
      </q-td>
    </template>
    <template #body-cell-title="scope">
      <WithAsideCell :scope="scope" style="white-space: normal">
        <template #aside>
          Publication: {{ scope.row.publication.name }}
        </template>
      </WithAsideCell>
    </template>
  </QueryTable>
</template>

<script setup>
import UserSubmissionsItemSlot from "./components/UserSubmissionsItemSlot.vue"
import UserSubmissionsTopSlot from "./components/UserSubmissionsTopSlot.vue"
import UserSubmissionsNoDataSlot from "./components/UserSubmissionsNoDataSlot.vue"
import WithAsideCell from "src/components/tables/common/WithAsideCell.vue"
import QueryTable from "src/components/tables/QueryTable.vue"
import { ref, watch } from "vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const queryTableRef = ref(null)
defineProps({
  id: {
    type: String,
    required: true
  },
  dense: {
    type: Boolean,
    default: false,
    required: false
  }
})
const topSlotRef = ref(null)
const columns = [
  {
    name: "id",
    align: "left",
    field: "id",
    sortable: true
  },
  {
    name: "status",
    align: "left",
    sortable: true,
    field: (row) => t(`submission_status.${row.status.toLowerCase()}`),
    aside: (row) =>
      t(`admin.users.details.roles.${row.users[0].pivot.role.name}`),
    asideLabel: "Role",
    component: WithAsideCell
  },
  {
    name: "title",
    required: true,
    align: "left",
    field: "title",
    sortable: true,
    aside: "publication.name",
    asideLabel: "Publication",
    component: WithAsideCell,
    style: "white-space: normal"
  },
  {
    name: "created_at",
    align: "left",
    field: "created_at",
    sortable: true,
    component: DateTimeCell
  },

  {
    name: "actions",
    align: "left"
  }
]

//const icon = (status) => getIcon(status)
const statusFilter = ref([])
const roleFilter = ref([])
const publicationFilter = ref(null)

watch([statusFilter, roleFilter, publicationFilter], () => {
  queryTableRef.value.page = 1
})
</script>

<script>
import { gql } from "graphql-tag"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import { _PAGINATION_FIELDS } from "src/graphql/fragments"
//import { getIcon } from "src/use/icons"

const GET_USER_SUBMISSIONS = gql`
  query getUserSubmissions(
    $id: ID!
    $page: Int!
    $first: Int!
    $roles: [UserRoles!]
    $status: [SubmissionStatus!]
    $publication: [ID!]
  ) {
    user(id: $id) {
      assigned_submissions(
        page: $page
        first: $first
        roles: $roles
        status: $status
        publications: $publication
      ) {
        paginatorInfo {
          ...paginationFields
        }
        data {
          id
          title
          status
          users {
            id
            name
            pivot {
              role {
                name
              }
            }
          }
          created_at
          publication {
            id
            name
          }
        }
      }
    }
  }
  ${_PAGINATION_FIELDS}
`
</script>
