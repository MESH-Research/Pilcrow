<template>
  <div class="q-px-lg">
    <h2>{{ $t("admin.users.title") }}</h2>
  </div>
  <QueryTable
    class="q-px-lg"
    :query="GetUsersDocument"
    t-prefix="admin.users"
    :columns="columns"
    sync-url
    :default-sort="{ sortBy: 'name' }"
    @row-click="handleUserListBasicClick"
  >
    <template #body-cell-email="scope">
      <q-td :props="scope">
        {{ scope.value }}
        <q-icon
          :name="scope.row.email_verified_at ? 'verified' : 'cancel'"
          :color="scope.row.email_verified_at ? 'positive' : 'grey-5'"
          size="xs"
          class="q-ml-xs"
        >
          <q-tooltip :delay="500">
            {{
              scope.row.email_verified_at
                ? $t("admin.users.email_status.verified")
                : $t("admin.users.email_status.unverified")
            }}
          </q-tooltip>
        </q-icon>
      </q-td>
    </template>
  </QueryTable>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetUsers(
    $page: Int
    $search: String
    $first: Int
    $orderBy: [QueryUsersOrderByOrderByClause!]
  ) {
    users(page: $page, search: $search, first: $first, orderBy: $orderBy) {
      ...QueryTable
      data {
        id
        username
        email
        email_verified_at
        created_at
        ...NameAvatarCell
      }
    }
  }
`)
</script>

<script setup lang="ts">
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell, {
  type NameAvatarColumn
} from "src/components/tables/common/NameAvatarCell.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import {
  GetUsersDocument,
  type GetUsersQuery
} from "src/graphql/generated/graphql"
import { useRouter } from "vue-router"

definePage({
  name: "admin:users",
  meta: {
    crumb: { label: "breadcrumbs.admin.users" }
  }
})

type UserRow = GetUsersQuery["users"]["data"][number]

const columns: (QueryTableColumn | NameAvatarColumn)[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row,
    component: NameAvatarCell,
    hideUsername: true,
    sortable: true
  },
  {
    name: "username",
    align: "left",
    field: "username",
    sortable: true
  },
  {
    name: "email",
    align: "left",
    field: "email",
    sortable: true
  },
  {
    name: "created_at",
    align: "left",
    field: "created_at",
    sortable: true,
    component: DateTimeCell
  }
]

async function handleUserListBasicClick(_evt: Event, row: UserRow) {
  goToUserDetail(row.id)
}

const { push } = useRouter()
async function goToUserDetail(id: string) {
  push({
    name: "user_details",
    params: { id }
  })
}
</script>
