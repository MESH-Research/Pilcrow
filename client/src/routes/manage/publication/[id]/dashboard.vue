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
      <!-- Workflow diagram: three lanes that roughly trace how a
           submission moves through the publication. Arrows inside a
           lane mark sequential steps. Lanes are stacked vertically
           and wrap on narrow viewports. -->
      <div class="status-flow-diagram q-mb-md">
        <!-- Main review pipeline: the happy-path sequence a
             submission follows from arrival to decision. -->
        <div class="status-lane">
          <div class="status-lane-label">
            {{ $t("publication.dashboard.lanes.active") }}
          </div>
          <div class="status-lane-row">
            <template
              v-for="(status, i) in activeLane"
              :key="`active-${status}`"
            >
              <q-icon
                v-if="i > 0"
                name="arrow_forward"
                size="sm"
                class="lane-arrow text-grey-6"
                aria-hidden="true"
              />
              <div
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
            </template>
          </div>
        </div>

        <!-- Loopback lane: statuses that kick back to the author
             before the submission re-enters the review pipeline. -->
        <div class="status-lane">
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

        <!-- Closed lane: terminal states. No arrows — these are
             all endpoints, not a sequence. -->
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
const activeLane = [
  "INITIALLY_SUBMITTED",
  "RESUBMITTED",
  "AWAITING_REVIEW",
  "UNDER_REVIEW",
  "AWAITING_DECISION"
] as const
const authorLane = ["REVISION_REQUESTED", "RESUBMISSION_REQUESTED"] as const
const closedLane = [
  "ACCEPTED_AS_FINAL",
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
.status-flow-diagram {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.status-lane {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 6px;
  padding: 8px 10px 10px;
  background: rgba(0, 0, 0, 0.02);
}
.body--dark .status-lane {
  border-color: rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
}
.status-lane-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: rgba(0, 0, 0, 0.55);
  margin-bottom: 6px;
}
.body--dark .status-lane-label {
  color: rgba(255, 255, 255, 0.65);
}
.status-lane-row {
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: 8px;
}
.lane-arrow {
  align-self: center;
}
.status-lane-hint {
  align-self: center;
}
.status-flow-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px 6px 6px;
  border: 1px solid;
  border-radius: 6px;
  min-width: 140px;
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
