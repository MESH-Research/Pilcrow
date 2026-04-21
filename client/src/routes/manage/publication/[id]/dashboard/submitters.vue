<template>
  <QueryTable
    ref="queryTableRef"
    :query="GetPublicationUsersDocument"
    field="publication.users"
    t-prefix="publication.manage.users"
    :columns="columns"
    :variables="{ id, roles: ['submitter'], previewRoles: ['submitter'] }"
    sync-url
    :default-sort="{ sortBy: 'name' }"
    :dense="isDense"
    :grid="isGrid"
    @row-click="onRowClick"
  >
    <template v-if="isGrid" #item="gridProps">
      <div class="q-pa-sm col-12 col-sm-6 col-md-4 col-lg-3 column">
        <q-card
          flat
          bordered
          clickable
          class="col cursor-pointer user-grid-card"
          @click="goToDetail(gridProps.row.id)"
        >
          <q-card-section class="row items-center no-wrap q-gutter-md">
            <avatar-image :user="gridProps.row.user" size="56px" rounded />
            <div class="col column q-gutter-xs" style="min-width: 0">
              <div class="text-weight-medium ellipsis">
                {{ gridProps.row.user.name || gridProps.row.user.email }}
              </div>
              <div
                v-if="gridProps.row.user.username"
                class="text-caption text-grey-7 ellipsis"
              >
                {{ gridProps.row.user.username }}
              </div>
              <div
                v-if="gridProps.row.user.email"
                class="text-caption ellipsis"
              >
                {{ gridProps.row.user.email }}
              </div>
            </div>
          </q-card-section>
          <q-separator />
          <q-card-section class="row items-center q-py-sm">
            <span class="col text-body2 text-grey-8">
              {{ $t("publication.manage.users.headers.as_submitter_count") }}
            </span>
            <span
              v-if="gridProps.row.submissions.paginatorInfo.total > 0"
              class="row items-center q-gutter-sm"
            >
              <span
                v-for="sub in gridProps.row.submissions.data"
                :key="sub.id"
                :class="[
                  'submission-chip',
                  `bg-${styleFor(sub.status).color}`,
                  styleFor(sub.status).textClass,
                  styleFor(sub.status).pattern
                ]"
                :title="$t(`submission.status.${sub.status}`)"
                :aria-label="$t(`submission.status.${sub.status}`)"
              >
                <q-icon
                  name="description"
                  size="sm"
                  class="pattern-text-mask"
                />
                <span
                  :class="[
                    'category-badge',
                    `bg-${styleFor(sub.status).color}`,
                    styleFor(sub.status).textClass
                  ]"
                  aria-hidden="true"
                >
                  <q-icon :name="styleFor(sub.status).icon" size="10px" />
                </span>
              </span>
              <span
                v-if="overflowFor(gridProps.row) > 0"
                class="submission-chip overflow-chip bg-grey-3 text-grey-8"
                :title="
                  $t('publication.manage.users.submission_icon_overflow', {
                    n: overflowFor(gridProps.row)
                  })
                "
                :aria-label="
                  $t('publication.manage.users.submission_icon_overflow', {
                    n: overflowFor(gridProps.row)
                  })
                "
              >
                +{{ overflowFor(gridProps.row) }}
              </span>
            </span>
            <span v-else class="text-grey-5">—</span>
          </q-card-section>
        </q-card>
      </div>
    </template>
    <template #top-after>
      <q-btn
        v-if="isGrid"
        flat
        dense
        no-caps
        icon="sort"
        label="Sort"
        aria-label="Sort submitters"
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

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { useQuasar } from "quasar"
import { useRoute, useRouter } from "vue-router"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import SubmissionCountCell from "src/components/tables/common/SubmissionCountCell.vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { statusStyleMap } from "src/pages/Publication/components/statusCategories"
import { GetPublicationUsersDocument } from "src/graphql/generated/graphql"

function styleFor(status: string) {
  return (
    statusStyleMap[status] ?? {
      color: "grey-5",
      textClass: "text-white",
      icon: "description",
      pattern: ""
    }
  )
}

interface GridRow {
  submissions: {
    data: Array<unknown>
    paginatorInfo: { total: number }
  }
}

function overflowFor(row: GridRow): number {
  return Math.max(
    0,
    row.submissions.paginatorInfo.total - row.submissions.data.length
  )
}

definePage({
  name: "manage:publication:submitters",
  props: true,
  meta: {
    crumb: {
      label: "Submitters"
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

function goToDetail(userId: string) {
  router.push({
    name: "manage:publication:submitter",
    params: { id: props.id, userId }
  })
}

function onRowClick(_evt: Event, row: { id: string }) {
  goToDetail(row.id)
}

// Grid-mode sort options — mirror the column sortability in a menu
// since grid cards don't surface sortable column headers.
interface SortOption {
  id: string
  label: string
  sortBy: string
  descending: boolean
}
const sortOptions: SortOption[] = [
  { id: "name_asc", label: "Name (A–Z)", sortBy: "name", descending: false },
  { id: "name_desc", label: "Name (Z–A)", sortBy: "name", descending: true },
  { id: "email_asc", label: "Email (A–Z)", sortBy: "email", descending: false },
  {
    id: "email_desc",
    label: "Email (Z–A)",
    sortBy: "email",
    descending: true
  },
  {
    id: "submissions_desc",
    label: "Submissions (most first)",
    sortBy: "as_submitter_count",
    descending: true
  },
  {
    id: "submissions_asc",
    label: "Submissions (fewest first)",
    sortBy: "as_submitter_count",
    descending: false
  }
]
const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

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
  },
  {
    name: "as_submitter_count",
    align: "right",
    field: (row) => (row as { submissions: unknown }).submissions,
    sortable: true,
    component: SubmissionCountCell,
    label: "publication.manage.users.headers.as_submitter_count"
  }
]
</script>

<style scoped>
:deep(.q-table--grid .q-table__top) {
  padding: 0 0 4px 0;
}
:deep(.q-table--grid .q-table__grid-content) {
  border-radius: 4px;
}
.user-grid-card:hover {
  border-color: var(--q-primary);
}
.submission-chip {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  overflow: visible;
}
.category-badge {
  position: absolute;
  right: -4px;
  bottom: -4px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 0 1.5px #fff;
}
.body--dark .category-badge {
  box-shadow: 0 0 0 1.5px #1d1d1d;
}
</style>

<style>
.q-table--grid .q-table__grid-content {
  background-color: #f5f5f5;
}
.body--dark .q-table--grid .q-table__grid-content {
  background-color: #262626;
}
</style>
