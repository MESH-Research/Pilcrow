<template>
  <section v-if="visible" data-cy="needs_action_publications">
    <q-card
      v-if="total === 0"
      flat
      bordered
      class="needs-action-card"
      data-cy="needs_action_all_clear"
    >
      <!-- Mirrors the structure of the needs-attention variant below:
           a colored q-markup-table header (just a single column in the
           all-clear case, since there's nothing to categorize) on top
           of a plain-bg body section with the follow-up message and
           CTA. Reusing the same markup/classes keeps the two states
           visually consistent. -->
      <q-markup-table flat class="needs-action-table">
        <thead>
          <tr class="needs-action-table__header bg-positive text-white">
            <th class="text-left pattern-diagonal">
              <span class="pattern-text-mask">
                <q-icon name="task_alt" size="sm" class="q-mr-xs" />
                {{ $t("dashboard.needs_action.all_clear.heading") }}
              </span>
            </th>
          </tr>
        </thead>
      </q-markup-table>
      <q-separator />
      <q-card-section
        class="q-py-sm q-px-md row items-center q-gutter-sm text-body2"
      >
        <div class="col text-grey-7">
          {{ $t("dashboard.needs_action.all_clear.message") }}
        </div>
        <q-btn
          unelevated
          no-caps
          color="primary"
          icon-right="arrow_forward"
          :to="{ name: 'manage:index' }"
          :label="$t('dashboard.needs_action.view_all')"
          data-cy="needs_action_manage_cta"
        />
      </q-card-section>
    </q-card>

    <q-card v-else flat bordered class="needs-action-card">
      <q-markup-table flat separator="horizontal" class="needs-action-table">
        <thead>
          <tr class="needs-action-table__header bg-warning text-dark">
            <th class="text-left pattern-diagonal">
              <span class="pattern-text-mask">
                <q-icon name="flag" size="sm" class="q-mr-xs" />
                {{ $t("dashboard.needs_action.heading") }}
              </span>
            </th>
            <th
              v-for="stage in stageColumns"
              :key="stage.key"
              class="text-right needs-action-table__stage-col pattern-diagonal"
            >
              <span class="pattern-text-mask">
                {{ $t(`publication.manage.dashboard.stages.${stage.key}`) }}
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in topRows"
            :key="row.id"
            class="needs-action-row"
            @click="goToPublication(row.publication.id)"
          >
            <td>
              <div class="row items-center no-wrap q-gutter-sm">
                <router-link
                  :to="publicationLink(row.publication.id)"
                  class="text-primary publication-name"
                  @click.stop
                >
                  {{ row.publication.name }}
                </router-link>
                <RoleBadge :role="row.role" />
              </div>
            </td>
            <td
              v-for="stage in stageColumns"
              :key="stage.key"
              class="text-right"
            >
              <span
                v-if="countForStage(row, stage) > 0"
                class="needs-action-count"
              >
                {{ countForStage(row, stage) }}
              </span>
              <span v-else class="text-grey-5">0</span>
            </td>
          </tr>
        </tbody>
      </q-markup-table>

      <q-separator v-if="showFooter" />
      <q-card-section
        v-if="showFooter"
        class="q-py-sm q-px-md row items-center q-gutter-sm text-body2"
      >
        <div
          v-if="overflowCount > 0"
          class="row items-center q-gutter-sm text-grey-7 col"
        >
          <span class="needs-action-overflow-chip bg-warning text-dark">
            +{{ overflowCount }}
          </span>
          <span>
            {{
              $t("dashboard.needs_action.more", {
                n: overflowCount
              })
            }}
          </span>
        </div>
        <q-btn
          unelevated
          no-caps
          color="primary"
          icon-right="arrow_forward"
          :to="{ name: 'manage:index' }"
          :label="$t('dashboard.needs_action.view_all')"
          data-cy="needs_action_manage_cta"
        />
      </q-card-section>
    </q-card>
  </section>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Fetch up to `$first` managed publications that currently have any
// needs-action submission, then rank client-side by the rolled-up
// count so the busiest ones surface to the top `limit` shown in the
// card. `with_statuses` also populates paginatorInfo.total with the
// number of publications matching — that's how we decide whether to
// show the "N more need your attention" overflow note.
graphql(`
  query DashboardNeedsActionPublications($first: Int!) {
    currentUser {
      id
      publications(
        roles: [publication_admin, editor]
        with_statuses: [
          INITIALLY_SUBMITTED
          RESUBMITTED
          AWAITING_REVIEW
          AWAITING_DECISION
        ]
        first: $first
        page: 1
      ) {
        paginatorInfo {
          total
        }
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
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { DashboardNeedsActionPublicationsDocument } from "src/graphql/generated/graphql"
import { useRouter } from "vue-router"
import RoleBadge from "src/components/atoms/RoleBadge.vue"

interface Props {
  /** Max rows rendered in the card. */
  limit?: number
  /**
   * Upper bound on how many publications we fetch to rank client-side.
   * Keeps the request cheap but large enough to reliably find the
   * busiest few among a realistic managed-publication count.
   */
  fetchCeiling?: number
}
const props = withDefaults(defineProps<Props>(), {
  limit: 5,
  fetchCeiling: 25
})

// Stage columns shown in the table, ordered to match the workflow
// (screening → reviewing → decision). Each stage names the subset of
// needs-action statuses that live in that stage so per-stage counts
// can be rolled up from the publication's `submission_status_counts`.
interface StageColumn {
  key: "screening" | "reviewing" | "decision"
  statuses: readonly string[]
}
const stageColumns: readonly StageColumn[] = [
  { key: "screening", statuses: ["INITIALLY_SUBMITTED", "RESUBMITTED"] },
  { key: "reviewing", statuses: ["AWAITING_REVIEW"] },
  { key: "decision", statuses: ["AWAITING_DECISION"] }
]
const NEEDS_ACTION_STATUSES = stageColumns.flatMap((s) => s.statuses)

const { result, loading } = useQuery(
  DashboardNeedsActionPublicationsDocument,
  () => ({
    first: props.fetchCeiling
  })
)

interface Row {
  id: string
  role: string
  publication: {
    id: string
    name: string
    submission_status_counts?: Array<{ status: string; count: number }> | null
  }
}

function statusCounts(row: Row): Map<string, number> {
  const counts = new Map<string, number>()
  for (const c of row.publication.submission_status_counts ?? []) {
    counts.set(c.status, c.count)
  }
  return counts
}

function countForStage(row: Row, stage: StageColumn): number {
  const counts = statusCounts(row)
  return stage.statuses.reduce((sum, s) => sum + (counts.get(s) ?? 0), 0)
}

function totalNeedsAction(row: Row): number {
  return stageColumns.reduce((sum, stage) => sum + countForStage(row, stage), 0)
}

const rows = computed<Row[]>(() => {
  const data = result.value?.currentUser?.publications?.data ?? []
  return [...data].sort(
    (a, b) => totalNeedsAction(b as Row) - totalNeedsAction(a as Row)
  )
})

const total = computed(
  () => result.value?.currentUser?.publications?.paginatorInfo?.total ?? 0
)

const topRows = computed(() => rows.value.slice(0, props.limit))
const overflowCount = computed(() => Math.max(0, total.value - props.limit))
// Stay hidden during the initial load so we don't flash the "all
// caught up" state before the query resolves. Once we have a result
// (loading false OR a cached value already in place), render the
// right variant — table when there's work, all-clear otherwise.
const visible = computed(() => !loading.value || result.value !== undefined)
const showFooter = computed(() => total.value > 0)

const router = useRouter()

function publicationLink(id: string) {
  // Jump straight to the publication's submissions list with the
  // needs-action statuses pre-filtered, so the click takes the user
  // to the actionable queue rather than the overview.
  return {
    name: "manage:publication:submissions" as const,
    params: { id },
    query: {
      status: `[${NEEDS_ACTION_STATUSES.join(",")}]`,
      scroll: "table"
    }
  }
}

function goToPublication(id: string) {
  router.push(publicationLink(id))
}
</script>

<style scoped>
/* Constrain the card so the table columns don't stretch to fill a
   wide viewport — the 4 columns (publication + 3 stages) read
   better at a tighter width than the full dashboard page width. */
.needs-action-card {
  max-width: 48rem;
}
.needs-action-table :deep(th) {
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
/* Pattern overlay on header cells is intentionally NOT declared
   here — the global rules in app.sass (`.a11y-patterns` /
   prefers-contrast:more) apply the diagonal texture only when the
   user has opted in or their OS is set for higher contrast. Color
   alone carries the needs-attention identity for the default case. */
.needs-action-table__stage-col {
  width: 7rem;
}
.needs-action-row {
  cursor: pointer;
  transition: background-color 120ms ease;
}
.needs-action-row:hover {
  background-color: rgba(0, 0, 0, 0.035);
}
.body--dark .needs-action-row:hover {
  background-color: rgba(255, 255, 255, 0.05);
}
.publication-name {
  font-weight: 500;
  text-decoration: none;
  overflow-wrap: anywhere;
  word-break: break-word;
}
.publication-name:hover {
  text-decoration: underline;
}
.needs-action-count {
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  color: var(--q-warning);
  filter: brightness(0.75);
}
/* Small rounded pill carrying the overflow count, echoing the
   +N chip style used for per-row submission icon overflows. */
.needs-action-overflow-chip {
  display: inline-flex;
  align-items: center;
  padding: 0 8px;
  min-width: 2rem;
  height: 1.4rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  letter-spacing: 0.02em;
  justify-content: center;
}
</style>
