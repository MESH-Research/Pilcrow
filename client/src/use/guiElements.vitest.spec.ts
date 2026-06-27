import { describe, test, expect } from "vitest"
import { ref } from "vue"
import { useSubmissionExport, useStatusChangeControls } from "./guiElements"
import type {
  Submission,
  SubmissionAbilities
} from "src/graphql/generated/graphql"

/** Minimal submission stub — only the fields these composables read. */
function submission(partial: {
  status?: string
  abilities?: Partial<SubmissionAbilities>
}): Submission {
  return partial as unknown as Submission
}

describe("useSubmissionExport", () => {
  test("disables by access when there is no submission", () => {
    const { isDisabledByAccess } = useSubmissionExport(ref(null))
    expect(isDisabledByAccess.value).toBe(true)
  })

  test("gates export on the `view` ability, not a role", () => {
    const withView = useSubmissionExport(
      ref(submission({ status: "REJECTED", abilities: { view: true } }))
    )
    expect(withView.isDisabledByAccess.value).toBe(false)

    const withoutView = useSubmissionExport(
      ref(submission({ status: "REJECTED", abilities: { view: false } }))
    )
    expect(withoutView.isDisabledByAccess.value).toBe(true)
  })

  test("disables by state outside the exportable states", () => {
    const exportable = useSubmissionExport(
      ref(submission({ status: "ARCHIVED", abilities: { view: true } }))
    )
    expect(exportable.isDisabledByState.value).toBe(false)

    const notExportable = useSubmissionExport(
      ref(submission({ status: "UNDER_REVIEW", abilities: { view: true } }))
    )
    expect(notExportable.isDisabledByState.value).toBe(true)
  })
})

describe("useStatusChangeControls", () => {
  test("disables by role when there is no submission", () => {
    const { statusChangingDisabledByRole } = useStatusChangeControls(ref(null))
    expect(statusChangingDisabledByRole.value).toBe(true)
  })

  test("gates status changes on the `update_status` ability", () => {
    const allowed = useStatusChangeControls(
      ref(
        submission({
          status: "UNDER_REVIEW",
          abilities: { update_status: true }
        })
      )
    )
    expect(allowed.statusChangingDisabledByRole.value).toBe(false)

    const denied = useStatusChangeControls(
      ref(
        submission({
          status: "UNDER_REVIEW",
          abilities: { update_status: false }
        })
      )
    )
    expect(denied.statusChangingDisabledByRole.value).toBe(true)
  })

  test("disables by state in a terminal status", () => {
    const { statusChangingDisabledByState } = useStatusChangeControls(
      ref(
        submission({ status: "REJECTED", abilities: { update_status: true } })
      )
    )
    expect(statusChangingDisabledByState.value).toBe(true)
  })
})
