import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, RouterLinkStub } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import RecordOfReviewCard from "./RecordOfReviewCard.vue"

installQuasarPlugin()

interface AssignmentOverrides {
  role?: string
  status?: string
  updated_at?: string
}

const makeAssignment = (overrides: AssignmentOverrides = {}) => ({
  id: "1",
  role: overrides.role ?? "reviewer",
  submission: {
    id: "100",
    title: "A Submission",
    status: overrides.status ?? "ACCEPTED_AS_FINAL",
    updated_at: overrides.updated_at ?? "2026-03-15T17:31:52.000000Z",
    submitters: [],
    publication: { id: "7", name: "Pub Seven" }
  }
})

const mountCard = (overrides: AssignmentOverrides = {}, selected = false) =>
  mount(RecordOfReviewCard, {
    props: { assignment: makeAssignment(overrides), selected },
    global: { stubs: { "router-link": RouterLinkStub } }
  })

describe("RecordOfReviewCard content", () => {
  it("renders the submission id, title, and publication", () => {
    const text = mountCard().text()
    expect(text).toContain("#100")
    expect(text).toContain("A Submission")
    expect(text).toContain("Pub Seven")
  })

  it("renders the role and status as translation keys", () => {
    const text = mountCard({
      role: "review_coordinator",
      status: "REJECTED"
    }).text()
    expect(text).toContain("admin.users.details.roles.review_coordinator")
    expect(text).toContain("submission.status.REJECTED")
  })

  it("formats the updated date absolutely", () => {
    const text = mountCard({
      updated_at: "2026-03-15T17:31:52.000000Z"
    }).text()
    // toFormat("LLL d yyyy h:mm a") — locale-independent month + day + year.
    expect(text).toContain("Mar 15 2026")
  })

  it("links the title to the submission view", () => {
    const link = mountCard().findComponent(RouterLinkStub)
    expect(link.props("to")).toEqual({
      name: "submission:view",
      params: { id: "100" }
    })
  })
})

describe("RecordOfReviewCard selection", () => {
  it("labels the checkbox with the review id and title", () => {
    const checkbox = mountCard().find(".q-checkbox")
    expect(checkbox.attributes("aria-label")).toContain(
      "record_of_review.label_select_review_checkbox"
    )
  })

  it("reflects the selected model", () => {
    const checkbox = mountCard({}, true).find(".q-checkbox")
    expect(checkbox.attributes("aria-checked")).toBe("true")
  })

  it("emits update:selected when toggled", async () => {
    const wrapper = mountCard()
    await wrapper.find(".q-checkbox").trigger("click")
    expect(wrapper.emitted("update:selected")?.at(-1)).toEqual([true])
  })
})
