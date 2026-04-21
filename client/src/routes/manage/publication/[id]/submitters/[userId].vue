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
            <div class="text-h5 text-weight-bold q-my-none ellipsis">
              {{ displayName }}
              <q-badge
                v-if="publicationUser.user.staged"
                color="warning"
                text-color="dark"
                class="q-ml-sm align-middle"
                :aria-label="$t('publication.manage.user_detail.invited_aria')"
              >
                <q-icon name="schedule" size="xs" class="q-mr-xs" />
                {{ $t("publication.manage.user_detail.invited_badge") }}
              </q-badge>
            </div>
            <!-- Username + email stacked under the name. Same
                 size + muted tone so they share a visual family;
                 email stays an anchor but inherits color by
                 default and only promotes to primary on hover. -->
            <div
              v-if="publicationUser.user.username"
              class="text-body2 text-grey-7 user-meta"
            >
              {{ publicationUser.user.username }}
            </div>
            <div
              v-if="publicationUser.user.email"
              class="text-body2 text-grey-7 user-meta"
            >
              <a
                :href="`mailto:${publicationUser.user.email}`"
                class="user-meta-email"
              >
                {{ publicationUser.user.email }}
              </a>
            </div>
          </div>
        </q-card-section>

        <!-- At-a-glance status breakdown for this submitter's
             submissions. Links each chip to the publication's
             Submissions tab filtered to that status so users can
             drill in. Only renders statuses that have submissions. -->
        <q-separator v-if="statusBreakdown.length" />
        <q-card-section v-if="statusBreakdown.length" class="q-py-sm">
          <SubmissionStatusBar
            :counts="statusBreakdown"
            :filtered-status="activeFilter"
            :link-for="(status) => chipLinkTo(status)"
            :closed-link-for="(statuses) => submissionsFilterTo(statuses)"
            :clear-link="clearLink"
          />
        </q-card-section>
      </q-card>

      <UserProfileCard
        v-if="publicationUser?.user?.profile_metadata"
        :profile="publicationUser.user.profile_metadata"
        class="q-mb-md"
      />

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
          profile_metadata {
            ...userProfileCard
          }
        }
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
import UserProfileCard from "src/components/users/UserProfileCard.vue"
import SubmissionStatusBar from "src/components/users/SubmissionStatusBar.vue"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import TextCell from "src/components/tables/common/TextCell.vue"
import ReviewTeamCell from "src/components/tables/common/ReviewTeamCell.vue"
import StatusBadgeCell from "src/pages/Publication/components/StatusBadgeCell.vue"
import SubmissionCard from "src/pages/Publication/components/SubmissionCard.vue"
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

// The status breakdown is modeled as single-status filter toggles —
// the "active" filter is the sole status when exactly one is set,
// null otherwise.
const activeFilter = computed(() =>
  statusFilter.value.length === 1 ? statusFilter.value[0] : null
)

function chipLinkTo(status: string) {
  if (activeFilter.value === status) return clearLink.value
  return submissionsFilterTo([status])
}

// Route back to the unfiltered detail page. Preserves `view=grid`
// so clearing the filter doesn't also flip the user back to table
// layout if they'd opted into grid.
const clearLink = computed(() => {
  const query: Record<string, string> = {}
  if (route.query.view === "grid") query.view = "grid"
  return {
    name: "manage:publication:submitter" as const,
    params: { id: props.id, userId: props.userId },
    query
  }
})

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
/* Username + email line under the display name. Both share the
   same text-body2 muted grey so they scan as one metadata row;
   the email's underline-on-hover keeps a subtle link affordance
   without competing with the name for visual weight. */
.user-meta {
  line-height: 1.35;
}
.user-meta-email {
  color: inherit;
  text-decoration: none;
}
.user-meta-email:hover,
.user-meta-email:focus-visible {
  color: var(--q-primary);
  text-decoration: underline;
}
/* Role label above a count — matches the dashboard's stage-label
   exactly (0.7rem, tracked, semi-bold, muted grey, uppercase)
   so both surfaces share one vocabulary. */
.role-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
}
.body--dark .role-label {
  color: rgba(255, 255, 255, 0.72);
}
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
  border-radius: 4px;
}
</style>
