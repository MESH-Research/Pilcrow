import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { beforeAll, describe, expect, it } from "vitest"
import type { recordOfReviewFragment } from "src/graphql/generated/graphql"
import RecordOfReview from "./RecordOfReview.vue"

installQuasarPlugin()

type Audit = NonNullable<recordOfReviewFragment["submission"]["audits"]>[number]

const audit = (created_at: string, status: string | null): Audit =>
  ({
    id: created_at,
    created_at,
    new_values: status ? { status, __typename: "SubmissionAuditValues" } : null,
    __typename: "SubmissionAudit"
  }) as Audit

const makeAssignment = (
  overrides: {
    audits?: Audit[]
    orcid?: string | null
  } = {}
): recordOfReviewFragment =>
  ({
    id: "1",
    role: "reviewer",
    user: {
      id: "1",
      display_label: "Reviewer One",
      name: "Reviewer One",
      email: "reviewer@example.test",
      profile_metadata:
        overrides.orcid !== undefined
          ? {
              academic_profiles: {
                orcid_id: overrides.orcid,
                __typename: "AcademicProfiles"
              },
              __typename: "ProfileMetadata"
            }
          : null,
      __typename: "User"
    },
    submission: {
      id: "100",
      title: "A Submission",
      audits: overrides.audits ?? [],
      reviewers: [],
      review_coordinators: [],
      publication: {
        id: "1",
        name: "Pub 1",
        editors: [],
        publication_admins: [],
        __typename: "Publication"
      },
      __typename: "Submission"
    },
    __typename: "SubmissionAssignment"
  }) as recordOfReviewFragment

const mountRecord = async (assignment: recordOfReviewFragment) => {
  const wrapper = mount(RecordOfReview, {
    props: { assignment },
    global: { stubs: ["i18n-t", "router-link"] }
  })
  await flushPromises()
  return wrapper
}

describe("RecordOfReview completionDate", () => {
  beforeAll(() => {
    // Component creates an object URL for the per-record download link.
    Object.assign(URL, {
      createObjectURL: () => "blob:test",
      revokeObjectURL: () => {}
    })
  })

  it("shows the incomplete label when there are no audits", async () => {
    const wrapper = await mountRecord(makeAssignment({ audits: [] }))
    expect(wrapper.text()).toContain("record_of_review.completed.incomplete")
  })

  it("ignores audits that are not a post-review status", async () => {
    const wrapper = await mountRecord(
      makeAssignment({
        audits: [audit("2026-01-01T00:00:00.000000Z", "UNDER_REVIEW")]
      })
    )
    expect(wrapper.text()).toContain("record_of_review.completed.incomplete")
  })

  it("ignores audits whose new_values is null", async () => {
    const wrapper = await mountRecord(
      makeAssignment({
        audits: [audit("2026-01-01T00:00:00.000000Z", null)]
      })
    )
    expect(wrapper.text()).toContain("record_of_review.completed.incomplete")
  })

  it("formats the date of the only post-review transition", async () => {
    const wrapper = await mountRecord(
      makeAssignment({
        audits: [audit("2026-03-15T17:31:52.000000Z", "ACCEPTED_AS_FINAL")]
      })
    )
    expect(wrapper.text()).toContain("2026-03-15")
    expect(wrapper.text()).not.toContain(
      "record_of_review.completed.incomplete"
    )
  })

  it("picks the most recent post-review transition", async () => {
    const wrapper = await mountRecord(
      makeAssignment({
        audits: [
          // Deliberately out of order; the latest should win.
          audit("2026-02-10T09:00:00.000000Z", "RESUBMISSION_REQUESTED"),
          audit("2026-04-20T09:00:00.000000Z", "ACCEPTED_AS_FINAL"),
          audit("2026-03-01T09:00:00.000000Z", "REJECTED")
        ]
      })
    )
    expect(wrapper.text()).toContain("2026-04-20")
    expect(wrapper.text()).not.toContain("2026-02-10")
    expect(wrapper.text()).not.toContain("2026-03-01")
  })
})

describe("RecordOfReview orcid", () => {
  beforeAll(() => {
    Object.assign(URL, {
      createObjectURL: () => "blob:test",
      revokeObjectURL: () => {}
    })
  })

  it("renders the ORCID when present", async () => {
    const wrapper = await mountRecord(
      makeAssignment({ orcid: "0000-0002-1825-0097" })
    )
    expect(wrapper.text()).toContain("0000-0002-1825-0097")
  })

  it("omits the ORCID block when absent", async () => {
    const wrapper = await mountRecord(makeAssignment({ orcid: null }))
    expect(wrapper.text()).not.toContain(
      "account.profile.fields.profile_metadata.academic_profiles.orcid_id.label"
    )
  })
})
