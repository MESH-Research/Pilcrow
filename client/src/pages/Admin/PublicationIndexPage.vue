<template>
  <div>
    <h2 class="q-pl-lg">{{ $t("publication.admin_header") }}</h2>
    <q-expansion-item
      :label="$t('publication.create_button')"
      switch-toggle-side
      header-class="light-grey"
      data-cy="create_pub_button"
    >
      <CreateForm @created="publicationCreated" />
    </q-expansion-item>
    <QueryTable
      :query="GetAdminPublicationsDocument"
      t-prefix="admin.publication"
      :columns="columns"
      sync-url
      :default-sort="{ sortBy: 'name' }"
    >
      <template #body-cell-actions="rProps">
        <q-td :props="rProps">
          <q-btn-group flat>
            <q-btn
              icon="visibility"
              class="dark-grey"
              :to="{
                name: 'publication:home',
                params: { id: rProps.value }
              }"
              :aria-label="$t('publication.view_button_aria')"
            >
              <q-tooltip :delay="500">
                {{ $t("publication.view_button") }}
              </q-tooltip>
            </q-btn>
            <q-btn-dropdown
              auto-close
              class="dark-grey"
              :aria-label="$t('publication.configure')"
            >
              <q-list>
                <q-item :to="destRoute(rProps.value as string, 'basic')">
                  <q-item-section avatar>
                    <q-icon class="dark-grey" name="tune" />
                  </q-item-section>
                  <q-item-section>
                    {{ $t(pageTitleKey("basic")) }}
                  </q-item-section>
                </q-item>
                <q-item :to="destRoute(rProps.value as string, 'users')">
                  <q-item-section avatar>
                    <q-icon class="dark-grey" name="people" />
                  </q-item-section>
                  <q-item-section>
                    {{ $t(pageTitleKey("users")) }}
                  </q-item-section>
                </q-item>
                <q-item :to="destRoute(rProps.value as string, 'criteria')">
                  <q-item-section avatar>
                    <q-icon class="dark-grey" name="card_membership" />
                  </q-item-section>
                  <q-item-section>
                    {{ $t(pageTitleKey("criteria")) }}
                  </q-item-section>
                </q-item>
                <q-item :to="destRoute(rProps.value as string, 'content')">
                  <q-item-section avatar>
                    <q-icon class="dark-grey" name="toc" />
                  </q-item-section>
                  <q-item-section>
                    {{ $t(pageTitleKey("content")) }}
                  </q-item-section>
                </q-item>
              </q-list>
              <template #label>
                <q-icon name="settings" />
                <q-tooltip :delay="500">{{
                  $t("publication.configure")
                }}</q-tooltip>
              </template>
            </q-btn-dropdown>
          </q-btn-group>
        </q-td>
      </template>
    </QueryTable>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetAdminPublications(
    $page: Int
    $first: Int
    $search: String
    $orderBy: [QueryPublicationsOrderByOrderByClause!]
  ) {
    publications(
      page: $page
      first: $first
      search: $search
      orderBy: $orderBy
    ) {
      ...QueryTable
      data {
        id
        name
        is_publicly_visible
        is_accepting_submissions
        created_at
      }
    }
  }
`)
</script>

<script setup lang="ts">
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import { GetAdminPublicationsDocument } from "src/graphql/generated/graphql"
import CreateForm from "src/components/forms/Publication/CreateForm.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import { useRouter } from "vue-router"

const destRoute = (id: string, page: string) => ({
  name: `publication:setup:${page}`,
  params: { id }
})

const columns: QueryTableColumn[] = [
  {
    name: "name",
    field: "name",
    align: "left",
    sortable: true,
    label: "Name"
  },
  {
    name: "is_publicly_visible",
    field: (row) => (row.is_publicly_visible ? "Public" : "Hidden"),
    align: "center",
    label: "Visibility"
  },
  {
    name: "is_accepting_submissions",
    field: (row) => (row.is_accepting_submissions ? "Yes" : "No"),
    align: "center",
    label: "Accepting"
  },
  {
    name: "created_at",
    field: "created_at",
    align: "left",
    sortable: true,
    component: DateTimeCell,
    label: "Created"
  },
  {
    name: "actions",
    field: "id",
    align: "right",
    label: "Actions"
  }
]

const pageTitleKey = (page: string) => `publication.setup_pages.${page}`

const { push } = useRouter()
function publicationCreated(publication: { id: string }) {
  push({
    name: "publication:setup:basic",
    params: { id: publication.id }
  })
}
</script>
