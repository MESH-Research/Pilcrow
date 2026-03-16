<template>
  <h2 class="q-pl-lg">User Search</h2>
  <QueryTable
    class="q-px-lg"
    :query="GetUsersDocument"
    t-prefix="admin.users"
    :columns="columns"
    @row-click="handleUserListBasicClick"
  />
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetUsers($page: Int) {
    userSearch(page: $page) {
      paginatorInfo {
        ...QueryTablePaginator
      }
      data {
        id
        username
        email
        ...NameAvatarCell
      }
    }
  }
`)
</script>

<script setup lang="ts">
import QueryTable from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import { GetUsersDocument } from "src/graphql/generated/graphql"
import { useRouter } from "vue-router"
import type { QueryTableColumn } from "src/components/tables/types"

const columns: QueryTableColumn[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row.id,
    component: NameAvatarCell,
    sortable: true,
    label: "admin.users.headers.name"
  },
  {
    name: "username",
    align: "left",
    field: "username",
    sortable: true,
    label: "admin.users.headers.username"
  },
  {
    name: "email",
    align: "left",
    field: "email",
    sortable: true,
    label: "admin.users.headers.email"
  }
]

async function handleUserListBasicClick(
  _evt: Event,
  row: Record<string, unknown>
) {
  goToUserDetail(row.id as string)
}

const { push } = useRouter()
async function goToUserDetail(id: string) {
  push({
    name: "user_details",
    params: { id }
  })
}
</script>
