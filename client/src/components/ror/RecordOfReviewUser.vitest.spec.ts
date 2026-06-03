import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import type { recordOfReviewUserFragment } from "src/graphql/generated/graphql"
import RecordOfReviewUser from "./RecordOfReviewUser.vue"

installQuasarPlugin()

const makeUser = (orcid?: string | null): recordOfReviewUserFragment =>
  ({
    id: "1",
    display_label: "Reviewer One",
    profile_metadata:
      orcid !== undefined
        ? {
            academic_profiles: {
              orcid_id: orcid,
              __typename: "AcademicProfiles"
            },
            __typename: "ProfileMetadata"
          }
        : null,
    __typename: "User"
  }) as recordOfReviewUserFragment

const mountUser = (orcid?: string | null) =>
  mount(RecordOfReviewUser, {
    props: { user: makeUser(orcid), role: "Reviewer" }
  })

describe("RecordOfReviewUser", () => {
  it("renders the display label and role", () => {
    const text = mountUser().text()
    expect(text).toContain("Reviewer One")
    expect(text).toContain("Reviewer")
  })

  it("renders the ORCID block when present", () => {
    const text = mountUser("0000-0002-1825-0097").text()
    expect(text).toContain("0000-0002-1825-0097")
    expect(text).toContain(
      "account.profile.fields.profile_metadata.academic_profiles.orcid_id.label"
    )
  })

  it("omits the ORCID block when the id is absent", () => {
    expect(mountUser(null).find("dl").exists()).toBe(false)
  })

  it("omits the ORCID block when profile metadata is absent", () => {
    expect(mountUser().find("dl").exists()).toBe(false)
  })
})
