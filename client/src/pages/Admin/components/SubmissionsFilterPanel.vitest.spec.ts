import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import SubmissionsFilterPanel from "./SubmissionsFilterPanel.vue"
import { defaultOptions as defaultStatusOptions } from "./SubmissionsFilterPanelStatus.vue"
import { defaultOptions as defaultRoleOptions } from "./SubmissionsFilterPanelRoles.vue"

installQuasarPlugin()

const childStubs = [
  "SubmissionsFilterPanelStatus",
  "SubmissionsFilterPanelRoles",
  "SubmissionsFilterPanelPublication"
]

function factory(props: Record<string, unknown> = {}) {
  return mount(SubmissionsFilterPanel, {
    props: {
      statusFilter: [...defaultStatusOptions],
      "onUpdate:statusFilter": () => {},
      roleFilter: [...defaultRoleOptions],
      "onUpdate:roleFilter": () => {},
      publicationFilter: null,
      "onUpdate:publicationFilter": () => {},
      ...props
    },
    global: {
      stubs: childStubs,
      mocks: {
        $t: (token: string, params?: Record<string, unknown>) =>
          params ? `${token}:${JSON.stringify(params)}` : token
      }
    }
  })
}

describe("SubmissionsFilterPanel", () => {
  it("shows filters.label when every dimension is at its default", () => {
    const wrapper = factory()
    expect(wrapper.text()).toContain("admin.filters.label")
    expect(wrapper.text()).not.toContain("admin.filters.active")
  })

  it("counts a publication filter as 1 active dimension", () => {
    const wrapper = factory({ publicationFilter: "5" })
    expect(wrapper.text()).toContain('admin.filters.active:{"count":1}')
  })

  it("counts a non-default status filter as 1 active dimension", () => {
    const wrapper = factory({ statusFilter: [defaultStatusOptions[0]] })
    expect(wrapper.text()).toContain('admin.filters.active:{"count":1}')
  })

  it("counts a non-default role filter as 1 active dimension", () => {
    const wrapper = factory({ roleFilter: [defaultRoleOptions[0]] })
    expect(wrapper.text()).toContain('admin.filters.active:{"count":1}')
  })

  it("counts all three deviating dimensions", () => {
    const wrapper = factory({
      statusFilter: [defaultStatusOptions[0]],
      roleFilter: [defaultRoleOptions[0]],
      publicationFilter: "9"
    })
    expect(wrapper.text()).toContain('admin.filters.active:{"count":3}')
  })

  it("seeds default status and role filters on mount when empty", () => {
    const wrapper = factory({ statusFilter: [], roleFilter: [] })
    expect(wrapper.emitted("update:statusFilter")?.[0]?.[0]).toEqual(
      defaultStatusOptions
    )
    expect(wrapper.emitted("update:roleFilter")?.[0]?.[0]).toEqual(
      defaultRoleOptions
    )
  })

  it("does not overwrite filters that already have values on mount", () => {
    const wrapper = factory({
      statusFilter: [defaultStatusOptions[0]],
      roleFilter: [defaultRoleOptions[0]]
    })
    expect(wrapper.emitted("update:statusFilter")).toBeUndefined()
    expect(wrapper.emitted("update:roleFilter")).toBeUndefined()
  })
})
