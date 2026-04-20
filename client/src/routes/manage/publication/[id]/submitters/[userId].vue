<template>
  <div class="q-px-lg q-pt-md">
    <div v-if="notFound" class="text-grey-7 q-pa-md">
      {{ $t("publication.manage.user_detail.not_found") }}
    </div>

    <template v-else>
      <q-card v-if="publicationUser" flat bordered class="q-mb-md">
        <q-card-section class="row items-center no-wrap q-gutter-md">
          <avatar-image :user="publicationUser.user" size="72px" rounded />
          <div class="col column q-gutter-xs" style="min-width: 0">
            <div class="row items-center q-gutter-sm">
              <div class="text-h6 ellipsis">{{ displayName }}</div>
              <q-badge
                v-if="publicationUser.user.staged"
                color="warning"
                text-color="dark"
                :aria-label="$t('publication.manage.user_detail.invited_aria')"
              >
                <q-icon name="schedule" size="xs" class="q-mr-xs" />
                {{ $t("publication.manage.user_detail.invited_badge") }}
              </q-badge>
            </div>
            <div
              v-if="publicationUser.user.username"
              class="text-caption text-grey-7"
            >
              {{ publicationUser.user.username }}
            </div>
            <div v-if="publicationUser.user.email" class="text-body2">
              <a
                :href="`mailto:${publicationUser.user.email}`"
                class="text-primary"
              >
                {{ publicationUser.user.email }}
              </a>
            </div>
          </div>
          <q-card class="bg-grey-2" flat>
            <q-card-section class="q-pa-md text-center">
              <div class="text-overline text-grey-7">
                {{ $t("publication.manage.user_detail.role.submitter") }}
              </div>
              <div class="text-h4">
                {{ publicationUser.as_submitter_count }}
              </div>
              <div class="text-caption text-grey-7">
                {{
                  $t("publication.manage.user_detail.submissions", {
                    n: publicationUser.as_submitter_count
                  })
                }}
              </div>
            </q-card-section>
          </q-card>
        </q-card-section>

        <!-- At-a-glance status breakdown for this submitter's
             submissions. Links each chip to the publication's
             Submissions tab filtered to that status so users can
             drill in. Only renders statuses that have submissions. -->
        <q-separator v-if="statusBreakdown.length" />
        <q-card-section v-if="statusBreakdown.length" class="q-py-sm">
          <div class="row items-center q-gutter-sm">
            <span class="text-caption text-grey-7 q-mr-sm">
              {{ $t("publication.manage.user_detail.status_breakdown") }}
            </span>
            <router-link
              v-for="entry in statusBreakdown"
              :key="entry.status"
              class="status-mini-chip row items-center q-px-sm"
              :class="{ 'status-mini-chip--active': isActive(entry.status) }"
              :style="`border-color: var(--q-${styleFor(entry.status).color})`"
              :to="chipLinkTo(entry.status)"
              :title="
                isActive(entry.status)
                  ? $t('publication.manage.user_detail.clear_filter')
                  : $t(`submission.status.${entry.status}`)
              "
            >
              <span
                :class="[
                  'status-mini-dot',
                  `bg-${styleFor(entry.status).color}`,
                  styleFor(entry.status).textClass,
                  styleFor(entry.status).pattern
                ]"
              >
                <q-icon
                  :name="styleFor(entry.status).icon"
                  size="10px"
                  class="pattern-text-mask"
                />
              </span>
              <span class="text-caption q-ml-xs">
                {{ $t(`submission.status.${entry.status}`) }}
              </span>
              <span class="text-caption text-weight-bold q-ml-xs">
                {{ entry.count }}
              </span>
            </router-link>
          </div>
        </q-card-section>
      </q-card>

      <h3 class="q-mt-lg q-mb-sm" style="font-size: 1.125rem">
        {{ $t("publication.manage.user_detail.submissions_heading") }}
      </h3>

      <QueryTable
        ref="queryTableRef"
        :query="GetPublicationSubmitterDetailDocument"
        field="publication.user.submissions"
        t-prefix="publication.dashboard"
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
          <q-btn
            v-if="!isSmallScreen"
            flat
            dense
            no-caps
            :icon="isGrid ? 'table_rows' : 'grid_view'"
            :label="isGrid ? 'Table view' : 'Grid view'"
            :aria-label="
              isGrid ? 'Switch to table view' : 'Switch to grid view'
            "
            @click="toggleViewPreference"
          />
        </template>
      </QueryTable>
    </template>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPublicationSubmitterDetail(
    $publicationId: ID!
    $userId: ID!
    $page: Int
    $first: Int
    $status: [SubmissionStatus!]
    $orderBy: [QuerySubmissionsOrderByOrderByClause!]
  ) {
    publication(id: $publicationId) {
      id
      user(id: $userId) {
        id
        user {
          id
          name
          username
          email
          staged
          ...avatarImage
        }
        as_submitter_count
        submission_status_counts(roles: [submitter]) {
          status
          count
        }
        submissions(
          page: $page
          first: $first
          orderBy: $orderBy
          status: $status
          roles: [submitter]
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
  }
`)
</script>

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { useQuasar } from "quasar"
import { useRoute, useRouter } from "vue-router"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import TextCell from "src/components/tables/common/TextCell.vue"
import ReviewTeamCell from "src/components/tables/common/ReviewTeamCell.vue"
import StatusBadgeCell from "src/pages/Publication/components/StatusBadgeCell.vue"
import SubmissionCard from "src/pages/Publication/components/SubmissionCard.vue"
import { statusStyleMap } from "src/pages/Publication/components/statusCategories"
import {
  GetPublicationSubmitterDetailDocument,
  type GetPublicationSubmitterDetailQuery,
  type SubmissionStatus
} from "src/graphql/generated/graphql"
import { setCrumbLabel } from "src/use/breadcrumbs"

definePage({
  name: "manage:publication:submitter",
  props: true,
  meta: {
    crumb: [
      {
        label: "Submitters",
        to: { name: "manage:publication:submitters" }
      },
      { label: "Submitter" }
    ]
  }
})

interface Props {
  id: string
  userId: string
}
const props = defineProps<Props>()

const $q = useQuasar()
const route = useRoute()
const router = useRouter()

const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

// Derive the user header and status counts from the same query
// the table is driving — saves a second round trip. The result is
// exposed by QueryTable via defineExpose; typed against the
// generated query type so the avatar fragment and other nested
// fields resolve correctly.
type QueryResult = GetPublicationSubmitterDetailQuery | null | undefined
const queryResult = computed(
  () => (queryTableRef.value?.result as QueryResult) ?? null
)

const publicationUser = computed(
  () => queryResult.value?.publication?.user ?? null
)

// When the query has loaded and explicitly returned null for the
// nested `user`, treat it as "not found" rather than "still loading".
const notFound = computed(() => {
  const pub = queryResult.value?.publication
  return pub !== undefined && pub !== null && pub.user === null
})

const displayName = computed(
  () =>
    publicationUser.value?.user?.name ??
    publicationUser.value?.user?.email ??
    ""
)

setCrumbLabel(
  "manage:publication:submitter",
  computed(() => displayName.value || undefined)
)

// Hide statuses with zero submissions from the at-a-glance strip —
// showing all 13 would bury the ones that matter. Ordered by count
// desc so the most common state reads first.
const statusBreakdown = computed(() => {
  const counts = publicationUser.value?.submission_status_counts ?? []
  return [...counts]
    .filter((c) => c.count > 0)
    .sort((a, b) => b.count - a.count)
})

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

// Link target: stay on this same page, just set the `status` query
// param. Matches the bracketed comma-separated encoding the
// QueryTable search/filter layer uses elsewhere. We preserve
// `view` so a grid-mode user doesn't lose it on a filter click.
function submissionsFilterTo(statuses: readonly string[]) {
  const query: Record<string, string> = { status: `[${statuses.join(",")}]` }
  if (route.query.view === "grid") query.view = "grid"
  return {
    name: "manage:publication:submitter" as const,
    params: { id: props.id, userId: props.userId },
    query
  }
}

function parseStatusList(
  value: string | string[] | undefined
): SubmissionStatus[] {
  if (!value) return []
  const str = Array.isArray(value) ? value[0] : value
  if (!str) return []
  const inner = str.startsWith("[") ? str.slice(1, -1) : str
  return inner ? (inner.split(",") as SubmissionStatus[]) : []
}

// Active status filter driven by the URL. Empty means "no filter"
// — the table shows all statuses. The ref is seeded from the URL
// at setup and kept in sync with subsequent navigation.
const statusFilter = ref<SubmissionStatus[]>(
  parseStatusList(route.query.status as string | undefined)
)

watch(
  () => route.query.status,
  (value) => {
    const parsed = parseStatusList(value as string | string[] | undefined)
    const same =
      parsed.length === statusFilter.value.length &&
      parsed.every((s) => statusFilter.value.includes(s))
    if (!same) statusFilter.value = parsed
  }
)

const tableVariables = computed(() => ({
  publicationId: props.id,
  userId: props.userId,
  ...(statusFilter.value.length ? { status: statusFilter.value } : {})
}))

// A chip is "active" when its status is the only one in the
// current filter — the UX treats the breakdown as single-status
// toggles, not a multi-select. Clicking an already-active chip
// drops the filter and returns the full list.
function isActive(status: string): boolean {
  return statusFilter.value.length === 1 && statusFilter.value[0] === status
}

function chipLinkTo(status: string) {
  if (isActive(status)) {
    const query: Record<string, string> = {}
    if (route.query.view === "grid") query.view = "grid"
    return {
      name: "manage:publication:submitter" as const,
      params: { id: props.id, userId: props.userId },
      query
    }
  }
  return submissionsFilterTo([status])
}

// Grid vs table preference, mirrored in the URL so a link can open
// the view the way the user last left it.
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

// Columns mirror the dashboard Submissions tab minus "Submitter" —
// every row on this page is by the same user, so the column would
// just repeat the avatar in the header.
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
    classes: "title-cell",
    style: "white-space: normal"
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
  background-color: #f5f5f5;
  border-radius: 4px;
}
/* Compact status chips shown in the user header. Smaller than the
   pipeline chips — they're just an at-a-glance readout, not a
   workflow element. */
.status-mini-chip {
  text-decoration: none;
  color: inherit;
  border: 1px solid;
  border-radius: 9999px;
  padding: 2px 8px;
  line-height: 1.4;
  background: #fff;
}
.status-mini-chip:hover {
  filter: brightness(0.98);
}
.status-mini-chip--active {
  box-shadow: 0 0 0 2px var(--q-primary);
  font-weight: 500;
}
.body--dark .status-mini-chip {
  background: #1d1d1d;
}
.status-mini-dot {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  flex: 0 0 auto;
}
</style>

<style>
.body--dark .q-table--grid .q-table__grid-content {
  background-color: #262626;
}
</style>
