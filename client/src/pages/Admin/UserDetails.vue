<template>
  <QueryTable
    :refresh-btn="false"
    :query="getUserPublicationsDocument"
    t-prefix="admin.users.details.publications"
    :variables="{ id }"
    field="user.publications"
    :columns="columns"
  >
    <template #no-data>
      <div class="full-width row flex-center text-grey-7 q-gutter-sm q-py-lg">
        <q-icon size="2em" name="menu_book" />
        <span>{{ $t("admin.users.details.no_publications") }}</span>
      </div>
    </template>
    <template #body-cell-actions="scope">
      <q-td :props="scope">
        <q-btn
          color="primary"
          size="sm"
          :to="{
            name: 'publication:home',
            params: { id: scope.row.publication.id }
          }"
          :label="$t('admin.users.details.publications.actions.view')"
        />
      </q-td>
    </template>
  </QueryTable>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query getUserPublications($id: ID, $page: Int!, $first: Int!) {
    user(id: $id) {
      id
      publications(first: $first, page: $page) {
        ...QueryTable
        data {
          id
          role
          publication {
            id
            name
          }
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import { getUserPublicationsDocument } from "src/graphql/generated/graphql"
import { useI18n } from "vue-i18n"

interface Props {
  id: string
}
defineProps<Props>()

const { t } = useI18n()

const columns: QueryTableColumn[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row.publication.name,
    label: "Name"
  },
  {
    name: "role",
    align: "left",
    field: (row) => t(`admin.users.details.roles.${row.role}`),
    label: "Role"
  },
  {
    name: "actions",
    align: "right",
    field: "id",
    label: ""
  }
]
</script>
