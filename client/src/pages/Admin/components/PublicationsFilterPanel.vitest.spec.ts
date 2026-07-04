import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it, vi } from "vitest"
import PublicationsFilterPanel from "./PublicationsFilterPanel.vue"

vi.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: () => true,
    t: (token: string, params?: Record<string, unknown>) =>
      params ? `${token}:${JSON.stringify(params)}` : token
  })
}))

installQuasarPlugin()

function factory(
  visibilityFilter: "all" | "public" | "hidden" = "all",
  acceptingFilter: "all" | "yes" | "no" = "all"
) {
  return mount(PublicationsFilterPanel, {
    props: {
      visibilityFilter,
      "onUpdate:visibilityFilter": () => {},
      acceptingFilter,
      "onUpdate:acceptingFilter": () => {}
    },
    global: {
      mocks: {
        $t: (token: string, params?: Record<string, unknown>) =>
          params ? `${token}:${JSON.stringify(params)}` : token
      }
    }
  })
}

describe("PublicationsFilterPanel", () => {
  it("shows filters.label when both filters are at defaults", () => {
    const wrapper = factory("all", "all")
    expect(wrapper.text()).toContain("admin.filters.label")
    expect(wrapper.text()).not.toContain("admin.filters.active")
  })

  it("counts visibility deviation as 1", () => {
    const wrapper = factory("public", "all")
    expect(wrapper.text()).toContain('admin.filters.active:{"count":1}')
  })

  it("counts accepting deviation as 1", () => {
    const wrapper = factory("all", "yes")
    expect(wrapper.text()).toContain('admin.filters.active:{"count":1}')
  })

  it("counts both deviations as 2", () => {
    const wrapper = factory("hidden", "no")
    expect(wrapper.text()).toContain('admin.filters.active:{"count":2}')
  })
})
