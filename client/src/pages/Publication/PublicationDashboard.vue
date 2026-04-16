<template>
  <div :class="$q.screen.lt.sm ? 'q-px-sm' : 'q-px-lg'">
    <nav class="q-pt-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('publication.entity', 2)"
          :to="{ name: 'publication:index' }"
        />
        <q-breadcrumbs-el
          :label="publication?.name ?? ''"
          :to="{ name: 'publication:home', params: { id } }"
        />
        <q-breadcrumbs-el :label="$t('publication.dashboard.breadcrumb')" />
      </q-breadcrumbs>
    </nav>

    <h2>{{ publication?.name }} {{ $t("publication.dashboard.heading") }}</h2>

    <div v-if="!publication" class="q-pa-lg">{{ $t("loading") }}</div>
    <template v-else>
      <!-- Global filter controls -->
      <div class="row items-center q-mb-sm q-gutter-sm">
        <span class="text-body2 text-grey-7">Filters:</span>
        <q-btn-group flat>
          <q-btn dense no-caps flat size="sm" label="All" @click="selectAll" />
          <q-btn
            dense
            no-caps
            flat
            size="sm"
            label="None"
            @click="selectNone"
          />
          <q-btn
            dense
            no-caps
            flat
            size="sm"
            label="Invert"
            @click="invertSelection"
          />
        </q-btn-group>
      </div>

      <!-- Category Cards with Filters -->
      <div class="row q-col-gutter-md q-mb-lg items-stretch">
        <div
          v-for="category in categories"
          :key="category.key"
          class="col-xs-12 col-sm-6 col-md-3 column"
        >
          <q-card class="col">
            <q-card-section
              :class="`bg-${category.color} ${category.textClass} ${category.pattern}`"
            >
              <div class="row items-center no-wrap pattern-text-mask">
                <q-icon :name="category.icon" size="md" />
                <q-separator
                  vertical
                  class="q-mx-sm"
                  style="background: currentColor; opacity: 0.5"
                />
                <div
                  class="col text-weight-medium"
                  style="font-size: 1rem; line-height: 1.3"
                >
                  {{ $t(`publication.dashboard.categories.${category.key}`) }}
                </div>
                <div
                  class="text-weight-bold q-ml-sm"
                  style="font-size: 2rem; line-height: 1"
                >
                  {{ category.total }}
                </div>
              </div>
            </q-card-section>
            <q-card-section v-if="category.items.length > 0" class="q-py-sm">
              <div class="row items-center no-wrap">
                <q-checkbox
                  :model-value="categoryCheckboxState(category)"
                  :label="
                    isCategoryFullySelected(category)
                      ? 'Deselect all'
                      : 'Select all'
                  "
                  dense
                  class="col"
                  @update:model-value="toggleCategoryAll(category)"
                />
                <q-btn flat round dense size="xs" icon="more_vert" @click.stop>
                  <q-menu>
                    <q-list dense style="min-width: 180px">
                      <q-item
                        v-close-popup
                        clickable
                        @click="soloCategory(category)"
                      >
                        <q-item-section>
                          Show only
                          {{
                            $t(
                              `publication.dashboard.categories.${category.key}`
                            )
                          }}
                        </q-item-section>
                      </q-item>
                      <q-separator />
                      <q-item
                        v-for="item in category.items"
                        :key="item.status"
                        v-close-popup
                        clickable
                        @click="soloStatus(item.status)"
                      >
                        <q-item-section>
                          Show only
                          {{ $t(`submission.status.${item.status}`) }}
                        </q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </div>
              <q-separator class="q-my-sm" />
              <div
                v-for="item in category.items"
                :key="item.status"
                class="row items-center no-wrap q-py-xs cursor-pointer"
                @click="toggleStatus(item.status)"
              >
                <q-checkbox
                  :model-value="statusFilter.includes(item.status)"
                  :label="$t(`submission.status.${item.status}`)"
                  dense
                  class="col"
                  @update:model-value="toggleStatus(item.status)"
                />
                <span
                  class="text-body2 text-weight-bold text-center"
                  style="min-width: 24px"
                >
                  {{ item.count }}
                </span>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Submissions Table -->
      <QueryTable
        ref="queryTableRef"
        :query="GetPublicationDashboardSubmissionsDocument"
        t-prefix="publication.dashboard"
        field="publication.submissions"
        :search-hint="$t('publication.dashboard.search_hint')"
        :variables="tableVariables"
        :columns="columns"
        :visible-columns="visibleColumns"
        :dense="isDense"
        :grid="isGrid"
        sync-url
        :default-sort="{ sortBy: 'updated_at', descending: true }"
      >
        <template v-if="isGrid" #item="gridProps">
          <div class="q-pa-xs col-xs-12 col-sm-6">
            <q-card flat bordered>
              <q-card-section class="q-pa-sm">
                <div class="text-subtitle2 q-mb-xs">
                  <router-link
                    :to="{
                      name: 'submission:details',
                      params: { id: gridProps.row.id }
                    }"
                    class="text-primary"
                  >
                    {{ gridProps.row.title }}
                  </router-link>
                </div>
                <div class="row items-center q-gutter-sm q-mb-xs">
                  <q-badge
                    :color="getStatusStyle(gridProps.row.status).color"
                    :class="[
                      'text-weight-medium q-pa-sm',
                      getStatusStyle(gridProps.row.status).textClass,
                      getStatusStyle(gridProps.row.status).pattern
                    ]"
                  >
                    <q-icon
                      :name="getStatusStyle(gridProps.row.status).icon"
                      size="xs"
                    />
                    <q-separator vertical class="q-mx-xs" />
                    <span class="pattern-text-mask">
                      {{ $t(`submission.status.${gridProps.row.status}`) }}
                    </span>
                  </q-badge>
                </div>
                <div class="text-caption text-grey-7">
                  {{ $t("publication.dashboard.headers.created_by") }}:
                  {{ gridProps.row.created_by?.name ?? "" }}
                </div>
              </q-card-section>
            </q-card>
          </div>
        </template>
        <template #top-after>
          <q-btn
            flat
            dense
            :icon="expandAllReviewers ? 'unfold_less' : 'unfold_more'"
            :label="expandAllReviewers ? 'Collapse all' : 'Expand all'"
            :aria-label="
              expandAllReviewers
                ? 'Collapse all reviewer lists'
                : 'Expand all reviewer lists'
            "
            no-caps
            @click="expandAllReviewers = !expandAllReviewers"
          />
        </template>
      </QueryTable>
    </template>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPublicationDashboard($id: ID!) {
    publication(id: $id) {
      id
      name
      effective_role
      submission_status_counts {
        status
        count
      }
    }
  }
`)

graphql(`
  query GetPublicationDashboardSubmissions(
    $id: ID!
    $page: Int
    $first: Int
    $search: String
    $status: [SubmissionStatus!]
    $orderBy: [QuerySubmissionsOrderByOrderByClause!]
  ) {
    publication(id: $id) {
      id
      submissions(
        page: $page
        first: $first
        search: $search
        status: $status
        orderBy: $orderBy
      ) {
        ...QueryTable
        data {
          id
          title
          status
          created_at
          updated_at
          created_by {
            id
            ...NameAvatarCell
          }
          review_coordinators {
            id
            ...NameAvatarCell
          }
          reviewers {
            id
            ...NameAvatarCell
          }
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, provide, ref, watch } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import { useRouter, useRoute } from "vue-router"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import TextCell from "src/components/tables/common/TextCell.vue"
import NameAvatarListCell, {
  NameAvatarListExpandAllKey
} from "src/components/tables/common/NameAvatarListCell.vue"
import StatusBadgeCell from "./components/StatusBadgeCell.vue"
import {
  statusCategories,
  statusStyleMap,
  type StatusCategoryDef
} from "./components/statusCategories"
import {
  GetPublicationDashboardDocument,
  GetPublicationDashboardSubmissionsDocument,
  type SubmissionStatus
} from "src/graphql/generated/graphql"

interface Props {
  id: string
}

const props = defineProps<Props>()
const $q = useQuasar()
const router = useRouter()
const route = useRoute()

// Shared state for expanding/collapsing all reviewer lists at once.
const expandAllReviewers = ref(false)
provide(NameAvatarListExpandAllKey, expandAllReviewers)

// Responsive: hide less-essential columns on smaller viewports.
const allColumnNames = [
  "title",
  "created_by",
  "status",
  "review_coordinators",
  "reviewers",
  "updated_at"
]

const visibleColumns = computed(() => {
  if ($q.screen.lt.sm) {
    return ["title", "status"]
  }
  if ($q.screen.lt.md) {
    return ["title", "created_by", "status", "updated_at"]
  }
  if ($q.screen.lt.lg) {
    return ["title", "created_by", "status", "reviewers", "updated_at"]
  }
  return allColumnNames
})

const isDense = computed(() => $q.screen.lt.md)
const isGrid = computed(() => $q.screen.lt.sm)

const defaultStatusStyle = {
  color: "grey",
  textClass: "text-white",
  icon: "help",
  pattern: ""
}
function getStatusStyle(status: string) {
  return statusStyleMap[status] ?? defaultStatusStyle
}

// Fetch publication data with global status counts
const { result } = useQuery(GetPublicationDashboardDocument, { id: props.id })
const publication = computed(() => result.value?.publication ?? null)

// Redirect if user doesn't have access
watch(publication, (pub) => {
  if (
    pub &&
    pub.effective_role !== "publication_admin" &&
    pub.effective_role !== "editor"
  ) {
    router.replace("/error403")
  }
})

// Category definitions — built from shared config + live counts
interface CategoryItem {
  status: string
  count: number
}

interface StatusCategory extends StatusCategoryDef {
  total: number
  items: CategoryItem[]
}

const statusCountMap = computed(() => {
  const map = new Map<string, number>()
  for (const sc of publication.value?.submission_status_counts ?? []) {
    map.set(sc.status, sc.count)
  }
  return map
})

const categories = computed<StatusCategory[]>(() =>
  statusCategories.map((cat) => {
    const items = cat.statuses.map((status) => ({
      status,
      count: statusCountMap.value.get(status) ?? 0
    }))
    return {
      ...cat,
      total: items.reduce((sum, b) => sum + b.count, 0),
      items
    }
  })
)

// Status filter state
const allStatuses = computed(() => categories.value.flatMap((c) => c.statuses))

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

const statusFilter = ref<string[]>(parseList(route.query.status as string))

// Default: all statuses selected
if (statusFilter.value.length === 0) {
  statusFilter.value = [...allStatuses.value]
}

function toggleStatus(status: string) {
  const idx = statusFilter.value.indexOf(status)
  if (idx >= 0) {
    statusFilter.value = statusFilter.value.filter((s) => s !== status)
  } else {
    statusFilter.value = [...statusFilter.value, status]
  }
}

function soloCategory(category: StatusCategory) {
  statusFilter.value = [...category.statuses]
}

function soloStatus(status: string) {
  statusFilter.value = [status]
}

function selectAll() {
  statusFilter.value = [...allStatuses.value]
}

function selectNone() {
  statusFilter.value = []
}

function invertSelection() {
  statusFilter.value = allStatuses.value.filter(
    (s) => !statusFilter.value.includes(s)
  )
}

function isCategoryFullySelected(category: StatusCategory): boolean {
  return category.statuses.every((s) => statusFilter.value.includes(s))
}

function isCategoryPartiallySelected(category: StatusCategory): boolean {
  return (
    !isCategoryFullySelected(category) &&
    category.statuses.some((s) => statusFilter.value.includes(s))
  )
}

function categoryCheckboxState(category: StatusCategory): boolean | null {
  if (isCategoryFullySelected(category)) return true
  if (isCategoryPartiallySelected(category)) return null
  return false
}

function toggleCategoryAll(category: StatusCategory) {
  if (isCategoryFullySelected(category)) {
    statusFilter.value = statusFilter.value.filter(
      (s) => !category.statuses.includes(s)
    )
  } else {
    const toAdd = category.statuses.filter(
      (s) => !statusFilter.value.includes(s)
    )
    statusFilter.value = [...statusFilter.value, ...toAdd]
  }
}

// Sync filter to URL and reset pagination
const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

watch(statusFilter, (status) => {
  if (queryTableRef.value) {
    queryTableRef.value.page = 1
  }

  const query: Record<string, string> = { ...route.query } as Record<
    string,
    string
  >

  const isDefault =
    status.length === allStatuses.value.length &&
    allStatuses.value.every((s) => status.includes(s))
  if (!isDefault) query.status = formatList(status)
  else delete query.status

  router.replace({ query })
})

// Table config — publication ID is always passed, status filter is dynamic
const tableVariables = computed(() => ({
  id: props.id,
  status: statusFilter.value as SubmissionStatus[]
}))

const columns: QueryTableColumn[] = [
  {
    name: "title",
    field: "title",
    align: "left",
    sortable: true,
    component: TextCell,
    linkTo: (row) => ({
      name: "submission:details",
      params: { id: row.id as string }
    }),
    label: "Title",
    style: "white-space: normal"
  },
  {
    name: "created_by",
    field: (row) => row.created_by ?? null,
    align: "left",
    component: NameAvatarCell,
    label: "Submitter"
  },
  {
    name: "status",
    field: "status",
    align: "left",
    sortable: true,
    component: StatusBadgeCell,
    label: "Status"
  },
  {
    name: "review_coordinators",
    field: (row) => (row.review_coordinators ?? [])[0] ?? null,
    align: "left",
    component: NameAvatarCell,
    label: "Review Coordinator"
  },
  {
    name: "reviewers",
    field: (row) => row.reviewers ?? [],
    align: "left",
    component: NameAvatarListCell,
    label: "Reviewers"
  },
  {
    name: "updated_at",
    field: "updated_at",
    align: "left",
    sortable: true,
    component: DateTimeCell,
    label: "Updated"
  }
]
</script>

<style scoped>
:deep(.q-table tbody td) {
  vertical-align: top;
}
</style>
