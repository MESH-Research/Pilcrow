<template>
  <QueryTable
    ref="queryTableRef"
    :query="GetPublicationDashboardSubmissionsDocument"
    t-prefix="publication.dashboard"
    field="publication.submissions"
    :search-hint="$t('publication.dashboard.search_hint')"
    :variables="tableVariables"
    :columns="columns"
    :dense="isDense"
    :grid="isGrid"
    sync-url
    :default-sort="{ sortBy: 'updated_at', descending: true }"
  >
    <template v-if="isGrid" #item="gridProps">
      <div class="q-pa-sm col-12 col-sm-6 col-lg-4 col-xl-3 column">
        <SubmissionCard :submission="gridProps.row" class="col" />
      </div>
    </template>
    <template #top-after>
      <!-- Status filter menu — lives in the table header so it's
           discoverable next to Sort/View and doesn't occupy its
           own row above the table. Select all / none / invert sit
           at the top of the popup where the hand lands first; the
           grouped checkbox list (one group per category) follows. -->
      <q-btn
        flat
        dense
        no-caps
        icon="filter_list"
        :label="
          isSmallScreen
            ? $t('publication.dashboard.filters.short_label')
            : $t('publication.dashboard.filters.selected', {
                n: statusFilter.length,
                total: allStatuses.length
              })
        "
        :aria-label="$t('publication.dashboard.filters.short_label')"
      >
        <q-menu>
          <q-list dense style="min-width: 240px">
            <q-item class="q-py-xs">
              <q-btn-group flat stretch class="full-width">
                <q-btn
                  dense
                  no-caps
                  flat
                  size="sm"
                  :label="$t('publication.dashboard.filters.all')"
                  @click="selectAll"
                />
                <q-btn
                  dense
                  no-caps
                  flat
                  size="sm"
                  :label="$t('publication.dashboard.filters.none')"
                  @click="selectNone"
                />
                <q-btn
                  dense
                  no-caps
                  flat
                  size="sm"
                  :label="$t('publication.dashboard.filters.invert')"
                  @click="invertSelection"
                />
              </q-btn-group>
            </q-item>
            <q-separator />
            <template v-for="category in categories" :key="category.key">
              <q-item-label header>
                {{ $t(`publication.dashboard.categories.${category.key}`) }}
              </q-item-label>
              <q-item
                v-for="status in category.statuses"
                :key="status"
                v-ripple
                tag="label"
                clickable
                dense
              >
                <q-item-section avatar>
                  <q-checkbox
                    :model-value="statusFilter.includes(status)"
                    @update:model-value="toggleStatus(status)"
                  />
                </q-item-section>
                <q-item-section>
                  {{ $t(`submission.status.${status}`) }}
                </q-item-section>
              </q-item>
              <q-separator />
            </template>
          </q-list>
        </q-menu>
      </q-btn>
      <q-btn
        v-if="isGrid"
        flat
        dense
        no-caps
        icon="sort"
        label="Sort"
        aria-label="Sort submissions"
      >
        <q-menu>
          <q-list dense style="min-width: 220px">
            <q-item
              v-for="option in sortOptions"
              :key="option.id"
              v-close-popup
              clickable
              @click="applySort(option)"
            >
              <q-item-section>{{ option.label }}</q-item-section>
              <q-item-section v-if="isCurrentSort(option)" side>
                <q-icon name="check" size="xs" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
      <q-btn
        v-if="!isGrid"
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
        @click="toggleExpandAllReviewers"
      />
      <q-btn
        v-if="!isSmallScreen"
        flat
        dense
        no-caps
        :icon="isGrid ? 'table_rows' : 'grid_view'"
        :label="isGrid ? 'Table view' : 'Grid view'"
        :aria-label="isGrid ? 'Switch to table view' : 'Switch to grid view'"
        @click="toggleViewPreference"
      />
    </template>
  </QueryTable>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

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
          submitted_at
          created_by {
            id
            ...NameAvatarCell
          }
          review_coordinators {
            id
            username
            ...NameAvatarCell
          }
          reviewers {
            id
            username
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
import { useQuasar } from "quasar"
import { useRoute, useRouter } from "vue-router"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import TextCell from "src/components/tables/common/TextCell.vue"
import ReviewTeamCell, {
  ReviewTeamExpandAllKey
} from "src/components/tables/common/ReviewTeamCell.vue"
import StatusBadgeCell from "src/pages/Publication/components/StatusBadgeCell.vue"
import SubmissionCard from "src/pages/Publication/components/SubmissionCard.vue"
import { statusCategories } from "src/pages/Publication/components/statusCategories"
import {
  GetPublicationDashboardSubmissionsDocument,
  type SubmissionStatus
} from "src/graphql/generated/graphql"

definePage({
  name: "manage:publication:submissions",
  props: true,
  meta: {
    crumb: {
      label: "Submissions"
    }
  }
})

interface Props {
  id: string
}
const props = defineProps<Props>()
const $q = useQuasar()
const router = useRouter()
const route = useRoute()

// Shared state for expanding/collapsing all reviewer lists at once.
const expandAllReviewers = ref(false)
function toggleExpandAllReviewers() {
  expandAllReviewers.value = !expandAllReviewers.value
}
provide(ReviewTeamExpandAllKey, expandAllReviewers)

// Grid-mode sort options (table mode uses column headers).
interface SortOption {
  id: string
  label: string
  sortBy: string
  descending: boolean
}
const sortOptions: SortOption[] = [
  {
    id: "updated_desc",
    label: "Updated (newest first)",
    sortBy: "updated_at",
    descending: true
  },
  {
    id: "updated_asc",
    label: "Updated (oldest first)",
    sortBy: "updated_at",
    descending: false
  },
  {
    id: "title_asc",
    label: "Title (A–Z)",
    sortBy: "title",
    descending: false
  },
  {
    id: "title_desc",
    label: "Title (Z–A)",
    sortBy: "title",
    descending: true
  },
  {
    id: "status_asc",
    label: "Status (A–Z)",
    sortBy: "status",
    descending: false
  },
  {
    id: "status_desc",
    label: "Status (Z–A)",
    sortBy: "status",
    descending: true
  }
]

function isCurrentSort(option: SortOption): boolean {
  const p = queryTableRef.value?.pagination
  if (!p) return false
  return p.sortBy === option.sortBy && p.descending === option.descending
}

function applySort(option: SortOption) {
  const p = queryTableRef.value?.pagination
  if (!p) return
  p.sortBy = option.sortBy
  p.descending = option.descending
  p.page = 1
}

// Responsive: switch to grid cards on small screens. Users can also
// opt into grid mode at larger sizes via a toggle; the preference is
// persisted in the `view` URL param.
const viewPreference = ref<"grid" | null>(
  route.query.view === "grid" ? "grid" : null
)
const isSmallScreen = computed(() => $q.screen.lt.md)
const isGrid = computed(
  () => isSmallScreen.value || viewPreference.value === "grid"
)
const isDense = computed(() => $q.screen.md)

function toggleViewPreference() {
  viewPreference.value = viewPreference.value === "grid" ? null : "grid"
}

watch(viewPreference, (value) => {
  const query: Record<string, string> = { ...route.query } as Record<
    string,
    string
  >
  if (value === "grid") query.view = "grid"
  else delete query.view
  router.replace({ query })
})

const categories = statusCategories
const allStatuses = categories.flatMap((c) => c.statuses)

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

// Default: all statuses selected when no filter in URL.
if (statusFilter.value.length === 0) {
  statusFilter.value = [...allStatuses]
}

function toggleStatus(status: string) {
  const idx = statusFilter.value.indexOf(status)
  if (idx >= 0) {
    statusFilter.value = statusFilter.value.filter((s) => s !== status)
  } else {
    statusFilter.value = [...statusFilter.value, status]
  }
}

function selectAll() {
  statusFilter.value = [...allStatuses]
}

function selectNone() {
  statusFilter.value = []
}

function invertSelection() {
  statusFilter.value = allStatuses.filter(
    (s) => !statusFilter.value.includes(s)
  )
}

// Sync filter to URL and reset pagination on change.
const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

function isSameFilter(a: string[], b: string[]): boolean {
  if (a.length !== b.length) return false
  const set = new Set(a)
  return b.every((s) => set.has(s))
}

watch(statusFilter, (status) => {
  if (queryTableRef.value) {
    queryTableRef.value.page = 1
  }
  const query: Record<string, string> = { ...route.query } as Record<
    string,
    string
  >
  const isDefault =
    status.length === allStatuses.length &&
    allStatuses.every((s) => status.includes(s))
  if (!isDefault) query.status = formatList(status)
  else delete query.status
  router.replace({ query })
})

// Re-sync from the URL when external navigation (e.g. clicking a
// dashboard chip) changes the `status` query while the component
// stays mounted. Without this, vue-router reuses the instance and
// the filter ref stays at whatever the user had before.
//
// Distinguish "query key absent" (default → all selected) from
// "query key present but empty" (`?status=[]`, i.e. None was
// explicitly chosen). If we collapsed both into "all selected",
// the None button would instantly revert itself.
watch(
  () => route.query.status,
  (value) => {
    if (value === undefined) {
      if (!isSameFilter(statusFilter.value, allStatuses)) {
        statusFilter.value = [...allStatuses]
      }
      return
    }
    const parsed = parseList(value as string | string[])
    if (!isSameFilter(statusFilter.value, parsed)) {
      statusFilter.value = parsed
    }
  }
)

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
    captionAbove: (row) => (row.id != null ? `#${row.id as string}` : null),
    label: "Title",
    classes: "title-cell",
    style: "white-space: normal"
  },
  {
    name: "created_by",
    field: (row) => row.created_by ?? null,
    align: "left",
    component: NameAvatarCell,
    linkTo: (row) => {
      const user = (row as { created_by?: { id?: string } }).created_by
      return user?.id
        ? {
            name: "manage:publication:submitter" as const,
            params: { id: props.id, userId: user.id }
          }
        : null
    },
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
    name: "review_team",
    field: (row) => ({
      coordinator: (row.review_coordinators ?? [])[0] ?? null,
      reviewers: row.reviewers ?? []
    }),
    align: "left",
    component: ReviewTeamCell,
    label: "Review Team"
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
  padding-top: 12px;
  padding-bottom: 12px;
}
/* Title column: clamp to two lines with mid-word break so a very
   long title doesn't stretch the column or blow up row height. The
   full title is available via the link's hover tooltip. Kept at two
   lines (versus three in the grid card) because table rows should
   stay as compact as the other cells allow. The class is attached
   to the td via the column's `classes` option; targeting the
   column name directly doesn't work because QTd doesn't auto-apply
   the column name as a class. */
:deep(.q-table td.title-cell) {
  max-width: 320px;
}
:deep(.q-table td.title-cell .q-item__label) {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  overflow-wrap: anywhere;
  word-break: break-word;
  white-space: normal;
}
:deep(.q-table--grid .q-table__top) {
  padding: 0 0 4px 0;
}
:deep(.q-table--grid .q-table__grid-content) {
  border-radius: 4px;
}
</style>

<!-- Light/dark grid tint behind the cards. Kept out of the scoped
     block: Quasar-rendered elements don't carry our `data-v-*`
     attribute, and the scoped light rule was losing specificity
     against an identical unscoped dark rule below, leaving light
     mode without the grey fill. Keeping both rules side-by-side
     here means they compete on equal footing. -->
<style>
.q-table--grid .q-table__grid-content {
  background-color: #f5f5f5;
}
.body--dark .q-table--grid .q-table__grid-content {
  background-color: #262626;
}
</style>
