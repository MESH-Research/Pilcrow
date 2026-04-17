import { describe, expect, it, vi } from "vitest"
import { ref } from "vue"
import {
  statusTransitions,
  useStatusTransitions,
  type StatusTransition
} from "./submissionStatusTransitions"
import type { Submission } from "src/graphql/generated/graphql"
import { SubmissionStatus } from "src/graphql/generated/graphql"

vi.mock("./user", () => ({
  useCurrentUser: () => ({
    isReviewer: (sub: { reviewers?: { id: string }[] }) =>
      sub?.reviewers?.some((r) => r.id === "current-user") ?? false
  })
}))

function makeSubmission(
  status: SubmissionStatus,
  reviewers: { id: string }[] = []
) {
  return { status, reviewers } as Pick<Submission, "status" | "reviewers">
}

describe("statusTransitions map", () => {
  it("defines transitions for all workflow statuses", () => {
    const expected = [
      "DRAFT",
      "INITIALLY_SUBMITTED",
      "AWAITING_REVIEW",
      "UNDER_REVIEW",
      "AWAITING_DECISION",
      "RESUBMITTED",
      "EXPIRED",
      "ACCEPTED_AS_FINAL",
      "ARCHIVED",
      "REJECTED",
      "RESUBMISSION_REQUESTED",
      "REVISION_REQUESTED",
      "DELETED"
    ]
    for (const status of expected) {
      expect(statusTransitions).toHaveProperty(status)
    }
  })

  it("DELETED and REVISION_REQUESTED are terminal (no transitions)", () => {
    expect(statusTransitions["DELETED"]).toEqual([])
    expect(statusTransitions["REVISION_REQUESTED"]).toEqual([])
  })

  it("each transition has toStatus and action strings", () => {
    for (const [, transitions] of Object.entries(statusTransitions)) {
      for (const t of transitions) {
        expect(typeof t.toStatus).toBe("string")
        expect(typeof t.action).toBe("string")
        expect(t.toStatus.length).toBeGreaterThan(0)
        expect(t.action.length).toBeGreaterThan(0)
      }
    }
  })
})

describe("useStatusTransitions", () => {
  it("returns transitions for a non-reviewer user", () => {
    const sub = ref(makeSubmission(SubmissionStatus.UNDER_REVIEW))
    const { canChangeStatus, transitions } = useStatusTransitions(sub)

    expect(canChangeStatus.value).toBe(true)
    expect(transitions.value.length).toBe(4)
    expect(transitions.value.map((t: StatusTransition) => t.action)).toEqual([
      "close",
      "accept_as_final",
      "request_resubmission",
      "reject"
    ])
  })

  it("blocks status changes for a reviewer", () => {
    const sub = ref(
      makeSubmission(SubmissionStatus.UNDER_REVIEW, [{ id: "current-user" }])
    )
    const { canChangeStatus, transitions } = useStatusTransitions(sub)

    expect(canChangeStatus.value).toBe(false)
    expect(transitions.value).toEqual([])
  })

  it("returns false for null submission", () => {
    const sub = ref(null)
    const { canChangeStatus, transitions } = useStatusTransitions(sub)

    expect(canChangeStatus.value).toBe(false)
    expect(transitions.value).toEqual([])
  })

  it("returns false for terminal statuses", () => {
    const sub = ref(makeSubmission(SubmissionStatus.DELETED))
    const { canChangeStatus, transitions } = useStatusTransitions(sub)

    expect(canChangeStatus.value).toBe(false)
    expect(transitions.value).toEqual([])
  })

  it("reacts to status changes", () => {
    const sub = ref(makeSubmission(SubmissionStatus.DRAFT))
    const { canChangeStatus, transitions } = useStatusTransitions(sub)

    expect(canChangeStatus.value).toBe(true)
    expect(transitions.value).toHaveLength(1)

    sub.value = makeSubmission(SubmissionStatus.DELETED)
    expect(canChangeStatus.value).toBe(false)
    expect(transitions.value).toEqual([])
  })

  it("handles unknown status gracefully", () => {
    const sub = ref(makeSubmission("NONEXISTENT_STATUS" as SubmissionStatus))
    const { canChangeStatus, transitions } = useStatusTransitions(sub)

    expect(canChangeStatus.value).toBe(false)
    expect(transitions.value).toEqual([])
  })
})
