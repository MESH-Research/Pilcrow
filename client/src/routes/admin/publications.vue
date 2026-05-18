<template>
  <div class="q-px-lg">
    <h2>{{ $t("publication.admin_header") }}</h2>
    <QueryTable
      ref="queryTableRef"
      :query="GetAdminPublicationsDocument"
      t-prefix="admin.publication"
      :columns="columns"
      :variables="filterVariables"
      sync-url
      :default-sort="{ sortBy: 'name' }"
      @row-click="onRowClick"
    >
      <template #top-after>
        <PublicationsFilterPanel
          v-model:visibility-filter="visibilityFilter"
          v-model:accepting-filter="acceptingFilter"
        />
        <q-btn
          color="primary"
          icon="add"
          :label="$t('publication.create_button')"
          data-cy="create_pub_button"
          @click="showCreateDialog = true"
        />
      </template>
      <template #body-cell-name="rProps">
        <q-td :props="rProps">
          <router-link
            class="text-primary"
            :to="{
              name: 'publication:home',
              params: { id: rProps.row.id }
            }"
          >
            {{ rProps.value }}
          </router-link>
        </q-td>
      </template>
      <template #body-cell-actions="rProps">
        <q-td :props="rProps">
          <q-btn
            color="primary"
            size="sm"
            dense
            icon="settings"
            :label="$t('admin.publication.actions.configure')"
            :to="destRoute(rProps.value as string, 'basic')"
          />
        </q-td>
      </template>
    </QueryTable>
    <q-dialog
      v-model="showCreateDialog"
      aria-labelledby="create-pub-dialog-title"
    >
      <q-card style="min-width: 450px">
        <q-card-section class="bg-accent text-white">
          <div
            id="create-pub-dialog-title"
            class="text-subtitle1 text-weight-bold"
          >
            {{ $t("publication.create_dialog_title") }}
          </div>
        </q-card-section>
        <q-card-section class="q-pt-md">
          <CreateForm ref="createFormRef" @created="publicationCreated" />
        </q-card-section>
        <q-card-actions align="right" class="q-px-md q-pb-md">
          <q-btn
            v-close-popup
            flat
            :label="$t('buttons.cancel')"
            color="grey-9"
          />
          <q-btn
            color="accent"
            text-color="white"
            icon="add"
            :label="$t('buttons.create')"
            @click="createFormRef?.submit()"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
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
    $public: Boolean
    $accepting_submissions: Boolean
  ) {
    publications(
      page: $page
      first: $first
      search: $search
      orderBy: $orderBy
      public: $public
      accepting_submissions: $accepting_submissions
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
import PublicationsFilterPanel, {
  defaultVisibility,
  defaultAccepting,
  type VisibilityFilter,
  type AcceptingFilter
} from "src/pages/Admin/components/PublicationsFilterPanel.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import { useRouter, useRoute } from "vue-router"
import { ref, computed, watch } from "vue"

definePage({
  name: "admin:publication:index",
  meta: {
    crumb: { label: "breadcrumbs.admin.publications" }
  }
})

const destRoute = (id: string, page: string) => ({
  name: `publication:setup:${page}`,
  params: { id }
})

const columns: QueryTableColumn[] = [
  {
    name: "name",
    field: "name",
    align: "left",
    sortable: true
  },
  {
    name: "is_publicly_visible",
    field: (row) => (row.is_publicly_visible ? "Public" : "Hidden"),
    align: "center",
    sortable: true
  },
  {
    name: "is_accepting_submissions",
    field: (row) => (row.is_accepting_submissions ? "Yes" : "No"),
    align: "center",
    sortable: true
  },
  {
    name: "created_at",
    field: "created_at",
    align: "left",
    sortable: true,
    component: DateTimeCell
  },
  {
    name: "actions",
    field: "id",
    align: "right"
  }
]

const showCreateDialog = ref(false)
const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)
const createFormRef = ref<InstanceType<typeof CreateForm> | null>(null)

const route = useRoute()
const router = useRouter()

function parseVisibility(value: unknown): VisibilityFilter {
  return value === "public" || value === "hidden" ? value : defaultVisibility
}

function parseAccepting(value: unknown): AcceptingFilter {
  return value === "yes" || value === "no" ? value : defaultAccepting
}

const visibilityFilter = ref<VisibilityFilter>(
  parseVisibility(route.query.visibility)
)
const acceptingFilter = ref<AcceptingFilter>(
  parseAccepting(route.query.accepting)
)

const filterVariables = computed(() => {
  const vars: Record<string, unknown> = {}
  if (visibilityFilter.value !== "all") {
    vars.public = visibilityFilter.value === "public"
  }
  if (acceptingFilter.value !== "all") {
    vars.accepting_submissions = acceptingFilter.value === "yes"
  }
  return vars
})

watch([visibilityFilter, acceptingFilter], ([visibility, accepting]) => {
  if (queryTableRef.value) {
    queryTableRef.value.page = 1
  }

  const query: Record<string, string> = { ...route.query } as Record<
    string,
    string
  >

  if (visibility !== defaultVisibility) query.visibility = visibility
  else delete query.visibility

  if (accepting !== defaultAccepting) query.accepting = accepting
  else delete query.accepting

  router.replace({ query })
})

const { push } = router

function onRowClick(_evt: Event, row: { id: string }) {
  push({
    name: "publication:home",
    params: { id: row.id }
  })
}

function publicationCreated(publication: { id: string }) {
  showCreateDialog.value = false
  push({
    name: "publication:setup:basic",
    params: { id: publication.id }
  })
}
</script>
