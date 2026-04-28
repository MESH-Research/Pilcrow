<template>
  <div class="q-px-lg q-pt-md">
    <div v-if="loading" class="text-grey-7 q-pa-md">{{ $t("loading") }}</div>
    <div v-else-if="!submission" class="text-grey-7 q-pa-md">
      {{ $t("submissions.create.error_title") }}
    </div>

    <template v-else>
      <q-card flat bordered class="q-mb-md">
        <q-card-section class="row items-start no-wrap q-gutter-md">
          <div class="col column q-gutter-xs" style="min-width: 0">
            <div class="text-caption text-grey-7">#{{ submission.id }}</div>
            <div class="row items-center no-wrap submission-title-row">
              <submission-title />
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section class="row items-center q-gutter-sm q-py-sm status-row">
          <q-badge
            :color="statusStyle.color"
            :class="[
              'text-weight-medium q-pa-sm',
              statusStyle.textClass,
              statusStyle.pattern
            ]"
          >
            <q-icon :name="statusStyle.icon" size="xs" />
            <q-separator vertical class="q-mx-xs" />
            <span class="pattern-text-mask">
              {{ $t(`submission.status.${submission.status}`) }}
            </span>
          </q-badge>
          <q-space />
          <submission-export-button :submission="submission" />
          <q-btn
            data-cy="submission_review_btn"
            color="accent"
            :label="$t(`submission.action.${viewType}`)"
            :to="{
              name: `submission:${viewType}`,
              params: { id: submission.id }
            }"
          />
        </q-card-section>
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
          <q-card flat bordered class="q-mb-md">
            <q-card-section>
              <submission-assignee-list
                data-cy="submitters_list"
                role-group="submitters"
                :users="submission.submitters"
                :assignments="submission.assignments"
                :publication-id="submission.publication.id"
              />
            </q-card-section>
          </q-card>
        </q-tab-panel>

        <q-tab-panel name="team" class="q-pa-none">
          <q-card flat bordered class="q-mb-md column">
            <!-- Review Team grouping: the coordinator + reviewers
                 share an outer heading so they read as one unit
                 (the team responsible for this submission) instead
                 of two unrelated panels. -->
            <q-card-section class="q-py-sm">
              <h2 class="review-team-heading q-my-none">
                {{ $t("publication.manage.review_team.heading") }}
              </h2>
            </q-card-section>
            <q-separator />

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
          </q-card>
        </q-tab-panel>

        <q-tab-panel name="history" class="q-pa-none">
          <q-card flat bordered class="q-mb-md" data-cy="activity_section">
            <q-card-section class="q-py-sm">
              <h3 class="section-heading">
                {{ $t("submission.activity_section.title") }}
              </h3>
            </q-card-section>
            <q-separator />
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
          </q-card>
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
import { useRoute, useRouter } from "vue-router"
import { useQuery } from "@vue/apollo-composable"
import AssignReviewTeamDialog from "src/pages/Publication/components/AssignReviewTeamDialog.vue"
import ManageSubmissionAudit from "src/pages/Publication/components/ManageSubmissionAudit.vue"
import ManageTabHeader, {
  ACTION_NEEDED_TAB_CLASS
} from "src/pages/Publication/components/ManageTabHeader.vue"
import SubmissionAssigneeList from "src/pages/Publication/components/SubmissionAssigneeList.vue"
import SubmissionTitle from "src/components/SubmissionTitle.vue"
import SubmissionExportButton from "src/components/atoms/SubmissionExportButton.vue"
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
import { useFeedbackMessages } from "src/use/guiElements"

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
.status-row {
  flex-wrap: wrap;
  row-gap: 8px;
}
/* Outer "Review Team" header sits one level above the
   `.section-heading` sub-headings rendered by SubmissionAssigneeList,
   so it has more weight + a touch more size to read as the parent. */
.review-team-heading {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.3;
}
</style>
