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
        </q-card-section>

        <!-- Reviewer + Coordinator per-phase counts — these are the
             headline numbers for this user's review team involvement. -->
        <q-separator />
        <q-card-section class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-card flat bordered class="full-height">
              <q-card-section>
                <div class="text-overline text-grey-7">
                  {{ $t("publication.manage.user_detail.role.reviewer") }}
                </div>
                <div class="row items-baseline q-gutter-lg q-my-xs">
                  <div>
                    <div class="text-h4">
                      {{ publicationUser.as_reviewer_active_count }}
                    </div>
                    <div class="text-caption text-grey-7">
                      {{ $t("publication.manage.user_detail.phase.active") }}
                    </div>
                  </div>
                  <div>
                    <div class="text-h4">
                      {{ publicationUser.as_reviewer_completed_count }}
                    </div>
                    <div class="text-caption text-grey-7">
                      {{ $t("publication.manage.user_detail.phase.completed") }}
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-6">
            <q-card flat bordered class="full-height">
              <q-card-section>
                <div class="text-overline text-grey-7">
                  {{ $t("publication.manage.user_detail.role.coordinator") }}
                </div>
                <div class="row items-baseline q-gutter-lg q-my-xs">
                  <div>
                    <div class="text-h4">
                      {{ publicationUser.as_coordinator_active_count }}
                    </div>
                    <div class="text-caption text-grey-7">
                      {{ $t("publication.manage.user_detail.phase.active") }}
                    </div>
                  </div>
                  <div>
                    <div class="text-h4">
                      {{ publicationUser.as_coordinator_completed_count }}
                    </div>
                    <div class="text-caption text-grey-7">
                      {{ $t("publication.manage.user_detail.phase.completed") }}
                    </div>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </q-card-section>

        <!-- At-a-glance status breakdown for submissions this user is
             a reviewer or coordinator on. Each chip deep-links into
             the publication's Submissions tab, filtered to that
             status. Statuses with zero submissions are hidden. -->
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
        :query="GetPublicationTeamMemberDetailDocument"
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
  query GetPublicationTeamMemberDetail(
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
        as_reviewer_active_count
        as_reviewer_completed_count
        as_coordinator_active_count
        as_coordinator_completed_count
        submission_status_counts(roles: [reviewer, review_coordinator]) {
          status
          count
        }
        submissions(
          page: $page
          first: $first
          orderBy: $orderBy
          status: $status
          roles: [reviewer, review_coordinator]
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
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import TextCell from "src/components/tables/common/TextCell.vue"
import ReviewTeamCell from "src/components/tables/common/ReviewTeamCell.vue"
import StatusBadgeCell from "src/pages/Publication/components/StatusBadgeCell.vue"
import SubmissionCard from "src/pages/Publication/components/SubmissionCard.vue"
import { statusStyleMap } from "src/pages/Publication/components/statusCategories"
import {
  GetPublicationTeamMemberDetailDocument,
  type GetPublicationTeamMemberDetailQuery,
  type SubmissionStatus
} from "src/graphql/generated/graphql"
import { setCrumbLabel } from "src/use/breadcrumbs"

definePage({
  name: "manage:publication:team_member",
  props: true,
  meta: {
    crumb: [
      {
        label: "Review Team",
        to: { name: "manage:publication:team" }
      },
      { label: "Team Member" }
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

// The query driving the QueryTable is the only one we fire — pull
// user-header fields straight from its result (exposed via
// defineExpose). Typed against the generated operation so
// avatarImage and NameAvatarCell fragments resolve correctly.
type QueryResult = GetPublicationTeamMemberDetailQuery | null | undefined
const queryResult = computed(
  () => (queryTableRef.value?.result as QueryResult) ?? null
)

const publicationUser = computed(
  () => queryResult.value?.publication?.user ?? null
)

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
  "manage:publication:team_member",
  computed(() => displayName.value || undefined)
)

// Compact status readout matching the submitter page — show only
// statuses with a non-zero count, ordered by count desc.
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

// Link target: stay on this same page with the filter applied.
// Same encoding the QueryTable search/filter layer uses. Preserves
// `view` so a grid-mode user doesn't lose it on a filter click.
function submissionsFilterTo(statuses: readonly string[]) {
  const query: Record<string, string> = { status: `[${statuses.join(",")}]` }
  if (route.query.view === "grid") query.view = "grid"
  return {
    name: "manage:publication:team_member" as const,
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

// Treat breakdown chips as single-status toggles — clicking an
// already-active chip clears the filter and returns the full list.
function isActive(status: string): boolean {
  return statusFilter.value.length === 1 && statusFilter.value[0] === status
}

function chipLinkTo(status: string) {
  if (isActive(status)) {
    const query: Record<string, string> = {}
    if (route.query.view === "grid") query.view = "grid"
    return {
      name: "manage:publication:team_member" as const,
      params: { id: props.id, userId: props.userId },
      query
    }
  }
  return submissionsFilterTo([status])
}

// Grid vs table preference mirrored in the URL.
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

// Columns match the dashboard submissions tab. Keep the Submitter
// column here — on a team page the rows are assigned from many
// different submitters, so it's informative (unlike the
// submitter-detail page where every row is the same user).
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
    name: "created_by",
    field: (row) => row.created_by ?? null,
    align: "left",
    component: NameAvatarCell,
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
