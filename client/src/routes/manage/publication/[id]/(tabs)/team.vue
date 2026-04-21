<template>
  <QueryTable
    ref="queryTableRef"
    :query="GetPublicationUsersDocument"
    field="publication.users"
    t-prefix="publication.manage.users"
    :columns="columns"
    :variables="{
      id,
      roles: ['reviewer', 'review_coordinator'],
      staged: false,
      previewRoles: ['reviewer', 'review_coordinator']
    }"
    sync-url
    :default-sort="{ sortBy: 'name' }"
    :dense="isDense"
    :grid="isGrid"
    @row-click="onRowClick"
  >
    <template v-if="!isGrid" #header="headerProps">
      <q-tr class="team-group-header">
        <q-th class="team-group-spacer" />
        <q-th class="team-group-spacer" />
        <q-th
          colspan="2"
          class="text-center team-group-label bg-accent text-white"
        >
          {{ $t("publication.manage.users.groups.active") }}
        </q-th>
        <q-th
          colspan="2"
          class="text-center team-group-label bg-accent text-white"
        >
          {{ $t("publication.manage.users.groups.completed") }}
        </q-th>
      </q-tr>
      <q-tr :props="headerProps" class="bg-accent text-white">
        <q-th key="name" :props="headerProps" class="text-left">
          {{ $t("publication.manage.users.headers.name") }}
        </q-th>
        <q-th key="email" :props="headerProps" class="text-left">
          {{ $t("publication.manage.users.headers.email") }}
        </q-th>
        <q-th
          v-for="col in countHeaderCols(headerProps.cols)"
          :key="col.name"
          :props="headerProps"
          class="text-right"
        >
          {{ $t(`publication.manage.users.headers.${col.name}`) }}
        </q-th>
      </q-tr>
    </template>
    <template v-if="isGrid" #item="gridProps">
      <div class="q-pa-sm col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 column">
        <q-card
          flat
          bordered
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
          <q-card-section class="q-py-sm">
            <!-- 2x2 phase/role matrix — easier to scan than two
                 stacked active/completed groups. Mirrors the
                 layout on the team-member detail page. -->
            <table class="role-counts">
              <thead>
                <tr>
                  <th></th>
                  <th>
                    {{ $t("publication.manage.users.groups.active") }}
                  </th>
                  <th>
                    {{ $t("publication.manage.users.groups.completed") }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">
                    {{ $t("publication.manage.user_detail.role.coordinator") }}
                  </th>
                  <td>{{ gridProps.row.as_coordinator_active_count }}</td>
                  <td>{{ gridProps.row.as_coordinator_completed_count }}</td>
                </tr>
                <tr>
                  <th scope="row">
                    {{ $t("publication.manage.user_detail.role.reviewer") }}
                  </th>
                  <td>{{ gridProps.row.as_reviewer_active_count }}</td>
                  <td>{{ gridProps.row.as_reviewer_completed_count }}</td>
                </tr>
              </tbody>
            </table>
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
        aria-label="Sort review team"
      >
        <q-menu>
          <q-list dense style="min-width: 240px">
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
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { GetPublicationUsersDocument } from "src/graphql/generated/graphql"

definePage({
  name: "manage:publication:team",
  props: true,
  meta: {
    crumb: {
      label: "Review Team"
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
    name: "manage:publication:team_member",
    params: { id: props.id, userId }
  })
}

function onRowClick(_evt: Event, row: { id: string }) {
  goToDetail(row.id)
}

function countHeaderCols(cols: { name: string }[]) {
  return cols.filter((c) => c.name.endsWith("_count"))
}

// Grid-mode sort options — each sortable column surfaces here since
// grid cards don't have clickable column headers.
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
    id: "reviewer_active_desc",
    label: "Reviewing (active, most first)",
    sortBy: "as_reviewer_active_count",
    descending: true
  },
  {
    id: "coordinator_active_desc",
    label: "Coordinating (active, most first)",
    sortBy: "as_coordinator_active_count",
    descending: true
  },
  {
    id: "reviewer_completed_desc",
    label: "Reviewing (completed, most first)",
    sortBy: "as_reviewer_completed_count",
    descending: true
  },
  {
    id: "coordinator_completed_desc",
    label: "Coordinating (completed, most first)",
    sortBy: "as_coordinator_completed_count",
    descending: true
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

// Column order is intentional: group Active (reviewer + coordinator)
// first, then Completed (reviewer + coordinator). The grouped header
// above depends on this ordering.
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
    name: "as_reviewer_active_count",
    align: "right",
    field: "as_reviewer_active_count",
    sortable: true,
    label: "publication.manage.users.headers.as_reviewer_active_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  },
  {
    name: "as_coordinator_active_count",
    align: "right",
    field: "as_coordinator_active_count",
    sortable: true,
    label: "publication.manage.users.headers.as_coordinator_active_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  },
  {
    name: "as_reviewer_completed_count",
    align: "right",
    field: "as_reviewer_completed_count",
    sortable: true,
    label: "publication.manage.users.headers.as_reviewer_completed_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  },
  {
    name: "as_coordinator_completed_count",
    align: "right",
    field: "as_coordinator_completed_count",
    sortable: true,
    label: "publication.manage.users.headers.as_coordinator_completed_count",
    style: "width: 110px",
    headerStyle: "width: 110px"
  }
]
</script>

<style scoped>
/* Grid-card phase/role matrix — compact 2x2 table mirroring the
   detail page's layout. `role-counts` is the table class set on
   the card's body section. */
.role-counts {
  border-collapse: collapse;
  width: 100%;
  /* Lining numerals — some fonts slash the 0 when
     `tabular-nums` is requested, which looks like a "no" symbol. */
  font-variant-numeric: lining-nums;
}
.role-counts th,
.role-counts td {
  padding: 4px 12px;
  vertical-align: baseline;
}
.role-counts thead th,
.role-counts tbody td {
  text-align: right;
}
.role-counts thead th {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
  padding-bottom: 4px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}
.body--dark .role-counts thead th {
  color: rgba(255, 255, 255, 0.72);
  border-bottom-color: rgba(255, 255, 255, 0.16);
}
.role-counts tbody th {
  font-weight: 500;
  color: inherit;
  white-space: nowrap;
  padding-right: 16px;
  padding-left: 0;
  text-align: left;
  width: 1%;
}
.role-counts tbody td {
  font-size: 1.15rem;
  font-weight: 500;
}
/* Visual grouping for the count block:
   - col 3 starts the count section (after Email)
   - col 5 separates the Active and Completed groups */
:deep(th:nth-child(3)),
:deep(td:nth-child(3)),
:deep(th:nth-child(5)),
:deep(td:nth-child(5)) {
  border-left: 1px solid rgba(0, 0, 0, 0.12);
}
.body--dark :deep(th:nth-child(3)),
.body--dark :deep(td:nth-child(3)),
.body--dark :deep(th:nth-child(5)),
.body--dark :deep(td:nth-child(5)) {
  border-left-color: rgba(255, 255, 255, 0.18);
}
.team-group-header .team-group-label {
  font-weight: 600;
  letter-spacing: 0.02em;
}
.team-group-header .team-group-spacer {
  background: transparent;
  border: none;
  padding: 0;
}
/* Drop the table's own top & left borders (they'd wrap the empty spacer
   area) and redraw them only where wanted: above the group labels and
   above the main header row. */
:deep(.q-table--bordered) {
  border-top: 0;
  border-left: 0;
}
.team-group-header .team-group-label {
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}
.team-group-header .team-group-label:first-of-type {
  border-left: 1px solid rgba(0, 0, 0, 0.12);
}
.team-group-header + tr > th {
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}
.team-group-header + tr > th:first-child {
  border-left: 1px solid rgba(0, 0, 0, 0.12);
}
.body--dark .team-group-header .team-group-label,
.body--dark .team-group-header .team-group-label:first-of-type,
.body--dark .team-group-header + tr > th,
.body--dark .team-group-header + tr > th:first-child {
  border-color: rgba(255, 255, 255, 0.18);
}
:deep(.q-table--grid .q-table__top) {
  padding: 0 0 4px 0;
}
:deep(.q-table--grid .q-table__grid-content) {
  border-radius: 4px;
}
.user-grid-card:hover {
  border-color: var(--q-primary);
}
</style>
