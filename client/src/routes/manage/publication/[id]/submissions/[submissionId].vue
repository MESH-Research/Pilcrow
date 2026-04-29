<template>
  <div class="q-px-lg q-pt-md">
    <div v-if="loading" class="text-grey-7 q-pa-md">{{ $t("loading") }}</div>
    <div v-else-if="!submission" class="text-grey-7 q-pa-md">
      {{ $t("submissions.create.error_title") }}
    </div>

    <template v-else>
      <q-card flat bordered class="q-mb-md submission-header-card">
        <!-- Id chip absolutely positioned in the top-left corner of
             the card so it sits flush with the border instead of
             inset by the card-section padding. Bottom-right corner
             rounded so it reads as a tucked-in tag. -->
        <div
          class="submission-id-chip"
          :aria-label="`Submission #${submission.id}`"
        >
          #{{ submission.id }}
        </div>
        <q-card-section
          class="row items-start no-wrap q-gutter-md submission-title-section"
        >
          <div class="col column q-gutter-xs" style="min-width: 0">
            <div class="row items-center no-wrap submission-title-row">
              <submission-title />
            </div>
          </div>
        </q-card-section>

        <!-- Footer doubles as the status control: the entire bar
             takes the status's category color + pattern, with the
             status label / icon / transition dropdown on the left
             and the Actions menu on the right. Replaces the
             previous q-card-section + separated badge layout. -->
        <div
          class="submission-status-footer row items-center q-px-md q-py-sm"
          :class="[
            `bg-${statusStyle.color}`,
            statusStyle.textClass,
            statusStyle.pattern
          ]"
        >
          <span
            class="status-control row items-center pattern-text-mask"
            :class="canChangeStatus ? 'cursor-pointer' : ''"
            :role="canChangeStatus ? 'button' : undefined"
            :tabindex="canChangeStatus ? 0 : undefined"
            :aria-haspopup="canChangeStatus ? 'menu' : undefined"
            :aria-label="
              canChangeStatus
                ? $t('submissions.action.change_status.label') +
                  ': ' +
                  $t(`submission.status.${submission.status}`)
                : undefined
            "
          >
            <q-icon :name="statusStyle.icon" size="sm" class="q-mr-sm" />
            <span class="text-weight-medium">
              {{ $t(`submission.status.${submission.status}`) }}
            </span>
            <q-icon
              v-if="canChangeStatus"
              name="arrow_drop_down"
              size="sm"
              class="q-ml-xs"
            />
            <q-menu
              v-if="canChangeStatus"
              anchor="bottom start"
              self="top start"
            >
              <q-list dense style="min-width: 220px">
                <q-item
                  v-for="transition in transitions"
                  :key="transition.action"
                  v-close-popup
                  role="menuitem"
                  clickable
                  :data-cy="`change_status_${transition.action}`"
                  @click.stop="openStatusChange(transition.action)"
                >
                  <q-item-section>
                    {{ $t(`submission.action.${transition.action}`) }}
                  </q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </span>

          <q-space />

          <q-btn-dropdown
            data-cy="submission_actions"
            flat
            no-caps
            class="status-footer-actions pattern-text-mask"
            :label="$t('submission.actions_menu')"
          >
            <q-list>
              <q-item
                v-close-popup
                clickable
                :to="{
                  name: `submission:${viewType}`,
                  params: { id: submission.id }
                }"
                data-cy="submission_review_btn"
              >
                <q-item-section avatar>
                  <q-icon :name="viewActionIcon" />
                </q-item-section>
                <q-item-section>
                  {{ $t(`submission.action.${viewType}`) }}
                </q-item-section>
              </q-item>

              <q-separator />

              <q-item
                v-close-popup
                clickable
                :disable="exportDisabled"
                :to="
                  exportDisabled
                    ? undefined
                    : {
                        name: 'submission:export',
                        params: { id: submission.id }
                      }
                "
                data-cy="submission_export_btn"
              >
                <q-item-section avatar>
                  <q-icon name="exit_to_app" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    {{ $t("export.call_to_action") }}
                  </q-item-label>
                  <q-item-label v-if="exportDisabled" caption>
                    {{
                      isDisabledByRole
                        ? $t("export.disabled.by_role")
                        : $t("export.disabled.by_state")
                    }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </q-card>

      <q-tabs
        v-model="activeTab"
        active-color="primary"
        indicator-color="primary"
        align="left"
        class="q-mb-md"
        dense
        no-caps
      >
        <q-tab name="details">
          <manage-tab-header :label="$t('submission.tabs.details')" />
        </q-tab>
        <q-tab
          name="team"
          :class="teamNeedsAttention ? ACTION_NEEDED_TAB_CLASS : ''"
        >
          <manage-tab-header
            :label="$t('submission.tabs.team')"
            :action-needed="teamNeedsAttention"
            :reason="teamAttentionReason"
          />
        </q-tab>
        <q-tab name="history">
          <manage-tab-header :label="$t('submission.tabs.history')" />
        </q-tab>
      </q-tabs>

      <q-tab-panels
        v-model="activeTab"
        animated
        keep-alive
        class="bg-transparent"
      >
        <q-tab-panel name="details" class="q-pa-none">
          <manage-panel
            data-cy="submission_overview"
            :title="$t('submission.overview.heading')"
          >
            <q-card-section class="q-py-sm">
              <dl class="overview-list q-my-none">
                <div class="overview-row">
                  <dt>{{ $t("submission.overview.submitted") }}</dt>
                  <dd v-if="submission.submitted_at">
                    <span>{{ formatDateTime(submission.submitted_at) }}</span>
                    <span class="overview-relative">
                      ({{ relativeTime(submission.submitted_at) }})
                    </span>
                  </dd>
                  <dd v-else class="text-grey-7">
                    {{ $t("submission.overview.not_submitted") }}
                  </dd>
                </div>
                <div class="overview-row">
                  <dt>{{ $t("submission.overview.last_updated") }}</dt>
                  <dd v-if="submission.updated_at">
                    <span>{{ formatDateTime(submission.updated_at) }}</span>
                    <span class="overview-relative">
                      ({{ relativeTime(submission.updated_at) }})
                    </span>
                  </dd>
                  <dd v-else class="text-grey-7">—</dd>
                </div>
                <div v-if="submission.created_by" class="overview-row">
                  <dt>{{ $t("submission.overview.created_by") }}</dt>
                  <dd>
                    <router-link
                      :to="{
                        name: 'manage:publication:submitter',
                        params: {
                          id: submission.publication.id,
                          userId: submission.created_by.id
                        }
                      }"
                      class="overview-user-link row items-center q-gutter-x-xs"
                    >
                      <avatar-image
                        :user="submission.created_by"
                        size="24px"
                        rounded
                      />
                      <span>{{ creatorDisplayName }}</span>
                    </router-link>
                  </dd>
                </div>
              </dl>
            </q-card-section>
          </manage-panel>

          <manage-panel
            :title="$t('submission.submitters.heading')"
            :count="submission.submitters.length"
            :missing="submission.submitters.length === 0"
          >
            <q-card-section>
              <submission-assignee-list
                data-cy="submitters_list"
                role-group="submitters"
                headerless
                :users="submission.submitters"
                :assignments="submission.assignments"
                :publication-id="submission.publication.id"
              />
            </q-card-section>
          </manage-panel>
        </q-tab-panel>

        <q-tab-panel name="team" class="q-pa-none">
          <!-- Review Team grouping: the coordinator + reviewers
               share an outer heading so they read as one unit
               (the team responsible for this submission) instead
               of two unrelated panels. The inner SectionHeader on
               each role list reads at h3, the outer at h2 — natural
               parent/child hierarchy. -->
          <manage-panel
            level="h2"
            :title="$t('publication.manage.review_team.heading')"
          >
            <q-card-section>
              <submission-assignee-list
                data-cy="coordinators_list"
                role-group="review_coordinators"
                :users="submission.review_coordinators"
                :assignments="submission.assignments"
                :publication-id="submission.publication.id"
                mutable
                @unassign="confirmRemoveCoordinator"
              >
                <template #action>
                  <!-- Assign-coordinator only shows when the slot is
                       empty. There's no "Replace" button: the backend
                       requires the existing RC to be removed first
                       before a new one can be assigned, so the
                       per-row Remove icon is the entry point to that
                       workflow. -->
                  <q-btn
                    v-if="!submission.review_coordinators.length"
                    data-cy="assign_coordinator_btn"
                    flat
                    dense
                    no-caps
                    color="primary"
                    icon="person_add"
                    :label="
                      $t(
                        'publication.manage.assign_team.open_assign_coordinator'
                      )
                    "
                    @click="openAssignCoordinator"
                  />
                </template>
              </submission-assignee-list>
            </q-card-section>

            <q-separator />

            <q-card-section>
              <submission-assignee-list
                data-cy="reviewers_list"
                role-group="reviewers"
                :users="submission.reviewers"
                :assignments="submission.assignments"
                :publication-id="submission.publication.id"
                mutable
                @unassign="unassignReviewer"
              >
                <template #action>
                  <q-btn
                    data-cy="add_reviewers_btn"
                    flat
                    dense
                    no-caps
                    color="primary"
                    icon="group_add"
                    :label="
                      $t('publication.manage.assign_team.open_add_reviewers')
                    "
                    @click="openAddReviewers"
                  />
                </template>
              </submission-assignee-list>
            </q-card-section>
          </manage-panel>
        </q-tab-panel>

        <q-tab-panel name="history" class="q-pa-none">
          <manage-panel
            data-cy="activity_section"
            :title="$t('submission.activity_section.title')"
          >
            <q-card-section>
              <p
                v-if="submission.audits.length === 0"
                class="text-grey-7 q-my-none"
              >
                {{ $t("submission.activity_section.no_activity") }}
              </p>
              <q-timeline v-else color="primary" layout="comfortable">
                <manage-submission-audit
                  v-for="audit in reversedAudits"
                  :key="audit.id"
                  :audit="audit"
                />
              </q-timeline>
            </q-card-section>
          </manage-panel>
        </q-tab-panel>
      </q-tab-panels>
    </template>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetManagedSubmission($id: ID!) {
    submission(id: $id) {
      id
      title
      status
      created_at
      updated_at
      submitted_at
      created_by {
        id
        name
        username
        email
        ...avatarImage
      }
      audits {
        ...ManageSubmissionAuditFields
      }
      # Publication context exists solely so SubmissionExportButton's
      # role gate can run (isPublicationAdmin / isEditor / my_role).
      # The breadcrumb name is loaded by the parent layout, so we
      # don't need it here.
      publication {
        id
        my_role
        editors {
          ...relatedUserFields
        }
        publication_admins {
          ...relatedUserFields
        }
      }
      submitters {
        ...relatedUserFields
      }
      reviewers {
        ...relatedUserFields
      }
      review_coordinators {
        ...relatedUserFields
      }
      # Pivot rows used to render "assigned X ago" beside each name
      # in the assignee lists.
      assignments {
        id
        role
        created_at
        user {
          id
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, provide, ref, watch, watchEffect } from "vue"
import { DateTime } from "luxon"
import { useRoute, useRouter } from "vue-router"
import { useQuery } from "@vue/apollo-composable"
import { useTimeAgo } from "src/use/timeAgo"
import AssignReviewTeamDialog from "src/pages/Publication/components/AssignReviewTeamDialog.vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import ConfirmStatusChangeDialog from "src/components/dialogs/ConfirmStatusChangeDialog.vue"
import ManagePanel from "src/pages/Publication/components/ManagePanel.vue"
import ManageSubmissionAudit from "src/pages/Publication/components/ManageSubmissionAudit.vue"
import ManageTabHeader, {
  ACTION_NEEDED_TAB_CLASS
} from "src/pages/Publication/components/ManageTabHeader.vue"
import SubmissionAssigneeList from "src/pages/Publication/components/SubmissionAssigneeList.vue"
import SubmissionTitle from "src/components/SubmissionTitle.vue"
import {
  GetManagedSubmissionDocument,
  type Submission,
  type SubmissionAudit
} from "src/graphql/generated/graphql"
import {
  UPDATE_SUBMISSION_REVIEWERS,
  UPDATE_SUBMISSION_REVIEW_COORDINATORS
} from "src/graphql/mutations"
import { submissionKey } from "src/use/submissionContext"
import { setCrumbLabel } from "src/use/breadcrumbs"
import { statusStyleMap } from "src/pages/Publication/components/statusCategories"
import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"
import { useMutation } from "@vue/apollo-composable"
import { useFeedbackMessages, useSubmissionExport } from "src/use/guiElements"
import { useStatusTransitions } from "src/use/submissionStatusTransitions"

definePage({
  name: "manage:publication:submission",
  props: true,
  meta: {
    crumb: [
      {
        label: "Submissions",
        to: { name: "manage:publication:submissions" }
      },
      { label: "Submission" }
    ]
  }
})

interface Props {
  id: string
  submissionId: string
}
const props = defineProps<Props>()

const route = useRoute()
const router = useRouter()

// Tab state mirrored to ?tab=… so deep-links open on the right tab
// and a refresh keeps you where you were. Falls back to "details"
// (the most common landing surface) on first visit or when the
// query param holds an unrecognized value.
type SubmissionTab = "details" | "team" | "history"
const ALLOWED_TABS: readonly SubmissionTab[] = ["details", "team", "history"]
function parseTab(value: unknown): SubmissionTab {
  return ALLOWED_TABS.includes(value as SubmissionTab)
    ? (value as SubmissionTab)
    : "details"
}
const activeTab = ref<SubmissionTab>(parseTab(route.query.tab))

watch(activeTab, (value) => {
  const query: Record<string, string> = { ...route.query } as Record<
    string,
    string
  >
  if (value === "details") delete query.tab
  else query.tab = value
  void router.replace({ query })
})

watch(
  () => route.query.tab,
  (value) => {
    const next = parseTab(value)
    if (next !== activeTab.value) activeTab.value = next
  }
)

const { result, loading } = useQuery(GetManagedSubmissionDocument, () => ({
  id: props.submissionId
}))

// Shared components (SubmissionTitle, AssignedSubmissionUsers,
// SubmissionExportButton, ManageSubmissionAudit) are typed against
// the schema's full `Submission` / `SubmissionAudit`. The fields
// they read are a subset of what GetManagedSubmission returns, so
// we widen the narrow query type here at the boundary rather than
// forcing those components to learn the per-page shape.
const submission = computed(
  () => (result.value?.submission ?? undefined) as Submission | undefined
)

provide(submissionKey, submission)

setCrumbLabel(
  "manage:publication:submission",
  computed(() => submission.value?.title)
)

// Mirrors the legacy details page: DRAFT shows the preview action,
// INITIALLY_SUBMITTED shows view, everything else routes to review.
const viewType = ref<"review" | "preview" | "view">("review")
watchEffect(() => {
  const status = submission.value?.status
  if (status === "DRAFT") viewType.value = "preview"
  else if (status === "INITIALLY_SUBMITTED") viewType.value = "view"
  else viewType.value = "review"
})

const statusStyle = computed(() => {
  const status = submission.value?.status ?? ""
  return (
    statusStyleMap[status] ?? {
      color: "grey",
      textClass: "text-white",
      icon: "help",
      pattern: ""
    }
  )
})

const reversedAudits = computed(
  () => (submission.value?.audits?.slice().reverse() ?? []) as SubmissionAudit[]
)

// Overview tab helpers — match the format ManageSubmissionAudit uses
// for the activity timeline so timestamps read consistently across
// the page ("Apr 27, 2026 · 3:42 PM" with a relative aside).
const timeAgo = useTimeAgo()
function formatDateTime(iso: string | null | undefined): string {
  if (!iso) return ""
  const dt = DateTime.fromISO(iso)
  return dt.isValid ? dt.toFormat("LLL d, yyyy · h:mm a") : ""
}
function relativeTime(iso: string | null | undefined): string {
  if (!iso) return ""
  const dt = DateTime.fromISO(iso)
  return dt.isValid ? timeAgo.format(dt.toJSDate(), "long") : ""
}

const creatorDisplayName = computed(() => {
  const u = submission.value?.created_by
  if (!u) return ""
  return u.name || u.username || u.email || ""
})

// Icon shown next to the dropdown's primary action — picks a glyph
// matching the destination action so the menu reads at a glance.
const viewActionIcon = computed(() => {
  if (viewType.value === "review") return "rate_review"
  if (viewType.value === "preview") return "preview"
  return "visibility"
})

// Reuse SubmissionExportButton's role/state gating directly so the
// dropdown's Export item respects the same access rules. The
// composable returns refs; we expose a single `exportDisabled` for
// the menu binding plus the role-vs-state ref so the caption can
// explain *which* gate is closed.
const { isDisabledByRole, isDisabledByState } = useSubmissionExport(submission)
const exportDisabled = computed(
  () => isDisabledByRole.value || isDisabledByState.value
)

// Reuse the same status-transition machinery the submissions index
// table uses, so the badge here exposes the same set of allowed
// status changes for the same role/status combination.
const { canChangeStatus, transitions } = useStatusTransitions(submission)
function openStatusChange(action: string) {
  if (!submission.value) return
  $q.dialog({
    component: ConfirmStatusChangeDialog,
    componentProps: {
      action,
      submissionId: submission.value.id,
      currentStatus: submission.value.status
    }
  })
}

// Mirror the SubmissionAssigneeList "missing" flag onto the Review
// Team tab itself: if either the coordinator slot or the reviewers
// list is empty, surface a flag alert on the tab so the user sees
// the warning even when they're on a different tab.
const teamNeedsAttention = computed(() => {
  const sub = submission.value
  if (!sub) return false
  return !sub.review_coordinators.length || !sub.reviewers.length
})

// Tooltip explaining *why* the team tab is flagged, so a hover on
// the flag icon names the actual gap rather than just signalling
// that one exists.
const teamAttentionReason = computed(() => {
  const sub = submission.value
  if (!sub) return ""
  const noRC = !sub.review_coordinators.length
  const noReviewers = !sub.reviewers.length
  if (noRC && noReviewers) return t("submission.team_alert.no_team")
  if (noRC) return t("submission.team_alert.no_coordinator")
  if (noReviewers) return t("submission.team_alert.no_reviewers")
  return ""
})

const $q = useQuasar()
const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages()

function openAssignCoordinator() {
  if (!submission.value) return
  const sub = submission.value
  $q.dialog({
    component: AssignReviewTeamDialog,
    componentProps: {
      submissionId: sub.id,
      publicationId: sub.publication.id,
      role: "review_coordinator",
      multiple: false,
      // When replacing an existing RC the user expects the current
      // person to NOT show up as a candidate (they're already in the
      // role). Excluding them keeps the picker focused on alternatives.
      excludeUserIds: sub.review_coordinators.map((u) => u.id)
    }
  })
}

function openAddReviewers() {
  if (!submission.value) return
  const sub = submission.value
  $q.dialog({
    component: AssignReviewTeamDialog,
    componentProps: {
      submissionId: sub.id,
      publicationId: sub.publication.id,
      role: "reviewer",
      multiple: true,
      excludeUserIds: sub.reviewers.map((u) => u.id)
    }
  })
}

const { mutate: disconnectCoordinator } = useMutation(
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  { refetchQueries: ["GetManagedSubmission"] }
)
const { mutate: disconnectReviewer } = useMutation(
  UPDATE_SUBMISSION_REVIEWERS,
  {
    refetchQueries: ["GetManagedSubmission"]
  }
)

function nameOf(user: {
  name?: string | null
  username?: string | null
  email?: string
}) {
  return user.name || user.username || user.email || ""
}

function confirmRemoveCoordinator(userId: string) {
  const current = submission.value?.review_coordinators?.find(
    (u) => u.id === userId
  )
  if (!current) return
  const name = nameOf(current)
  $q.dialog({
    title: t("publication.manage.assign_team.remove_coordinator_confirm_title"),
    message: t(
      "publication.manage.assign_team.remove_coordinator_confirm_body",
      { name }
    ),
    cancel: true,
    persistent: true,
    ok: {
      label: t("publication.manage.assign_team.remove_coordinator_confirm_ok"),
      color: "negative"
    }
  }).onOk(async () => {
    try {
      await disconnectCoordinator({
        id: submission.value!.id,
        disconnect: [current.id]
      })
      newStatusMessage(
        "success",
        t("publication.manage.assign_team.remove_coordinator_success", { name })
      )
    } catch {
      newStatusMessage(
        "failure",
        t("publication.manage.assign_team.remove_coordinator_error")
      )
    }
  })
}

async function unassignReviewer(userId: string) {
  if (!submission.value) return
  const current = submission.value.reviewers.find((u) => u.id === userId)
  const name = current ? nameOf(current) : ""
  try {
    await disconnectReviewer({
      id: submission.value.id,
      disconnect: [userId]
    })
    newStatusMessage(
      "success",
      t("submission.reviewers.unassign.success", { display_name: name })
    )
  } catch {
    newStatusMessage("failure", t("submission.reviewers.unassign.error"))
  }
}
</script>

<style scoped>
.submission-title-row :deep(h2) {
  font-size: 1.5rem;
  line-height: 1.25;
  margin: 0;
}
/* Id chip — sits in the card's top-left corner with no padding
   buffer so the number reads as a "tag" tucked into the border.
   Title section reserves enough top space below it that the chip
   never overlaps the title text. */
.submission-header-card {
  position: relative;
}
.submission-id-chip {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
  font-family: var(--q-font-mono, "SFMono-Regular", Menlo, Consolas, monospace);
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.02em;
  padding: 4px 10px;
  border-bottom-right-radius: 6px;
  background: rgba(0, 0, 0, 0.06);
  color: rgba(0, 0, 0, 0.7);
  white-space: nowrap;
}
.body--dark .submission-id-chip {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.85);
}
/* Make room for the corner chip so it doesn't overlap the title.
   28px ≈ chip height (8px vertical padding + ~20px line-height). */
.submission-title-section {
  padding-top: 28px;
}
/* Colored status footer reads as a continuous extension of the
   card. `overflow: hidden` on the card lets us round the footer's
   bottom corners to match the card without restating the radius. */
.submission-header-card {
  overflow: hidden;
}
.submission-status-footer {
  flex-wrap: wrap;
  row-gap: 8px;
}
/* The Actions dropdown sits on the colored bar — flat with
   inherited text color reads cleanly without competing with the
   status's own label. */
.status-footer-actions {
  color: inherit;
}
/* Submission overview definition list. Two columns: a label that
   reads as a tracked uppercase caption (matches the role-label
   treatment used on submitter / team-member detail pages) and a
   value that flows naturally. The relative timestamp sits inline
   in a quieter grey so the absolute date reads as the primary
   timestamp. */
.overview-list {
  display: grid;
  grid-template-columns: max-content 1fr;
  column-gap: 24px;
  row-gap: 8px;
}
.overview-row {
  display: contents;
}
.overview-row dt {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
  align-self: center;
}
.body--dark .overview-row dt {
  color: rgba(255, 255, 255, 0.72);
}
.overview-row dd {
  margin: 0;
  font-size: 0.9375rem;
  line-height: 1.4;
}
.overview-relative {
  margin-left: 6px;
  color: rgba(0, 0, 0, 0.55);
  font-variant-numeric: tabular-nums;
}
.body--dark .overview-relative {
  color: rgba(255, 255, 255, 0.6);
}
.overview-user-link {
  text-decoration: none;
  color: inherit;
  display: inline-flex;
  align-items: center;
}
.overview-user-link:hover,
.overview-user-link:focus-visible {
  color: var(--q-primary);
  text-decoration: underline;
}
</style>
