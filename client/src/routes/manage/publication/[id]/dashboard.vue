<template>
  <div
    class="publication-dashboard"
    :class="$q.screen.lt.sm ? 'q-px-sm' : 'q-px-lg'"
  >
    <div class="row items-center q-gutter-sm q-mb-sm q-pt-md">
      <h2 class="q-my-none col">
        {{ publication?.name }} {{ $t("publication.dashboard.heading") }}
      </h2>
      <q-btn
        data-cy="manage_review_team_button"
        icon="groups"
        color="primary"
        :to="{ name: 'manage:publication:team', params: { id } }"
      >
        {{ $t("publication.manage.review_team.heading") }}
      </q-btn>
    </div>

    <div v-if="!publication" class="q-pa-lg">{{ $t("loading") }}</div>
    <template v-else>
      <!-- Workflow diagram: a single panel with three stacked lanes.
           The expected path through the system sits in the middle,
           with the loop-back (inactive) lane above and the terminal
           (closed) lane below, so a reader can see divergences on
           either side of the happy path at a glance. -->
      <div class="status-flow-diagram q-mb-md">
        <!-- Top lane: "inactive" — sent back to the author, waiting
             to re-enter the active pipeline once the author acts.
             Skips rendering when the lane has no statuses. -->
        <div v-if="authorLane.length" class="status-lane">
          <div class="status-lane-label">
            {{ $t("publication.dashboard.lanes.with_author") }}
          </div>
          <div class="status-lane-row">
            <div
              v-for="status in authorLane"
              :key="`author-${status}`"
              class="status-flow-chip"
              :style="`border-color: var(--q-${styleFor(status).color})`"
            >
              <span
                :class="[
                  'status-flow-swatch',
                  `bg-${styleFor(status).color}`,
                  styleFor(status).textClass,
                  styleFor(status).pattern
                ]"
              >
                <q-icon
                  :name="styleFor(status).icon"
                  size="xs"
                  class="pattern-text-mask"
                />
              </span>
              <span class="col column">
                <span class="status-flow-label">
                  {{ $t(`submission.status.${status}`) }}
                </span>
                <span class="status-flow-count text-weight-bold">
                  {{ statusCountMap.get(status) ?? 0 }}
                </span>
              </span>
            </div>
            <q-icon
              name="subdirectory_arrow_left"
              size="sm"
              class="lane-arrow text-grey-6"
              aria-hidden="true"
            />
            <span class="status-lane-hint text-caption text-grey-7">
              {{ $t("publication.dashboard.lanes.loops_back") }}
            </span>
          </div>
        </div>

        <!-- Middle lane: the expected path through the system. A
             cell can be a single chip or a stack of chips sharing a
             column — the intake cell stacks RESUBMITTED above
             INITIALLY_SUBMITTED so both read as entry points. -->
        <div class="status-lane">
          <div class="status-lane-row status-lane-row--active">
            <template v-for="(cell, i) in activeLane" :key="`active-${i}`">
              <q-icon
                v-if="i > 0"
                name="arrow_forward"
                size="sm"
                class="lane-arrow text-grey-6"
                aria-hidden="true"
              />
              <div v-if="isStack(cell)" class="status-lane-stack">
                <div v-if="cell.label" class="stage-label">
                  {{ $t(`publication.dashboard.stages.${cell.label}`) }}
                </div>
                <div
                  v-for="status in cell.stack"
                  :key="status"
                  class="status-flow-chip"
                  :style="`border-color: var(--q-${styleFor(status).color})`"
                >
                  <span
                    :class="[
                      'status-flow-swatch',
                      `bg-${styleFor(status).color}`,
                      styleFor(status).textClass,
                      styleFor(status).pattern
                    ]"
                  >
                    <q-icon
                      :name="styleFor(status).icon"
                      size="xs"
                      class="pattern-text-mask"
                    />
                  </span>
                  <span class="col column">
                    <span class="status-flow-label">
                      {{ $t(`submission.status.${status}`) }}
                    </span>
                    <span class="status-flow-count text-weight-bold">
                      {{ statusCountMap.get(status) ?? 0 }}
                    </span>
                  </span>
                </div>
              </div>
              <div v-else-if="isCombine(cell)" class="status-lane-stack">
                <div v-if="cell.label" class="stage-label">
                  {{ $t(`publication.dashboard.stages.${cell.label}`) }}
                </div>
                <div
                  class="status-flow-chip"
                  :style="`border-color: var(--q-${styleFor(cell.combine[0]).color})`"
                >
                  <span
                    :class="[
                      'status-flow-swatch',
                      `bg-${styleFor(cell.combine[0]).color}`,
                      styleFor(cell.combine[0]).textClass,
                      styleFor(cell.combine[0]).pattern
                    ]"
                  >
                    <q-icon
                      :name="styleFor(cell.combine[0]).icon"
                      size="xs"
                      class="pattern-text-mask"
                    />
                  </span>
                  <span class="col column">
                    <span class="status-flow-label">
                      {{ $t(`submission.status.${cell.combine[0]}`) }}
                    </span>
                    <span
                      class="row items-center no-wrap q-gutter-xs"
                      style="flex-wrap: wrap"
                    >
                      <span class="status-flow-count text-weight-bold">
                        {{ combineTotal(cell, statusCountMap) }}
                      </span>
                      <q-badge
                        v-for="extra in cell.combine.slice(1)"
                        :key="extra"
                        outline
                        :color="styleFor(extra).color"
                        class="combine-extra-badge"
                        :title="$t(`submission.status.${extra}`)"
                        :aria-label="`${
                          statusCountMap.get(extra) ?? 0
                        } ${$t(`submission.status.${extra}`)}`"
                      >
                        +{{ statusCountMap.get(extra) ?? 0 }}
                        {{ $t(`submission.status.${extra}`) }}
                      </q-badge>
                    </span>
                  </span>
                </div>
              </div>
              <div
                v-else
                class="status-flow-chip"
                :style="`border-color: var(--q-${styleFor(cell).color})`"
              >
                <span
                  :class="[
                    'status-flow-swatch',
                    `bg-${styleFor(cell).color}`,
                    styleFor(cell).textClass,
                    styleFor(cell).pattern
                  ]"
                >
                  <q-icon
                    :name="styleFor(cell).icon"
                    size="xs"
                    class="pattern-text-mask"
                  />
                </span>
                <span class="col column">
                  <span class="status-flow-label">
                    {{ $t(`submission.status.${cell}`) }}
                  </span>
                  <span class="status-flow-count text-weight-bold">
                    {{ statusCountMap.get(cell) ?? 0 }}
                  </span>
                </span>
              </div>
            </template>
          </div>
        </div>

        <!-- Bottom lane: terminal states. No arrows — endpoints. -->
        <div class="status-lane">
          <div class="status-lane-label">
            {{ $t("publication.dashboard.lanes.closed") }}
          </div>
          <div class="status-lane-row">
            <div
              v-for="status in closedLane"
              :key="`closed-${status}`"
              class="status-flow-chip"
              :style="`border-color: var(--q-${styleFor(status).color})`"
            >
              <span
                :class="[
                  'status-flow-swatch',
                  `bg-${styleFor(status).color}`,
                  styleFor(status).textClass,
                  styleFor(status).pattern
                ]"
              >
                <q-icon
                  :name="styleFor(status).icon"
                  size="xs"
                  class="pattern-text-mask"
                />
              </span>
              <span class="col column">
                <span class="status-flow-label">
                  {{ $t(`submission.status.${status}`) }}
                </span>
                <span class="status-flow-count text-weight-bold">
                  {{ statusCountMap.get(status) ?? 0 }}
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Overall category counts — display only, no filter UI here;
           filter controls live on the Submissions tab. -->
      <div class="row q-col-gutter-md q-mb-md items-stretch">
        <div
          v-for="category in categories"
          :key="category.key"
          class="col-xs-6 col-sm-6 col-md-3 column"
        >
          <q-card class="col">
            <q-card-section
              :class="`bg-${category.color} ${category.textClass} ${category.pattern}`"
            >
              <template v-if="$q.screen.xs">
                <div class="column items-center pattern-text-mask text-center">
                  <div
                    class="text-weight-bold q-mb-xs"
                    style="font-size: 2rem; line-height: 1"
                  >
                    {{ category.total }}
                  </div>
                  <div class="row items-center no-wrap">
                    <q-icon :name="category.icon" size="sm" class="q-mr-sm" />
                    <span
                      class="text-weight-medium"
                      style="font-size: 0.9rem; line-height: 1.3"
                    >
                      {{
                        $t(`publication.dashboard.categories.${category.key}`)
                      }}
                    </span>
                  </div>
                </div>
              </template>
              <template v-else>
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
              </template>
            </q-card-section>
            <q-card-section class="q-py-sm">
              <div
                v-for="item in category.items"
                :key="item.status"
                class="row items-center q-py-xs status-row"
              >
                <span class="col text-body2 text-grey-8 ellipsis">
                  {{ $t(`submission.status.${item.status}`) }}
                </span>
                <span class="text-body2 text-weight-medium">
                  {{ item.count }}
                </span>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Dashboard tabs: Submissions + Submitters. Detail pages
           (submitter detail, team member detail) render outside
           this layout. -->
      <q-tabs
        :model-value="activeTab"
        active-color="primary"
        indicator-color="primary"
        align="left"
        class="q-mb-md"
        dense
        no-caps
      >
        <q-route-tab
          name="submissions"
          :label="$t('publication.dashboard.tabs.submissions')"
          :to="{ name: 'manage:publication:submissions', params: { id } }"
        />
        <q-route-tab
          name="submitters"
          :label="$t('publication.dashboard.tabs.submitters')"
          :to="{ name: 'manage:publication:submitters', params: { id } }"
        />
      </q-tabs>

      <router-view :id="id" />
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
      submission_status_counts {
        status
        count
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import { useRoute } from "vue-router"
import {
  statusCategories,
  statusStyleMap,
  type StatusCategoryDef
} from "src/pages/Publication/components/statusCategories"
import { GetPublicationDashboardDocument } from "src/graphql/generated/graphql"

// Workflow lanes. DRAFT is intentionally excluded — drafts are
// private to the author and never appear on the publication
// dashboard.
//
// Active lane is sequential (arrows between chips). Author lane is
// where a submission parks when sent back for revision or
// resubmission before re-entering the active lane. Closed lane is
// terminal states with no outgoing transitions.
// Each cell in the active pipeline is a single status, a stack of
// statuses that share a column, or a combine cell where multiple
// related statuses roll up into one chip whose count is the sum
// and whose secondary statuses render as small badges.
type StackCell = { label?: string; stack: readonly string[] }
type CombineCell = { label?: string; combine: readonly string[] }
type LaneCell = string | StackCell | CombineCell

const activeLane: readonly LaneCell[] = [
  // Intake: INITIALLY_SUBMITTED and RESUBMITTED share a chip since
  // both are new work landing on the coordinator's desk. The chip
  // shows the total count; a badge surfaces how many of those are
  // resubmissions specifically.
  { label: "intake", combine: ["INITIALLY_SUBMITTED", "RESUBMITTED"] },
  { label: "screening", stack: ["AWAITING_REVIEW", "RESUBMISSION_REQUESTED"] },
  { label: "reviewing", stack: ["UNDER_REVIEW", "AWAITING_DECISION"] },
  { label: "decision", stack: ["REVISION_REQUESTED", "ACCEPTED_AS_FINAL"] }
]

// With-author lane is empty now that every request-type status has
// moved into the active pipeline — kept as a hook in case we
// reintroduce author-side states later.
const authorLane = [] as readonly string[]

function isStack(cell: LaneCell): cell is StackCell {
  return typeof cell === "object" && "stack" in cell
}
function isCombine(cell: LaneCell): cell is CombineCell {
  return typeof cell === "object" && "combine" in cell
}
function combineTotal(cell: CombineCell, counts: Map<string, number>): number {
  return cell.combine.reduce(
    (sum, status) => sum + (counts.get(status) ?? 0),
    0
  )
}
const closedLane = ["REJECTED", "EXPIRED", "ARCHIVED", "DELETED"] as const

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

definePage({
  name: "manage:publication:dashboard",
  props: true
})

interface Props {
  id: string
}
const props = defineProps<Props>()

const $q = useQuasar()
const route = useRoute()

// Highlight the active tab based on the currently-matched child route.
const activeTab = computed(() => {
  const name = route.name?.toString() ?? ""
  if (name.endsWith(":submitters")) return "submitters"
  return "submissions"
})

const { result } = useQuery(GetPublicationDashboardDocument, { id: props.id })
const publication = computed(() => result.value?.publication ?? null)

interface StatusCategoryItem {
  status: string
  count: number
}

interface StatusCategory extends StatusCategoryDef {
  total: number
  items: StatusCategoryItem[]
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
      items,
      total: items.reduce((sum, item) => sum + item.count, 0)
    }
  })
)
</script>

<style scoped>
.status-row + .status-row {
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}
.body--dark .status-row + .status-row {
  border-top-color: rgba(255, 255, 255, 0.08);
}
/* Outer panel holding every lane. Horizontal scroll keeps the
   pipeline readable on narrow viewports rather than collapsing
   columns under each other. */
.status-flow-diagram {
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.02);
  padding: 4px 16px;
  overflow-x: auto;
}
.body--dark .status-flow-diagram {
  border-color: rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
}
.status-lane {
  padding: 12px 0;
}
.status-lane + .status-lane {
  border-top: 1px dashed rgba(0, 0, 0, 0.1);
}
.body--dark .status-lane + .status-lane {
  border-top-color: rgba(255, 255, 255, 0.12);
}
.status-lane-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: rgba(0, 0, 0, 0.55);
  margin-bottom: 8px;
}
.body--dark .status-lane-label {
  color: rgba(255, 255, 255, 0.65);
}
/* Generic lane row (non-active lanes): flex, wraps, centered arrows. */
.status-lane-row {
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: 10px;
}
/* Active pipeline row uses a CSS grid so every column is equal
   width and the → arrows sit in their own auto-sized tracks
   between stacks. 4 stacks + 3 arrows = 7 tracks. */
.status-lane-row--active {
  display: grid;
  grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
  gap: 10px 12px;
  flex-wrap: nowrap;
  min-width: max-content;
  align-items: stretch;
}
.status-lane-stack {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 170px;
}
.status-lane-row--active .status-lane-stack {
  /* Stacks inherit their column's width; stretch chips to fill. */
  width: 100%;
}
.status-lane-row--active .status-lane-stack .status-flow-chip {
  width: 100%;
  min-width: 0;
}
/* Small outline badge clipped onto a combined chip to show the
   breakdown of a secondary status (e.g. "+2 Resubmitted"). */
.combine-extra-badge {
  font-size: 0.65rem;
  padding: 2px 6px;
  letter-spacing: 0.02em;
  font-weight: 500;
}
.stage-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
  text-align: center;
  margin-bottom: 4px;
  /* Reserve a consistent height across stacks even if a label is
     missing or shorter, so chips align across columns. */
  min-height: 1rem;
}
.body--dark .stage-label {
  color: rgba(255, 255, 255, 0.72);
}
.lane-arrow {
  align-self: center;
}
/* In the active pipeline row, the arrow sits between stacks in its
   own grid track. Nudge it down so it lines up mid-stack instead
   of at the stage-label baseline. */
.status-lane-row--active .lane-arrow {
  align-self: center;
  padding-top: 1.25rem;
}
.status-lane-hint {
  align-self: center;
}
.status-flow-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px 6px 6px;
  border: 1px solid;
  border-radius: 6px;
  min-width: 150px;
  background: #fff;
}
.body--dark .status-flow-chip {
  background: #1d1d1d;
}
.status-flow-swatch {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  flex: 0 0 auto;
}
.status-flow-label {
  font-size: 0.75rem;
  line-height: 1.1;
  color: inherit;
}
.status-flow-count {
  font-size: 1.1rem;
  line-height: 1.1;
}
</style>
