<template>
  <div v-if="hasAnything" class="status-bar-wrapper">
    <!-- Top-level heading + total + clear-filter affordance. -->
    <div class="status-bar-heading row items-baseline q-gutter-x-sm">
      <span class="status-bar-title">
        {{ $t("publication.manage.user_detail.submissions_by_status") }}
      </span>
      <span class="status-bar-total text-caption text-grey-7">
        {{ $t("publication.manage.user_detail.submissions", { n: total }) }}
      </span>
      <q-space />
      <q-btn
        v-if="filteredStatus"
        flat
        dense
        size="sm"
        icon="close"
        no-caps
        :label="$t('publication.manage.user_detail.clear_filter')"
        class="status-bar-clear"
        :to="clearLink"
      />
    </div>

    <!-- Active bar: everything still moving through the pipeline
         (needs action + in progress). -->
    <template v-if="activeSegments.length">
      <div class="status-bar-subtitle q-mt-sm">
        {{ $t("publication.manage.user_detail.active") }}
        <span class="status-bar-subtitle-count">{{ activeTotal }}</span>
      </div>
      <div
        class="status-bar-track q-mt-xs"
        role="list"
        :aria-label="$t('publication.manage.user_detail.active')"
      >
        <router-link
          v-for="segment in activeSegments"
          :key="segment.status"
          role="listitem"
          class="status-bar-segment"
          :class="{ 'is-active': isActive(segment.status) }"
          :style="segmentStyle(segment, activeTotal)"
          :to="linkFor(segment.status)"
          :aria-label="segmentAria(segment)"
        >
          <q-tooltip :delay="200" anchor="top middle" self="bottom middle">
            {{ segmentAria(segment) }}
          </q-tooltip>
        </router-link>
      </div>
      <ul class="status-bar-legend q-mt-xs q-mb-none q-pl-none">
        <li
          v-for="segment in activeSegments"
          :key="`active-legend-${segment.status}`"
        >
          <router-link
            :to="linkFor(segment.status)"
            class="status-bar-legend-item"
            :class="{ 'is-active': isActive(segment.status) }"
            :aria-label="segmentAria(segment)"
          >
            <span
              class="status-bar-legend-swatch"
              :style="`background-color: var(--q-${styleFor(segment.status).color})`"
              aria-hidden="true"
            />
            <span class="status-bar-legend-label">
              {{ $t(`submission.status.${segment.status}`) }}
            </span>
            <span class="status-bar-legend-count">
              {{ segment.count }}
            </span>
          </router-link>
        </li>
      </ul>
    </template>

    <!-- Closed section: collapsed to a one-line "Closed: N" +
         expand toggle by default, since the terminal statuses
         don't usually need the same visual weight as the active
         pipeline. Expanding reveals the per-status bar + legend. -->
    <template v-if="closedSegments.length">
      <div class="status-bar-subtitle q-mt-md row items-center no-wrap">
        <span>{{ $t("publication.manage.user_detail.closed") }}</span>
        <router-link
          :to="closedLinkFor(closedStatuses)"
          :class="[
            'closed-total-link q-ml-xs',
            closedFilterActive ? 'is-active' : ''
          ]"
          :aria-label="`${$t('publication.manage.user_detail.closed')}: ${closedTotal}`"
        >
          {{ closedTotal }}
        </router-link>
        <q-btn
          flat
          dense
          round
          size="sm"
          :icon="closedExpanded ? 'unfold_less' : 'unfold_more'"
          :aria-label="
            closedExpanded
              ? $t('publication.dashboard.lanes.closed_collapse')
              : $t('publication.dashboard.lanes.closed_expand')
          "
          :title="
            closedExpanded
              ? $t('publication.dashboard.lanes.closed_collapse')
              : $t('publication.dashboard.lanes.closed_expand')
          "
          class="q-ml-xs"
          @click="closedExpanded = !closedExpanded"
        />
      </div>
      <template v-if="closedExpanded">
        <ul class="status-bar-legend q-mt-xs q-mb-none q-pl-none">
          <li
            v-for="segment in closedSegments"
            :key="`closed-legend-${segment.status}`"
          >
            <router-link
              :to="linkFor(segment.status)"
              class="status-bar-legend-item"
              :class="{ 'is-active': isActive(segment.status) }"
              :aria-label="segmentAria(segment)"
            >
              <span
                class="status-bar-legend-swatch"
                :style="`background-color: var(--q-${styleFor(segment.status).color})`"
                aria-hidden="true"
              />
              <span class="status-bar-legend-label">
                {{ $t(`submission.status.${segment.status}`) }}
              </span>
              <span class="status-bar-legend-count">
                {{ segment.count }}
              </span>
            </router-link>
          </li>
        </ul>
      </template>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"
import type { RouteLocationRaw } from "vue-router"
import {
  statusCategories,
  statusStyleMap
} from "src/pages/Publication/components/statusCategories"

interface StatusCount {
  status: string
  count: number
}

interface Props {
  counts: ReadonlyArray<StatusCount>
  /** Build a filtered-to-a-single-status route. */
  linkFor: (status: string) => RouteLocationRaw
  /** Build a filtered-to-many-statuses route (used for the
   *  collapsed "all closed" aggregate chip). */
  closedLinkFor: (statuses: readonly string[]) => RouteLocationRaw
  /** Route to clear the filter (link target, not a handler). */
  clearLink: RouteLocationRaw
  /** The currently-active filtered status, if any. */
  filteredStatus?: string | null
}
const props = defineProps<Props>()
const { t } = useI18n()

// Closed lane starts collapsed — same default as the dashboard's
// closed-lane chip row, so the two surfaces behave the same way.
const closedExpanded = ref(false)

// Bucket statuses into "active" (still moving through the pipeline
// — needs_action + in_progress) and "closed" (the completed
// category: terminal states + the author-gated pauses). Keeps the
// two bars fed from a single counts prop, and matches the
// dashboard's lane split exactly.
const CLOSED_CATEGORY = "completed"
const closedCategory = statusCategories.find((c) => c.key === CLOSED_CATEGORY)
const closedStatuses: readonly string[] = closedCategory?.statuses ?? []
const closedStatusSet = new Set<string>(closedStatuses)

const positiveSegments = computed(() =>
  [...props.counts]
    .filter((c) => c.count > 0)
    .sort((a, b) => sortKey(a.status) - sortKey(b.status))
)

const activeSegments = computed(() =>
  positiveSegments.value.filter((s) => !closedStatusSet.has(s.status))
)
const closedSegments = computed(() =>
  positiveSegments.value.filter((s) => closedStatusSet.has(s.status))
)

const activeTotal = computed(() =>
  activeSegments.value.reduce((sum, s) => sum + s.count, 0)
)
const closedTotal = computed(() =>
  closedSegments.value.reduce((sum, s) => sum + s.count, 0)
)
const total = computed(() => activeTotal.value + closedTotal.value)

const hasAnything = computed(() => positiveSegments.value.length > 0)

// The closed-lane aggregate chip picks up the "filtered" state
// whenever the active filter is on a closed status — lets the
// viewer see that the filter lives inside the collapsed bar
// without forcing them to expand it.
const closedFilterActive = computed(
  () =>
    props.filteredStatus != null && closedStatusSet.has(props.filteredStatus)
)

// Build a status → rank map from the dashboard's category order so
// statuses in the same category cluster together in the bar: all
// "needs attention" first, "in progress" next, and the closed /
// terminal statuses last. Keeps a single source of truth alongside
// the workflow diagram.
const statusOrder: ReadonlyMap<string, number> = (() => {
  const map = new Map<string, number>()
  let i = 0
  for (const cat of statusCategories) {
    for (const s of cat.statuses) {
      if (!map.has(s)) map.set(s, i++)
    }
  }
  return map
})()

function sortKey(status: string): number {
  return statusOrder.get(status) ?? Number.MAX_SAFE_INTEGER
}

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

function segmentStyle(segment: StatusCount, denominator: number) {
  const color = styleFor(segment.status).color
  const flex = denominator ? segment.count / denominator : 0
  return {
    flex: `${flex} 0 0`,
    // Ensure even a tiny slice stays visible.
    minWidth: "8px",
    backgroundColor: `var(--q-${color})`
  }
}

function segmentAria(segment: StatusCount): string {
  return `${t(`submission.status.${segment.status}`)}: ${segment.count}`
}

function isActive(status: string): boolean {
  return props.filteredStatus === status
}
</script>

<style scoped>
.status-bar {
  width: 100%;
}
.status-bar-heading {
  display: flex;
  align-items: baseline;
  flex-wrap: wrap;
}
.status-bar-title {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
}
.body--dark .status-bar-title {
  color: rgba(255, 255, 255, 0.72);
}
/* Per-bar subtitle ("Active" / "Closed") — smaller than the top
   title but still framed as a heading so the viewer can tell the
   two bars apart. */
.status-bar-subtitle {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  gap: 6px;
}
.body--dark .status-bar-subtitle {
  color: rgba(255, 255, 255, 0.72);
}
.status-bar-subtitle-count {
  color: inherit;
  font-variant-numeric: tabular-nums;
  opacity: 0.75;
}
/* Collapsed-state number next to the "Closed" heading. Styled as
   a link so clicking it jumps to the Submissions tab filtered to
   every closed status — same destination the expanded bar's
   aggregate used to go. */
.closed-total-link {
  color: inherit;
  text-decoration: none;
  font-variant-numeric: tabular-nums;
  font-weight: 600;
}
.closed-total-link:hover {
  text-decoration: underline;
}
.closed-total-link.is-active {
  color: var(--q-primary);
}
/* Inline color → status legend. Flat, non-interactive; the bar
   above is the single click surface. Active status gets a subtle
   ring so it reads as "this one's filtered". */
.status-bar-legend {
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 4px 14px;
  font-size: 0.8rem;
  line-height: 1.3;
}
.status-bar-legend-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  padding: 2px 4px;
  margin: -2px -4px;
  border-radius: 4px;
}
.status-bar-legend-item:hover {
  background: rgba(0, 0, 0, 0.05);
}
.body--dark .status-bar-legend-item:hover {
  background: rgba(255, 255, 255, 0.06);
}
.status-bar-legend-item.is-active {
  font-weight: 600;
}
.status-bar-legend-swatch {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 2px;
  flex: 0 0 auto;
}
.status-bar-legend-count {
  color: rgba(0, 0, 0, 0.55);
  font-variant-numeric: tabular-nums;
}
.body--dark .status-bar-legend-count {
  color: rgba(255, 255, 255, 0.6);
}
.status-bar-track {
  display: flex;
  align-items: stretch;
  flex: 1 1 auto;
  height: 14px;
  border-radius: 7px;
  overflow: hidden;
  background: rgba(0, 0, 0, 0.06);
}
.body--dark .status-bar-track {
  background: rgba(255, 255, 255, 0.08);
}
.status-bar-segment {
  display: block;
  height: 100%;
  cursor: pointer;
  transition:
    filter 120ms ease,
    outline-offset 120ms ease;
}
.status-bar-segment + .status-bar-segment {
  /* A faint divider keeps adjacent segments distinguishable when
     their colors are close (e.g. rejected red vs expired). */
  box-shadow: inset 1px 0 0 rgba(255, 255, 255, 0.5);
}
.status-bar-segment:hover {
  filter: brightness(1.08);
}
.status-bar-segment.is-active {
  outline: 2px solid var(--q-primary);
  outline-offset: 1px;
  z-index: 1;
  position: relative;
}
.status-bar-clear {
  flex: 0 0 auto;
}
</style>
