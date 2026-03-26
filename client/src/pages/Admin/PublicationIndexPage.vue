<template>
  <div class="q-px-lg">
    <nav class="q-pt-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          label="Administration"
          :to="{ name: 'admin:dashboard' }"
        />
        <q-breadcrumbs-el label="Publications" />
      </q-breadcrumbs>
    </nav>
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
      <template #top-before>
        <PublicationsFilterPanel
          v-model:visibility-filter="visibilityFilter"
          v-model:accepting-filter="acceptingFilter"
        />
      </template>
      <template #top-after>
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
            label="Configure"
            :to="destRoute(rProps.value as string, 'basic')"
          />
        </q-td>
      </template>
    </QueryTable>
    <q-dialog v-model="showCreateDialog">
      <q-card style="min-width: 450px">
        <q-card-section class="bg-accent text-white">
          <div class="text-subtitle1 text-weight-bold">
            {{ $t("publication.create_button") }}
          </div>
        </q-card-section>
        <q-card-section class="q-pt-md">
          <CreateForm ref="createFormRef" @created="publicationCreated" />
        </q-card-section>
        <q-card-actions align="right" class="q-px-md q-pb-md">
          <q-btn v-close-popup flat label="Cancel" color="grey-7" />
          <q-btn
            color="accent"
            icon="add"
            label="Create"
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
  defaultAccepting
} from "./components/PublicationsFilterPanel.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import { useRouter, useRoute } from "vue-router"
import { ref, computed, watch, onMounted } from "vue"

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
    sortable: true,
    label: "Visibility"
  },
  {
    name: "is_accepting_submissions",
    field: (row) => (row.is_accepting_submissions ? "Yes" : "No"),
    align: "center",
    sortable: true,
    label: "Accepting Submissions"
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

const showCreateDialog = ref(false)
const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)
const createFormRef = ref<InstanceType<typeof CreateForm> | null>(null)

const route = useRoute()
const router = useRouter()

function parseList(value: string | string[] | undefined): string[] {
  if (!value) return []
  const str = Array.isArray(value) ? value[0] : value
  if (!str) return []
  const inner = str.startsWith("[") ? str.slice(1, -1) : str
  return inner ? inner.split(",") : []
}

function formatList(values: string[]): string {
  return `[${values.join(",")}]`
}

const visibilityFilter = ref<string[]>(
  parseList(route.query.visibility as string)
)
const acceptingFilter = ref<string[]>(
  parseList(route.query.accepting as string)
)

onMounted(() => {
  if (visibilityFilter.value.length === 0) {
    visibilityFilter.value = [...defaultVisibility]
  }
  if (acceptingFilter.value.length === 0) {
    acceptingFilter.value = [...defaultAccepting]
  }
})

const filterVariables = computed(() => {
  const vars: Record<string, unknown> = {}
  // Only pass the filter when exactly one option is selected
  if (visibilityFilter.value.length === 1) {
    vars.public = visibilityFilter.value[0] === "public"
  }
  if (acceptingFilter.value.length === 1) {
    vars.accepting_submissions = acceptingFilter.value[0] === "yes"
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

  const isDefaultVisibility =
    visibility.length === defaultVisibility.length &&
    visibility.every((v) => defaultVisibility.includes(v))
  if (!isDefaultVisibility) query.visibility = formatList(visibility)
  else delete query.visibility

  const isDefaultAccepting =
    accepting.length === defaultAccepting.length &&
    accepting.every((v) => defaultAccepting.includes(v))
  if (!isDefaultAccepting) query.accepting = formatList(accepting)
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
