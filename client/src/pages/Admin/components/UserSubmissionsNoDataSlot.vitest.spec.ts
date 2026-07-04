import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import UserSubmissionsNoDataSlot from "./UserSubmissionsNoDataSlot.vue"

installQuasarPlugin()

function factory(props: Record<string, unknown> = {}) {
  return mount(UserSubmissionsNoDataSlot, {
    props,
    global: {
      mocks: {
        $t: (token: string) => token
      }
    }
  })
}

describe("UserSubmissionsNoDataSlot", () => {
  it("always shows the empty-state message", () => {
    const wrapper = factory({
      statusFilter: ["DRAFT"],
      roleFilter: ["reviewer"]
    })
    expect(wrapper.text()).toContain("admin.users.details.no_submissions")
  })

  it("hides the filter warning banner when both filters are populated", () => {
    const wrapper = factory({
      statusFilter: ["DRAFT"],
      roleFilter: ["reviewer"]
    })
    expect(wrapper.text()).not.toContain(
      "admin.users.details.submissions.no_data.banner_warning"
    )
  })

  it("warns about an empty status filter", () => {
    const wrapper = factory({ statusFilter: [], roleFilter: ["reviewer"] })
    expect(wrapper.text()).toContain(
      "admin.users.details.submissions.no_data.banner_warning"
    )
    expect(wrapper.text()).toContain(
      "admin.users.details.submissions.no_data.no_status"
    )
    expect(wrapper.text()).not.toContain(
      "admin.users.details.submissions.no_data.no_role"
    )
  })

  it("warns about an empty role filter", () => {
    const wrapper = factory({ statusFilter: ["DRAFT"], roleFilter: [] })
    expect(wrapper.text()).toContain(
      "admin.users.details.submissions.no_data.no_role"
    )
    expect(wrapper.text()).not.toContain(
      "admin.users.details.submissions.no_data.no_status"
    )
  })

  it("defaults both filters to empty and warns for both", () => {
    const wrapper = factory()
    expect(wrapper.text()).toContain(
      "admin.users.details.submissions.no_data.no_status"
    )
    expect(wrapper.text()).toContain(
      "admin.users.details.submissions.no_data.no_role"
    )
  })

  it("emits resetFilters when the reset button is clicked", async () => {
    const wrapper = factory({ statusFilter: [], roleFilter: [] })
    await wrapper
      .findAll("button")
      .find((b) =>
        b.text().includes("admin.users.details.submissions.no_data.reset")
      )!
      .trigger("click")
    expect(wrapper.emitted("resetFilters")).toHaveLength(1)
  })
})
