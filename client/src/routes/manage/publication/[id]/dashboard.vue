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
      <!-- On wider screens the workflow diagram sits beside the
           category summary (8/4 split). On narrow screens they
           stack. The parent `row` carries the bottom margin so
           the diagram's own margin doesn't double up. -->
      <div class="row q-col-gutter-md q-mb-md items-stretch">
        <div class="col-xs-12 col-md-8 column">
          <!-- Workflow diagram: a single panel with three stacked lanes.
           The expected path through the system sits in the middle,
           with the loop-back (inactive) lane above and the terminal
           (closed) lane below, so a reader can see divergences on
           either side of the happy path at a glance. -->
          <div class="status-flow-diagram col">
            <!-- Top lane: "inactive" — sent back to the author, waiting
             to re-enter the active pipeline once the author acts.
             Skips rendering when the lane has no statuses. -->
            <div v-if="authorLane.length" class="status-lane">
              <div class="status-lane-label">
                {{ $t("publication.dashboard.lanes.with_author") }}
              </div>
              <div class="status-lane-row">
                <router-link
                  v-for="status in authorLane"
                  :key="`author-${status}`"
                  class="status-flow-chip"
                  :style="`border-color: var(--q-${styleFor(status).color})`"
                  :to="submissionsFilterTo([status])"
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
                </router-link>
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
                  <div v-if="isColumn(cell)" class="status-lane-stack">
                    <router-link
                      v-if="cell.label"
                      class="stage-label stage-label--link"
                      :to="submissionsFilterTo(columnStatuses(cell))"
                    >
                      {{ $t(`publication.dashboard.stages.${cell.label}`) }}
                    </router-link>
                    <div
                      :class="[
                        'stack-chips',
                        `stack-chips--${cell.align ?? 'top'}`
                      ]"
                    >
                      <template
                        v-for="(chip, ci) in cell.chips"
                        :key="`chip-${i}-${ci}`"
                      >
                        <!-- Combined chip: sums its statuses, shows the
                       primary's identity and sub-counts in parens. -->
                        <router-link
                          v-if="isCombineChip(chip)"
                          class="status-flow-chip"
                          :style="`border-color: var(--q-${styleFor(chip.combine[0]).color})`"
                          :to="submissionsFilterTo(chip.combine)"
                        >
                          <span
                            :class="[
                              'status-flow-swatch',
                              `bg-${styleFor(chip.combine[0]).color}`,
                              styleFor(chip.combine[0]).textClass,
                              styleFor(chip.combine[0]).pattern
                            ]"
                          >
                            <q-icon
                              :name="styleFor(chip.combine[0]).icon"
                              size="xs"
                              class="pattern-text-mask"
                            />
                          </span>
                          <span class="col column">
                            <span class="status-flow-label">
                              {{
                                chip.titleKey
                                  ? $t(chip.titleKey)
                                  : $t(`submission.status.${chip.combine[0]}`)
                              }}
                            </span>
                            <span
                              class="row items-baseline no-wrap q-gutter-xs"
                              style="flex-wrap: wrap"
                            >
                              <span class="status-flow-count text-weight-bold">
                                {{ combineTotal(chip, statusCountMap) }}
                              </span>
                              <!-- Sub-caption is a span, not a router-link,
                               because the outer chip is already an <a>
                               and nested anchors are invalid HTML.
                               Programmatic navigation keeps the
                               per-status filter while letting the
                               outer chip own its "both statuses" link. -->
                              <span
                                v-for="extra in chip.combine.slice(1)"
                                :key="extra"
                                role="link"
                                tabindex="0"
                                class="combine-extra text-caption text-grey-7"
                                :title="$t(`submission.status.${extra}`)"
                                :aria-label="`${
                                  statusCountMap.get(extra) ?? 0
                                } ${$t(`submission.status.${extra}`)}`"
                                @click.stop.prevent="goToSubmissions([extra])"
                                @keydown.enter.stop.prevent="
                                  goToSubmissions([extra])
                                "
                                @keydown.space.stop.prevent="
                                  goToSubmissions([extra])
                                "
                              >
                                ({{ statusCountMap.get(extra) ?? 0 }}
                                {{ $t(`submission.status.${extra}`) }})
                              </span>
                            </span>
                          </span>
                        </router-link>
                        <!-- Plain status chip. -->
                        <router-link
                          v-else
                          class="status-flow-chip"
                          :style="`border-color: var(--q-${styleFor(chip).color})`"
                          :to="submissionsFilterTo([chip])"
                        >
                          <span
                            :class="[
                              'status-flow-swatch',
                              `bg-${styleFor(chip).color}`,
                              styleFor(chip).textClass,
                              styleFor(chip).pattern
                            ]"
                          >
                            <q-icon
                              :name="styleFor(chip).icon"
                              size="xs"
                              class="pattern-text-mask"
                            />
                          </span>
                          <span class="col column">
                            <span class="status-flow-label">
                              {{ $t(`submission.status.${chip}`) }}
                            </span>
                            <span class="status-flow-count text-weight-bold">
                              {{ statusCountMap.get(chip) ?? 0 }}
                            </span>
                          </span>
                        </router-link>
                      </template>
                    </div>
                  </div>
                  <router-link
                    v-else
                    class="status-flow-chip"
                    :style="`border-color: var(--q-${styleFor(cell).color})`"
                    :to="submissionsFilterTo([cell])"
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
                  </router-link>
                </template>
              </div>
            </div>

            <!-- Bottom lane: terminal states. No arrows — endpoints.
             Every chip shares the "completed" category, so a
             single patterned icon sits at the start of the chip
             row (not on each chip). Chips keep a left border
             accent to echo the category color. -->
            <div class="status-lane">
              <router-link
                class="status-lane-label status-lane-label--link"
                :to="submissionsFilterTo(closedLane)"
              >
                {{ $t("publication.dashboard.lanes.closed") }}
              </router-link>
              <div class="status-lane-row">
                <router-link
                  v-if="closedCategory"
                  class="closed-lane-icon-link"
                  :to="submissionsFilterTo(closedLane)"
                  :aria-label="$t('publication.dashboard.lanes.closed')"
                >
                  <span
                    :class="[
                      'closed-lane-swatch',
                      `bg-${closedCategory.color}`,
                      closedCategory.textClass,
                      closedCategory.pattern
                    ]"
                  >
                    <q-icon
                      :name="closedCategory.icon"
                      size="sm"
                      class="pattern-text-mask"
                    />
                  </span>
                </router-link>
                <!-- Collapsed: single aggregate chip summarizing the
                 whole lane. Six separate chips for terminal states
                 makes the diagram noisier than it needs to be most
                 of the time. Click the chevron to expand. -->
                <router-link
                  v-if="!closedLaneExpanded"
                  class="status-flow-chip status-flow-chip--slim closed-lane-total"
                  :to="submissionsFilterTo(closedLane)"
                >
                  <span class="col column">
                    <span class="status-flow-label">
                      {{ $t("publication.dashboard.lanes.closed_total") }}
                    </span>
                    <span class="status-flow-count text-weight-bold">
                      {{ closedLaneTotal }}
                    </span>
                  </span>
                </router-link>
                <template v-else>
                  <router-link
                    v-for="status in closedLane"
                    :key="`closed-${status}`"
                    class="status-flow-chip status-flow-chip--slim"
                    :style="`border-left-color: var(--q-${styleFor(status).color})`"
                    :to="submissionsFilterTo([status])"
                  >
                    <span class="col column">
                      <span class="status-flow-label">
                        {{ $t(`submission.status.${status}`) }}
                      </span>
                      <span class="status-flow-count text-weight-bold">
                        {{ statusCountMap.get(status) ?? 0 }}
                      </span>
                    </span>
                  </router-link>
                </template>
                <q-btn
                  flat
                  dense
                  round
                  :icon="closedLaneExpanded ? 'unfold_less' : 'unfold_more'"
                  class="closed-lane-toggle"
                  :aria-label="
                    closedLaneExpanded
                      ? $t('publication.dashboard.lanes.closed_collapse')
                      : $t('publication.dashboard.lanes.closed_expand')
                  "
                  :title="
                    closedLaneExpanded
                      ? $t('publication.dashboard.lanes.closed_collapse')
                      : $t('publication.dashboard.lanes.closed_expand')
                  "
                  @click="closedLaneExpanded = !closedLaneExpanded"
                />
              </div>
            </div>
          </div>
        </div>
        <!-- Overall category counts. The colored header links to the
             category's full status set; each inner row links to that
             single status so a user can drill straight from "I see
             the number" to "show me those submissions". The row
             shrinks as categories are hidden (see
             HIDDEN_CATEGORY_KEYS) — typically only `needs_action`
             is surfaced here, so the card fills the 4-col side
             column on wider screens. -->
        <div class="col-xs-12 col-md-4 column q-gutter-md">
          <div
            v-for="category in categories"
            :key="category.key"
            class="column"
          >
            <q-card class="col">
              <router-link
                class="category-header-link"
                :class="`bg-${category.color} ${category.textClass} ${category.pattern}`"
                :to="submissionsFilterTo(category.statuses)"
              >
                <q-card-section>
                  <template v-if="$q.screen.xs">
                    <div
                      class="column items-center pattern-text-mask text-center"
                    >
                      <div
                        class="text-weight-bold q-mb-xs"
                        style="font-size: 2rem; line-height: 1"
                      >
                        {{ category.total }}
                      </div>
                      <div class="row items-center no-wrap">
                        <q-icon
                          :name="category.icon"
                          size="sm"
                          class="q-mr-sm"
                        />
                        <span
                          class="text-weight-medium"
                          style="font-size: 0.9rem; line-height: 1.3"
                        >
                          {{
                            $t(
                              `publication.dashboard.categories.${category.key}`
                            )
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
                        {{
                          $t(`publication.dashboard.categories.${category.key}`)
                        }}
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
              </router-link>
              <!-- Per-status rows are a drill-down aid. On small
                 screens the card header already carries the
                 headline number, so hide the row list to keep the
                 dashboard compact. -->
              <q-card-section class="q-py-sm category-items gt-xs">
                <router-link
                  v-for="item in category.items"
                  :key="item.status"
                  class="row items-center q-py-xs status-row status-row--link"
                  :to="submissionsFilterTo([item.status])"
                >
                  <span class="col text-body2 text-grey-8 ellipsis">
                    {{ $t(`submission.status.${item.status}`) }}
                  </span>
                  <span class="text-body2 text-weight-medium">
                    {{ item.count }}
                  </span>
                </router-link>
              </q-card-section>
            </q-card>
          </div>
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
import { computed, ref, watchEffect } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import { useRoute, useRouter } from "vue-router"
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
// A chip in the active pipeline is either a plain status or a
// combined chip where multiple related statuses roll up into one
// chip whose count is the sum and whose non-primary statuses
// render as a parenthetical subset caption.
type CombineSpec = {
  combine: readonly string[]
  // Optional i18n key for the chip's visible title. Falls back to
  // the primary status's label — useful when the sum doesn't map
  // cleanly to any single status (e.g. "Submitted" as an umbrella
  // for Initially Submitted + Resubmitted).
  titleKey?: string
}
type ChipSpec = string | CombineSpec

// A lane cell is either a single status (bare chip) or a labeled
// column that holds one or more chips stacked vertically. Mixing
// plain and combined chips in the same column is allowed. The
// optional `align` controls vertical alignment of the chips within
// the column — useful when column heights differ so the "happy
// path" chip in each column lines up horizontally.
type ColumnCell = {
  label?: string
  chips: readonly ChipSpec[]
  align?: "top" | "center" | "bottom"
}
type LaneCell = string | ColumnCell

const SUBMITTED_COMBINE: CombineSpec = {
  combine: ["INITIALLY_SUBMITTED", "RESUBMITTED"],
  titleKey: "publication.dashboard.combined.submitted"
}

// Vertical alignment is tuned so the happy path reads as a single
// horizontal band: Submitted → Under Review → Awaiting Decision →
// Accepted As Final (on row 3 of the decision column, the natural
// terminal of the happy path).
//
// Author-gated "requested" statuses (RESUBMISSION_REQUESTED,
// REVISION_REQUESTED) used to live in the active pipeline but
// they're closed from the publication's side — the admin can't
// advance them until the author resubmits — so they now live in
// the closed lane alongside the other terminal states.
const activeLane: readonly LaneCell[] = [
  // Every column is bottom-aligned so the chips share a single
  // ground line and the arrows between columns don't have to
  // dodge columns of different heights. The chip on that ground
  // line is the happy-path destination for each stage:
  //   Submitted (screening) → Under Review (reviewing) → Awaiting
  //   Decision (decision). Accepted As Final hangs below Awaiting
  //   Decision as the natural conclusion.
  { label: "screening", align: "bottom", chips: [SUBMITTED_COMBINE] },
  {
    label: "reviewing",
    align: "bottom",
    chips: ["AWAITING_REVIEW", "UNDER_REVIEW"]
  },
  {
    label: "decision",
    align: "bottom",
    chips: ["AWAITING_DECISION", "ACCEPTED_AS_FINAL"]
  }
]

// With-author lane is empty — if we ever need a dedicated "paused /
// awaiting the author" section, it goes here.
const authorLane = [] as readonly string[]

function isColumn(cell: LaneCell): cell is ColumnCell {
  return typeof cell === "object" && "chips" in cell
}
function isCombineChip(chip: ChipSpec): chip is CombineSpec {
  return typeof chip === "object" && "combine" in chip
}
function combineTotal(chip: CombineSpec, counts: Map<string, number>): number {
  return chip.combine.reduce(
    (sum, status) => sum + (counts.get(status) ?? 0),
    0
  )
}
function chipStatuses(chip: ChipSpec): readonly string[] {
  return isCombineChip(chip) ? chip.combine : [chip]
}
function columnStatuses(cell: ColumnCell): string[] {
  return cell.chips.flatMap((c) => [...chipStatuses(c)])
}
// ACCEPTED_AS_FINAL is drawn in the active lane as the conclusion
// of the happy path, not here. This lane collects everything else
// the dashboard admin can't act on: the two "waiting on the
// author" states (resubmission/revision requested) and the
// genuinely terminal ones (rejected, expired, archived, deleted).
const closedLane = [
  "RESUBMISSION_REQUESTED",
  "REVISION_REQUESTED",
  "REJECTED",
  "EXPIRED",
  "ARCHIVED",
  "DELETED"
] as const

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

// Build a router-link target for the submissions tab with the
// `status` query param set to the filter format used there:
// bracketed, comma-separated (e.g. `[INITIALLY_SUBMITTED,RESUBMITTED]`).
function submissionsFilterTo(statuses: readonly string[]) {
  return {
    name: "manage:publication:submissions" as const,
    params: { id: props.id },
    query: { status: `[${statuses.join(",")}]` }
  }
}

const $q = useQuasar()
const route = useRoute()
const router = useRouter()

function goToSubmissions(statuses: readonly string[]) {
  void router.push(submissionsFilterTo(statuses))
}

// If we land on the dashboard parent route with no child selected
// (route name === the parent's own name), drop the user on the
// Submissions tab — the most common first action. Using a
// watchEffect rather than definePage's `redirect` because
// plugin-vue-router forbids that property on routes that also
// have a component.
watchEffect(() => {
  if (route.name === "manage:publication:dashboard") {
    void router.replace({
      name: "manage:publication:submissions",
      params: { id: props.id }
    })
  }
})

// The closed lane header shows one icon for the whole lane (all
// five terminal statuses share the "completed" category).
const closedCategory = statusCategories.find((c) => c.key === "completed")

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

// Closed lane is collapsed by default — a single total chip — and
// expands into per-status chips on demand. This keeps the typical
// dashboard quieter without hiding the breakdown.
const closedLaneExpanded = ref(false)
const closedLaneTotal = computed(() =>
  closedLane.reduce((sum, s) => sum + (statusCountMap.value.get(s) ?? 0), 0)
)

// Categories we intentionally hide from the summary row because
// the pipeline diagram above already surfaces their counts:
//   - `in_progress` only holds UNDER_REVIEW, shown as its own chip.
//   - `completed` is the closed lane, which already enumerates
//     every terminal + author-gated status with a per-status count.
const HIDDEN_CATEGORY_KEYS = new Set<string>(["in_progress", "completed"])

const categories = computed<StatusCategory[]>(() =>
  statusCategories
    .filter((cat) => !HIDDEN_CATEGORY_KEYS.has(cat.key))
    .map((cat) => {
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
.status-row--link {
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  margin: 0 -16px;
  padding-left: 16px;
  padding-right: 16px;
}
.status-row--link:hover {
  background: rgba(0, 0, 0, 0.04);
}
.body--dark .status-row--link:hover {
  background: rgba(255, 255, 255, 0.06);
}
/* The whole colored card header is a link to the category's status
   set. Reset anchor defaults and keep the card-section padding. */
.category-header-link {
  display: block;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  transition: filter 120ms ease;
}
.category-header-link:hover {
  filter: brightness(1.05);
}
.body--dark .category-header-link:hover {
  filter: brightness(1.15);
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
   between stacks. 3 stacks + 2 arrows = 5 tracks. */
.status-lane-row--active {
  display: grid;
  grid-template-columns: 1fr auto 1fr auto 1fr;
  gap: 10px 12px;
  flex-wrap: nowrap;
  min-width: max-content;
  align-items: stretch;
}
/* On narrow viewports the horizontal pipeline doesn't fit without
   scrolling, so collapse to a single stacked column. Arrows
   rotate 90° so they still read as "the next step is below". */
@media (max-width: 700px) {
  .status-lane-row--active {
    grid-template-columns: 1fr;
    min-width: 0;
    justify-items: stretch;
  }
  .status-lane-row--active .lane-arrow {
    align-self: center;
    justify-self: center;
    padding-bottom: 0;
    transform: rotate(90deg);
  }
}
.status-lane-stack {
  display: flex;
  flex-direction: column;
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
/* Chip container inside a stack. Takes the remaining vertical
   space below the stage label and lays chips out vertically with
   configurable alignment so the happy path reads as a single
   horizontal band across columns of different depths. */
.stack-chips {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1 1 auto;
}
.stack-chips--top {
  justify-content: flex-start;
}
.stack-chips--center {
  justify-content: center;
}
.stack-chips--bottom {
  justify-content: flex-end;
}
/* Small parenthetical caption clipped onto a combined chip to
   break down a secondary status (e.g. "(2 Resubmitted)"). Reads
   as a subset of the total rather than an additive "+". */
.combine-extra {
  font-size: 0.75rem;
  line-height: 1.1;
  white-space: nowrap;
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
   own grid track. All columns are bottom-aligned so chips share a
   single ground line; the arrows sit on that line too (with just
   enough vertical padding to clear the chip's bottom edge). */
.status-lane-row--active .lane-arrow {
  align-self: end;
  padding-bottom: 0.5rem;
}
.status-lane-hint {
  align-self: center;
}
/* One patterned swatch at the head of the closed-lane chip row
   carries the category identity so individual chips can stay
   clean. Sized to match the chips' vertical rhythm. */
.closed-lane-icon-link {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
  align-self: stretch;
}
.closed-lane-icon-link:hover .closed-lane-swatch {
  filter: brightness(1.08);
}
.closed-lane-swatch {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 6px;
  flex: 0 0 auto;
  align-self: center;
}
/* Slim chips used in the closed lane: no patterned swatch, just a
   left-edge color accent that still tells the reader which category
   the chip belongs to. */
.status-flow-chip--slim {
  gap: 0;
  padding: 6px 10px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-left-width: 4px;
  min-width: 130px;
}
.body--dark .status-flow-chip--slim {
  border-color: rgba(255, 255, 255, 0.16);
}
/* The aggregate "All closed" chip uses the closed category's color
   on its left edge so it still reads as part of the lane even
   without individual statuses. */
.closed-lane-total {
  border-left-color: var(--q-blue-grey-7, #455a64) !important;
}
.closed-lane-toggle {
  align-self: center;
  color: rgba(0, 0, 0, 0.6);
}
.body--dark .closed-lane-toggle {
  color: rgba(255, 255, 255, 0.7);
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
  /* Router-links default to underlined/colored — strip that so
     the chip reads as a card and relies on its own border/shadow
     affordance. */
  text-decoration: none;
  color: inherit;
  transition:
    box-shadow 120ms ease,
    transform 120ms ease;
  cursor: pointer;
}
.status-flow-chip:hover {
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
}
.body--dark .status-flow-chip {
  background: #1d1d1d;
}
.body--dark .status-flow-chip:hover {
  box-shadow: 0 1px 4px rgba(255, 255, 255, 0.18);
}
.stage-label--link,
.status-lane-label--link {
  display: block;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}
.stage-label--link:hover,
.status-lane-label--link:hover {
  color: var(--q-primary);
}
.combine-extra {
  /* Sub-caption router-links inside a combined chip. Keep visually
     subtle so they blend with the caption text but remain clickable. */
  text-decoration: none;
  color: inherit;
}
.combine-extra:hover {
  text-decoration: underline;
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

/* Phone-width condensed layout. Placed after the base chip rules
   above so these overrides win at equal specificity — Reviewing
   has three chips and Decision has two; stacking them vertically
   ate a lot of screen height at phone width. */
@media (max-width: 700px) {
  .stack-chips {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 6px;
  }
  .status-lane-row--active .status-lane-stack .status-flow-chip {
    flex: 0 0 calc(50% - 3px);
    width: calc(50% - 3px);
    min-width: 0;
  }
  .status-flow-chip--slim {
    min-width: 0;
    flex: 0 0 calc(50% - 3px);
  }

  /* Tighten up the vertical rhythm so the whole diagram fits
     closer to the fold on a phone. Biggest wins: lane padding,
     the active-pipeline row gap (which sits between stages and
     their arrows), and the rotated arrow's footprint. */
  .status-lane {
    padding: 6px 0;
  }
  .status-lane-label {
    margin-bottom: 4px;
  }
  .status-lane-row--active {
    gap: 4px 12px;
  }
  /* The rotated arrow between stages doesn't add much on a phone —
     the stacked-under-each-other layout already reads as "next
     step below". Keep it for continuity with desktop but shrink
     it dramatically so it doesn't push the diagram below the fold. */
  .status-lane-row--active .lane-arrow {
    font-size: 16px !important;
    opacity: 0.6;
    padding: 0;
    margin: 0;
  }
  .stage-label {
    margin-bottom: 2px;
    min-height: 0;
  }
  .status-flow-chip {
    padding: 4px 8px 4px 4px;
  }
}
</style>
