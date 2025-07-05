<template>
  <h2 class="q-pl-lg">User Search</h2>
  <query-table
    class="q-px-lg"
    :query="GET_USERS"
    t-prefix="admin.users"
    :columns
    @row-click="handleUserListBasicClick"
  />
</template>

<script setup>
import QueryTable from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import { GET_USERS } from "src/graphql/queries"

const columns = [
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
import { useRouter } from "vue-router"

async function handleUserListBasicClick(_, row) {
  goToUserDetail(row.id)
}

const { push } = useRouter()
async function goToUserDetail(id) {
  push({
    name: "user_details",
    params: { id }
  })
}
</script>
