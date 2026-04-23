<template>
  <div class="q-px-lg q-pt-md">
    <h2 class="q-my-none q-mb-md">
      {{ $t("publication.manage.dashboard.heading") }}
    </h2>

    <div
      v-if="isAppAdmin"
      class="q-mb-md row items-center q-gutter-sm"
      data-cy="manage_admin_hint"
    >
      <q-badge
        outline
        color="primary"
        class="manage-admin-hint__badge"
        :aria-label="$t('publication.manage.dashboard.admin_hint_reason')"
      >
        {{ $t("publication.manage.dashboard.admin_hint_label") }}
      </q-badge>
      <q-tooltip anchor="top middle" self="bottom middle">
        {{ $t("publication.manage.dashboard.admin_hint_reason") }}
      </q-tooltip>
      <span class="text-body2 text-grey-7">
        <i18n-t
          keypath="publication.manage.dashboard.admin_hint"
          tag="span"
          scope="global"
        >
          <template #link>
            <router-link
              :to="{ name: 'admin:publication:index' }"
              class="text-primary"
            >
              {{ $t("publication.manage.dashboard.admin_hint_link") }}
            </router-link>
          </template>
        </i18n-t>
      </span>
    </div>

    <QueryTable
      ref="queryTableRef"
      :query="GetManagedPublicationsDocument"
      field="currentUser.publications"
      t-prefix="publication.manage.dashboard"
      :columns="columns"
      :grid="isGrid"
      :dense="isDense"
      sync-url
      :default-sort="{ sortBy: 'name' }"
      @row-click="onRowClick"
    >
      <template v-if="isGrid" #item="gridProps">
        <div class="q-pa-sm col-12 col-sm-6 col-lg-4 col-xl-3 column">
          <q-card
            flat
            bordered
            class="manage-publication-card full-height column col"
          >
            <!-- Header: name linked to publication home + role badge. -->
            <q-card-section class="q-py-sm q-px-md">
              <div class="row items-start no-wrap q-gutter-sm">
                <div class="col" style="min-width: 0">
                  <router-link
                    :to="{
                      name: 'manage:publication:dashboard',
                      params: { id: pub(gridProps.row).id }
                    }"
                    class="text-primary publication-title"
                    :title="pub(gridProps.row).name"
                  >
                    {{ pub(gridProps.row).name }}
                  </router-link>
                </div>
                <q-badge
                  outline
                  :color="
                    gridProps.row.role === 'publication_admin'
                      ? 'primary'
                      : 'secondary'
                  "
                  class="role-badge q-mt-xs"
                >
                  {{
                    $t(
                      `publication.manage.dashboard.role.${gridProps.row.role}`
                    )
                  }}
                </q-badge>
              </div>
            </q-card-section>

            <q-separator />

            <!-- Stage-grouped snapshot: mirrors the table's two-row
                 header. Each stage gets a small heading; sub-rows
                 break out the category split (Needs Action / In
                 Progress) within that stage. Each sub-row is a
                 click target into the filtered dashboard. -->
            <q-card-section class="q-py-xs q-px-none col">
              <template
                v-for="group in stageGroupsForRow(gridProps.row)"
                :key="group.key"
              >
                <div class="stage-heading q-px-md q-pt-sm">
                  {{ $t(`publication.manage.dashboard.stages.${group.key}`) }}
                </div>
                <q-list>
                  <q-item
                    v-for="row in group.rows"
                    :key="`${group.key}-${row.category}`"
                    clickable
                    :to="dashboardFilterTo(pub(gridProps.row).id, row.statuses)"
                    class="category-row"
                  >
                    <q-item-section side>
                      <q-icon
                        :name="row.icon"
                        :class="`text-${row.color}`"
                        size="sm"
                      />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>
                        {{
                          $t(`publication.dashboard.categories.${row.category}`)
                        }}
                      </q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <!-- Count sits in a small colored + patterned chip
                           when the row is a needs_action with a non-zero
                           count. Keeps the visual weight focused on
                           "where the work is" without coloring the
                           whole row. -->
                      <span
                        :class="[
                          'category-count-chip',
                          row.total > 0
                            ? [
                                'category-count-chip--highlighted',
                                `bg-${row.color}`,
                                row.textClass,
                                row.pattern
                              ]
                            : 'category-count-chip--muted'
                        ]"
                      >
                        <span class="category-count-chip-text">
                          {{ row.total }}
                        </span>
                      </span>
                    </q-item-section>
                  </q-item>
                </q-list>
              </template>
            </q-card-section>

            <q-separator />

            <q-card-section class="q-py-sm q-px-md row items-center no-wrap">
              <div class="col text-caption text-grey-7">
                {{
                  $t("publication.manage.dashboard.total_submissions", {
                    n: totalForRow(gridProps.row)
                  })
                }}
              </div>
              <q-btn
                flat
                dense
                no-caps
                color="primary"
                icon-right="arrow_forward"
                :to="{
                  name: 'manage:publication:dashboard',
                  params: { id: pub(gridProps.row).id }
                }"
                :label="$t('publication.manage.dashboard.open')"
              />
            </q-card-section>
          </q-card>
        </div>
      </template>

      <template #no-data="{ filter: activeFilter }">
        <EmptyState
          data-cy="manage_empty_state"
          :icon="activeFilter ? undefined : 'dashboard_customize'"
          :search-term="activeFilter"
          :message="
            activeFilter
              ? $t('publication.manage.dashboard.empty_filter_hint', {
                  term: activeFilter
                })
              : $t('publication.manage.dashboard.empty_hint')
          "
        />
      </template>

      <template #top-after>
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

      <!-- Two-row grouped header: top row shows the workflow stage
           (Screening / Reviewing / Decision / Closed) and spans any
           sub-columns belonging to that stage; the sub-row names
           the category split (Needs Action / In Progress) within
           each stage. Publication / Closed span both rows since
           they don't split further. `stage-start` draws a vertical
           divider on the left edge so the stage groupings read as
           distinct bands. -->
      <template v-if="!isGrid" #header="headerProps">
        <!-- Top row: workflow stage groupings. Mirrors the
             Review Team table's header pattern — spacer th above
             the ungrouped Publication column, then bg-accent
             group labels spanning each stage's sub-columns. -->
        <q-tr class="stage-group-header">
          <q-th class="stage-group-spacer" />
          <q-th class="text-center stage-group-label bg-accent text-white">
            {{ $t("publication.manage.dashboard.stages.screening") }}
          </q-th>
          <q-th
            colspan="2"
            class="text-center stage-group-label bg-accent text-white"
          >
            {{ $t("publication.manage.dashboard.stages.reviewing") }}
          </q-th>
          <q-th class="text-center stage-group-label bg-accent text-white">
            {{ $t("publication.manage.dashboard.stages.decision") }}
          </q-th>
          <q-th class="text-right stage-group-label bg-accent text-white">
            {{ $t("publication.manage.dashboard.stages.closed") }}
          </q-th>
        </q-tr>
        <!-- Second row: actual column headers. Publication gets
             `:props`+`:key="name"` so Quasar wires up sort; the
             count columns aren't sortable but we still pass props
             for consistent styling alignment. -->
        <q-tr :props="headerProps" class="bg-accent text-white">
          <q-th key="name" :props="headerProps" class="text-left">
            {{ $t("publication.manage.dashboard.headers.name") }}
          </q-th>
          <q-th class="text-right stage-start">
            <q-icon
              v-if="needsActionStyle"
              :name="needsActionStyle.icon"
              size="xs"
              class="q-mr-xs"
            />
            {{ $t("publication.dashboard.categories.needs_action") }}
          </q-th>
          <q-th class="text-right stage-start">
            <q-icon
              v-if="needsActionStyle"
              :name="needsActionStyle.icon"
              size="xs"
              class="q-mr-xs"
            />
            {{ $t("publication.dashboard.categories.needs_action") }}
          </q-th>
          <q-th class="text-right">
            <q-icon
              v-if="inProgressStyle"
              :name="inProgressStyle.icon"
              size="xs"
              class="q-mr-xs"
            />
            {{ $t("publication.dashboard.categories.in_progress") }}
          </q-th>
          <q-th class="text-right stage-start">
            <q-icon
              v-if="needsActionStyle"
              :name="needsActionStyle.icon"
              size="xs"
              class="q-mr-xs"
            />
            {{ $t("publication.dashboard.categories.needs_action") }}
          </q-th>
          <q-th class="text-right stage-start">
            <q-icon
              v-if="completedStyle"
              :name="completedStyle.icon"
              size="xs"
              class="q-mr-xs"
            />
            {{ $t("publication.manage.dashboard.headers.closed") }}
          </q-th>
        </q-tr>
      </template>
    </QueryTable>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetManagedPublications(
    $page: Int!
    $first: Int!
    $search: String
    $orderBy: [PublicationAssignmentOrderBy!]
  ) {
    currentUser {
      id
      publications(
        roles: [publication_admin, editor]
        page: $page
        first: $first
        search: $search
        orderBy: $orderBy
      ) {
        ...QueryTable
        data {
          id
          role
          publication {
            id
            name
            submission_status_counts {
              status
              count
            }
          }
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { useQuasar } from "quasar"
import { useRoute, useRouter } from "vue-router"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import PublicationNameCell from "src/components/tables/common/PublicationNameCell.vue"
import CategoryCountCell from "src/components/tables/common/CategoryCountCell.vue"
import EmptyState from "src/components/molecules/EmptyState.vue"
import { useCurrentUser } from "src/use/user"
import {
  statusCategories,
  type StatusCategoryDef
} from "src/pages/Publication/components/statusCategories"
import { GetManagedPublicationsDocument } from "src/graphql/generated/graphql"

definePage({
  name: "manage:index",
  meta: {
    // Page heading already reads "Manage" — suppress the breadcrumb
    // so we don't show the same label twice.
    crumb: false
  }
})

interface ManagedPublication {
  id: string
  name: string
  submission_status_counts?: Array<{ status: string; count: number }> | null
}

// Each row from `currentUser.publications` is a pivot assignment:
// {id, role, publication:{…}}. Helpers pull the nested publication so
// the rest of this file can read it with a single call.
interface ManagedAssignment {
  id: string
  role: string
  publication: ManagedPublication
}

function pub(row: unknown): ManagedPublication {
  return (row as ManagedAssignment).publication
}

const $q = useQuasar()
const route = useRoute()
const router = useRouter()
const { isAppAdmin } = useCurrentUser()

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

const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

function onRowClick(_evt: Event, row: unknown) {
  router.push({
    name: "manage:publication:dashboard",
    params: { id: pub(row).id }
  })
}

// Grid-card stage groupings that mirror the table's two-row header.
// Each stage has one sub-row per category split present in that
// stage — Screening / Decision have only Needs Action, Reviewing
// splits into Needs Action + In Progress, Closed collapses into
// a single Completed row. Icons + colors come from statusCategories
// so sub-rows match the dashboard chips and the table's count cells.
interface StageGroupRow {
  category: string
  color: string
  icon: string
  // Quasar text color class (e.g. "text-dark", "text-white") to pair
  // with `bg-{color}` when the row is highlighted.
  textClass: string
  // CSS class name for the a11y pattern overlay (e.g. pattern-diagonal).
  pattern: string
  statuses: readonly string[]
  total: number
}
interface StageGroup {
  key: string
  rows: StageGroupRow[]
}

function stageGroupsForRow(row: unknown): StageGroup[] {
  return stageGroups(pub(row))
}

function stageGroups(publication: ManagedPublication): StageGroup[] {
  const na = statusCategories.find((c) => c.key === "needs_action")
  const ip = statusCategories.find((c) => c.key === "in_progress")
  const done = statusCategories.find((c) => c.key === "completed")
  const row = (
    cat: StatusCategoryDef | undefined,
    statuses: readonly string[]
  ): StageGroupRow | null => {
    if (!cat) return null
    return {
      category: cat.key,
      color: cat.color,
      icon: cat.icon,
      textClass: cat.textClass,
      pattern: cat.pattern,
      statuses,
      total: countStatuses(
        publication as unknown as Record<string, unknown>,
        statuses
      )
    }
  }
  const rows: StageGroupRow[][] = [
    [row(na, SCREENING_NA_STATUSES)].filter(Boolean) as StageGroupRow[],
    [row(na, REVIEWING_NA_STATUSES), row(ip, REVIEWING_IP_STATUSES)].filter(
      Boolean
    ) as StageGroupRow[],
    [row(na, DECISION_NA_STATUSES)].filter(Boolean) as StageGroupRow[],
    [row(done, CLOSED_STATUSES)].filter(Boolean) as StageGroupRow[]
  ]
  const keys = ["screening", "reviewing", "decision", "closed"]
  return keys.map((key, i) => ({ key, rows: rows[i] }))
}

function totalForRow(row: unknown): number {
  return (pub(row).submission_status_counts ?? []).reduce(
    (sum, c) => sum + c.count,
    0
  )
}

function dashboardFilterTo(publicationId: string, statuses: readonly string[]) {
  // `scroll=table` is a hand-off flag for the submissions tab: after
  // it loads it smooth-scrolls past the workflow diagram to the
  // table below, then strips the flag from the URL so a manual
  // refresh doesn't keep re-triggering.
  return {
    name: "manage:publication:submissions" as const,
    params: { id: publicationId },
    query: { status: `[${statuses.join(",")}]`, scroll: "table" }
  }
}

// Column definitions for table view. Counts are rolled up per row
// from submission_status_counts against the shared statusCategories
// so the totals here match what the publication's own dashboard
// shows. Each count is a router-link into the filtered dashboard.
// Header icons: reuse the category styles so the labels in the
// second header row carry the same visual cue as the dashboard
// chips (flag for Needs Action, hourglass for In Progress, done
// icon for Closed).
const needsActionStyle = statusCategories.find((c) => c.key === "needs_action")
const inProgressStyle = statusCategories.find((c) => c.key === "in_progress")
const completedStyle = statusCategories.find((c) => c.key === "completed")

// Status splits per (stage x category) sub-column. Each column is
// both "in stage X" and "in category Y" — the intersection picks
// only the statuses that belong to both, which keeps the Manage
// table aligned with both the workflow diagram (stages) and the
// publication dashboard's category cards (categories).
const CLOSED_STATUSES =
  statusCategories.find((c) => c.key === "completed")?.statuses ?? []
const SCREENING_NA_STATUSES = ["INITIALLY_SUBMITTED", "RESUBMITTED"] as const
const REVIEWING_NA_STATUSES = ["AWAITING_REVIEW"] as const
const REVIEWING_IP_STATUSES = ["UNDER_REVIEW"] as const
const DECISION_NA_STATUSES = ["AWAITING_DECISION"] as const

function countStatuses(
  row: Record<string, unknown>,
  statuses: readonly string[]
): number {
  const p = pub(row)
  const counts = new Map<string, number>()
  for (const c of p.submission_status_counts ?? []) {
    counts.set(c.status, c.count)
  }
  return statuses.reduce((sum, s) => sum + (counts.get(s) ?? 0), 0)
}

const columns: QueryTableColumn[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: "publication.name",
    component: PublicationNameCell,
    sortable: true,
    linkTo: (row) => ({
      name: "manage:publication:dashboard",
      params: { id: pub(row).id }
    }),
    label: "publication.manage.dashboard.headers.name"
  },
  {
    name: "screening_na",
    align: "right",
    field: (row) => countStatuses(row, SCREENING_NA_STATUSES),
    component: CategoryCountCell,
    category: "needs_action",
    // `stage-start` draws a vertical divider on the left edge of this
    // cell so the 4-stage groupings (Screening / Reviewing / Decision /
    // Closed) read as distinct bands rather than one continuous strip
    // of count cells.
    classes: "stage-start",
    linkTo: (row) => dashboardFilterTo(pub(row).id, SCREENING_NA_STATUSES),
    label: "publication.dashboard.categories.needs_action"
  },
  {
    name: "reviewing_na",
    align: "right",
    field: (row) => countStatuses(row, REVIEWING_NA_STATUSES),
    component: CategoryCountCell,
    category: "needs_action",
    classes: "stage-start",
    linkTo: (row) => dashboardFilterTo(pub(row).id, REVIEWING_NA_STATUSES),
    label: "publication.dashboard.categories.needs_action"
  },
  {
    name: "reviewing_ip",
    align: "right",
    field: (row) => countStatuses(row, REVIEWING_IP_STATUSES),
    component: CategoryCountCell,
    category: "in_progress",
    linkTo: (row) => dashboardFilterTo(pub(row).id, REVIEWING_IP_STATUSES),
    label: "publication.dashboard.categories.in_progress"
  },
  {
    name: "decision_na",
    align: "right",
    field: (row) => countStatuses(row, DECISION_NA_STATUSES),
    component: CategoryCountCell,
    category: "needs_action",
    classes: "stage-start",
    linkTo: (row) => dashboardFilterTo(pub(row).id, DECISION_NA_STATUSES),
    label: "publication.dashboard.categories.needs_action"
  },
  {
    name: "closed",
    align: "right",
    field: (row) => countStatuses(row, CLOSED_STATUSES),
    component: CategoryCountCell,
    category: "completed",
    classes: "stage-start",
    linkTo: (row) => dashboardFilterTo(pub(row).id, CLOSED_STATUSES),
    label: "publication.manage.dashboard.headers.closed"
  }
]
</script>

<style scoped>
.manage-admin-hint__badge {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.publication-title {
  font-size: 1.1rem;
  line-height: 1.3;
  font-weight: 600;
  text-decoration: none;
  overflow-wrap: anywhere;
  word-break: break-word;
}
.publication-title:hover {
  text-decoration: underline;
}
.role-badge {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  flex: 0 0 auto;
}
.manage-publication-card {
  transition:
    box-shadow 120ms ease,
    border-color 120ms ease;
}
.manage-publication-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-color: rgba(0, 0, 0, 0.2);
}
.body--dark .manage-publication-card:hover {
  box-shadow: 0 2px 8px rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.3);
}
.category-row {
  padding: 4px 16px;
  min-height: 0;
}
.category-count {
  font-variant-numeric: tabular-nums;
  font-size: 1rem;
}
/* Compact colored + patterned chip around a needs_action count.
   Keeps the visual weight on the number without coloring the
   whole row. Pattern overlay painted locally (not gated on the
   app-wide `.a11y-patterns` toggle) for parity with the table's
   CategoryCountCell. */
.category-count-chip {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 3.5rem;
  padding: 6px 18px;
  border-radius: 9999px;
  font-weight: 700;
  font-size: 1rem;
  line-height: 1.3;
  font-variant-numeric: tabular-nums;
  overflow: hidden;
}
/* Neutral variant: same footprint as the highlighted chip so count
   values line up across rows even when only some rows are tinted.
   A subtle background gives the pill a box shape to match the
   highlighted rows without competing for attention. */
.category-count-chip--muted {
  background: rgba(0, 0, 0, 0.05);
  color: rgba(0, 0, 0, 0.7);
}
.body--dark .category-count-chip--muted {
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.7);
}
.category-count-chip-text {
  position: relative;
  z-index: 1;
}
.category-count-chip::after {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}
.category-count-chip.pattern-diagonal::after {
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 5px,
    rgba(255, 255, 255, 0.3) 5px,
    rgba(255, 255, 255, 0.3) 8px
  );
}
.category-count-chip.pattern-zigzag::after {
  background:
    linear-gradient(135deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%) -6px
      0,
    linear-gradient(225deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%) -6px
      0,
    linear-gradient(315deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%),
    linear-gradient(45deg, rgba(255, 255, 255, 0.3) 25%, transparent 25%);
  background-size: 12px 12px;
}
.category-count-chip.pattern-dots::after {
  background: radial-gradient(
    circle,
    rgba(255, 255, 255, 0.35) 2px,
    transparent 2px
  );
  background-size: 10px 10px;
}
.category-count-chip.pattern-crosshatch::after {
  background:
    repeating-linear-gradient(
      45deg,
      transparent,
      transparent 5px,
      rgba(255, 255, 255, 0.22) 5px,
      rgba(255, 255, 255, 0.22) 8px
    ),
    repeating-linear-gradient(
      -45deg,
      transparent,
      transparent 5px,
      rgba(255, 255, 255, 0.22) 5px,
      rgba(255, 255, 255, 0.22) 8px
    );
}
/* Small stage heading above each group of sub-rows — uppercased
   and muted so the actual category rows read as the emphasis. */
.stage-heading {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.55);
}
.body--dark .stage-heading {
  color: rgba(255, 255, 255, 0.72);
}
:deep(.q-table--grid .q-table__top) {
  padding: 0 0 4px 0;
}
:deep(.q-table--grid .q-table__grid-content) {
  border-radius: 4px;
}
/* Vertical divider between stage groups — applied via the
   `stage-start` class on body cells (first column of each stage)
   and on the matching second-row header cells. */
:deep(tbody .stage-start) {
  border-left: 1px solid rgba(0, 0, 0, 0.12);
}
.body--dark :deep(tbody .stage-start) {
  border-left-color: rgba(255, 255, 255, 0.16);
}
/* Header row sits on bg-accent, so the body divider tone would
   disappear — use translucent white instead. */
:deep(thead .stage-start) {
  border-left: 1px solid rgba(255, 255, 255, 0.24);
}
/* Stage group header row: ungrouped spacer cells are transparent
   (no bg-accent) so the row reads as "label goes here, nothing
   here" rather than a full-width bar. Matches the Review Team
   table's two-row header treatment. */
.stage-group-header .stage-group-label {
  font-weight: 600;
  letter-spacing: 0.02em;
}
.stage-group-header .stage-group-spacer {
  background: transparent;
  border: none;
  padding: 0;
}
</style>
