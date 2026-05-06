import { computed, toValue, type MaybeRef } from "vue"
import { useCurrentUser } from "./user"
import type { Submission } from "src/graphql/generated/graphql"

/**
 * One legal transition out of a submission's current status.
 *
 * `action` is both the i18n key suffix (submission.action.<action>) and
 * the value passed to ConfirmStatusChangeDialog, which mutates the
 * submission to the target status and emits the correct feedback.
 */
export interface StatusTransition {
  /** The status we're transitioning to. */
  toStatus: string
  /** Dialog action name & i18n key suffix. */
  action: string
}

/**
 * Declarative state machine for submission status transitions.
 * Keys are the current status; values are the list of transitions
 * offered from that state (in the order they should be rendered).
 */
export const statusTransitions: Record<string, StatusTransition[]> = {
  DRAFT: [{ toStatus: "INITIALLY_SUBMITTED", action: "submit_for_review" }],
  INITIALLY_SUBMITTED: [
    { toStatus: "UNDER_REVIEW", action: "accept_for_review" }
  ],
  AWAITING_REVIEW: [{ toStatus: "UNDER_REVIEW", action: "open" }],
  UNDER_REVIEW: [
    { toStatus: "AWAITING_DECISION", action: "close" },
    { toStatus: "ACCEPTED_AS_FINAL", action: "accept_as_final" },
    { toStatus: "RESUBMISSION_REQUESTED", action: "request_resubmission" },
    { toStatus: "REJECTED", action: "reject" }
  ],
  AWAITING_DECISION: [
    { toStatus: "ACCEPTED_AS_FINAL", action: "accept_as_final" },
    { toStatus: "RESUBMISSION_REQUESTED", action: "request_resubmission" },
    { toStatus: "REJECTED", action: "reject" }
  ],
  RESUBMITTED: [
    { toStatus: "ACCEPTED_AS_FINAL", action: "accept_as_final" },
    { toStatus: "RESUBMISSION_REQUESTED", action: "request_resubmission" },
    { toStatus: "REJECTED", action: "reject" }
  ],
  EXPIRED: [
    { toStatus: "ACCEPTED_AS_FINAL", action: "accept_as_final" },
    { toStatus: "RESUBMISSION_REQUESTED", action: "request_resubmission" },
    { toStatus: "REJECTED", action: "reject" }
  ],
  ACCEPTED_AS_FINAL: [
    { toStatus: "ARCHIVED", action: "archive" },
    { toStatus: "DELETED", action: "delete" }
  ],
  ARCHIVED: [{ toStatus: "DELETED", action: "delete" }],
  // Data-model permits archive/delete from these closed-ish states.
  REJECTED: [
    { toStatus: "ARCHIVED", action: "archive" },
    { toStatus: "DELETED", action: "delete" }
  ],
  RESUBMISSION_REQUESTED: [
    { toStatus: "ARCHIVED", action: "archive" },
    { toStatus: "DELETED", action: "delete" }
  ],
  // Terminal — no transitions offered.
  REVISION_REQUESTED: [],
  DELETED: []
}

/**
 * Returns the available status transitions for a submission, taking
 * both the declarative state machine and the acting user's role into
 * account. Reviewers can't change status; everyone else can perform
 * whatever the state machine permits.
 */
export function useStatusTransitions(
  submission: MaybeRef<
    Pick<Submission, "status" | "reviewers"> | null | undefined
  >
) {
  const { isReviewer } = useCurrentUser()

  const canChangeStatus = computed(() => {
    const sub = toValue(submission)
    if (!sub) return false
    // Reviewers can't change status at all.
    if (isReviewer(sub as Submission)) return false
    return (statusTransitions[sub.status] ?? []).length > 0
  })

  const transitions = computed<StatusTransition[]>(() => {
    if (!canChangeStatus.value) return []
    const sub = toValue(submission)
    if (!sub) return []
    return statusTransitions[sub.status] ?? []
  })

  return { canChangeStatus, transitions }
}
