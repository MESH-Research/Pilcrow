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
import { computed } from "vue"
import { useI18n } from "vue-i18n"
import { useRoute } from "vue-router"

definePage({
  name: "user_details",
  // No crumb — the parent layout already stacks "Users → {name}",
  // and "Publications" here would read as the tab label, not a
  // breadcrumb rung.
  meta: {}
})

const route = useRoute("user_details")
const id = computed(() => route.params.id as string)

const { t } = useI18n()

const columns: QueryTableColumn[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row.publication.name
  },
  {
    name: "role",
    align: "left",
    field: (row) => t(`admin.users.details.roles.${row.role}`)
  },
  {
    name: "actions",
    align: "right",
    field: "id"
  }
]
</script>
