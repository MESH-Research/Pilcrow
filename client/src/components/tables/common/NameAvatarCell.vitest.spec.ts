import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import NameAvatarCell from "./NameAvatarCell.vue"

installQuasarPlugin()

const stubs = {
  QTd: { template: "<td><slot /></td>" },
  AvatarImage: { template: "<img data-cy='avatar' />" }
}

function makeScope(
  value: { name?: string; username?: string; avatar_color?: string } | null,
  col: Record<string, unknown> = {}
) {
  return {
    value,
    row: {},
    col: { name: "user", ...col },
    rowIndex: 0,
    pageIndex: 0,
    key: "1",
    dense: false
  } as never
}

describe("NameAvatarCell", () => {
  it("renders name and username when user present", () => {
    const wrapper = mount(NameAvatarCell, {
      props: {
        scope: makeScope({
          name: "Alice",
          username: "alice99",
          avatar_color: "blue"
        })
      },
      global: { stubs }
    })
    expect(wrapper.text()).toContain("Alice")
    expect(wrapper.text()).toContain("alice99")
    expect(wrapper.find("[data-cy='avatar']").exists()).toBe(true)
  })

  it("hides username when column has hideUsername=true", () => {
    const wrapper = mount(NameAvatarCell, {
      props: {
        scope: makeScope(
          { name: "Alice", username: "alice99", avatar_color: "blue" },
          { hideUsername: true }
        )
      },
      global: { stubs }
    })
    expect(wrapper.text()).toContain("Alice")
    expect(wrapper.text()).not.toContain("alice99")
  })

  it("renders em-dash placeholder when user is null", () => {
    const wrapper = mount(NameAvatarCell, {
      props: { scope: makeScope(null) },
      global: { stubs }
    })
    expect(wrapper.text()).toContain("—")
    expect(wrapper.find("[data-cy='avatar']").exists()).toBe(false)
  })
})
