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
  />
</template>

<script setup lang="ts">
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
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
defineProps<Props>()

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
  }
]
</script>
