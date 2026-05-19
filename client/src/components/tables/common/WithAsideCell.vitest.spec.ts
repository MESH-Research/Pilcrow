import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import WithAsideCell from "./WithAsideCell.vue"

installQuasarPlugin()

const stubs = { QTd: { template: "<td><slot /></td>" } }

function makeScope(extra: Record<string, unknown>) {
  return {
    value: "primary",
    row: { name: "Alice", profile: { email: "a@b.com" } },
    col: { name: "name", ...extra },
    rowIndex: 0,
    pageIndex: 0,
    key: "1",
    dense: false
  } as never
}

function factory(
  scope: ReturnType<typeof makeScope>,
  slots: Record<string, string> = {}
) {
  return mount(WithAsideCell, {
    props: { scope },
    slots,
    global: { stubs }
  })
}

describe("WithAsideCell", () => {
  it("renders scope.value by default", () => {
    expect(factory(makeScope({})).text()).toContain("primary")
  })

  it("resolves aside via dotted string path", () => {
    expect(factory(makeScope({ aside: "profile.email" })).text()).toContain(
      "a@b.com"
    )
  })

  it("resolves aside via function", () => {
    const wrapper = factory(
      makeScope({
        aside: (row: Record<string, unknown>) => `id-${row.name}`
      })
    )
    expect(wrapper.text()).toContain("id-Alice")
  })

  it("renders asideLabel string with aside value", () => {
    const wrapper = factory(
      makeScope({ aside: "profile.email", asideLabel: "Email" })
    )
    expect(wrapper.text()).toContain("Email: a@b.com")
  })

  it("resolves asideLabel via function", () => {
    const wrapper = factory(
      makeScope({ aside: "name", asideLabel: () => "Who" })
    )
    expect(wrapper.text()).toContain("Who: Alice")
  })

  it("renders empty label/value as just ': '", () => {
    const wrapper = factory(makeScope({}))
    expect(wrapper.find(".text-caption").text()).toBe(":")
  })

  it("slot 'value' overrides default value rendering", () => {
    const wrapper = factory(makeScope({}), {
      value: "<span>custom-value</span>"
    })
    expect(wrapper.text()).toContain("custom-value")
    expect(wrapper.text()).not.toContain("primary")
  })

  it("slot 'aside' overrides default aside rendering", () => {
    const wrapper = factory(makeScope({ aside: "name", asideLabel: "X" }), {
      aside: "<span>custom-aside</span>"
    })
    expect(wrapper.text()).toContain("custom-aside")
    expect(wrapper.text()).not.toContain("X: Alice")
  })
})
