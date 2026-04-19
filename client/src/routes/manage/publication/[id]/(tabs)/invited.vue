<template>
  <QueryTable
    :query="GetPublicationUsersDocument"
    field="publication.users"
    t-prefix="publication.manage.users"
    :columns="columns"
    :variables="{
      id,
      roles: ['reviewer', 'review_coordinator'],
      staged: true
    }"
    sync-url
    :default-sort="{ sortBy: 'name' }"
    @row-click="onRowClick"
  />
</template>

<script setup lang="ts">
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import { useRouter } from "vue-router"
import { GetPublicationUsersDocument } from "src/graphql/generated/graphql"

definePage({
  name: "manage:publication:invited",
  props: true,
  meta: {
    crumb: {
      label: "Invited"
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
    name: "manage:publication:team_member",
    params: { id: props.id, userId: row.id }
  })
}

const columns: QueryTableColumn[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => (row as { user: unknown }).user,
    component: NameAvatarCell,
    sortable: true,
    label: "publication.manage.users.headers.name"
  },
  {
    name: "email",
    align: "left",
    field: (row) => (row as { user: { email?: string } }).user?.email ?? "",
    sortable: true,
    label: "publication.manage.users.headers.email"
  }
]
</script>
