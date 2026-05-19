import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it, vi } from "vitest"
import DateTimeCell from "./DateTimeCell.vue"

vi.mock("src/use/timeAgo", () => ({
  useTimeAgo: () => ({
    format: () => "5 days ago"
  })
}))

installQuasarPlugin()

function makeScope(value: string) {
  return {
    value,
    row: {},
    col: { name: "created_at" },
    rowIndex: 0,
    pageIndex: 0,
    key: "1",
    dense: false
  } as never
}

describe("DateTimeCell", () => {
  it("renders absolute date in 'LLL d yyyy h:mm a' format", () => {
    const wrapper = mount(DateTimeCell, {
      props: { scope: makeScope("2026-03-15T14:30:00Z") },
      global: { stubs: { QTd: { template: "<td><slot /></td>" } } }
    })
    // Avoid timezone brittleness — only assert pieces that are stable.
    const text = wrapper.text()
    expect(text).toMatch(/Mar 15 2026/)
  })

  it("renders relative time from useTimeAgo mock", () => {
    const wrapper = mount(DateTimeCell, {
      props: { scope: makeScope("2026-03-15T14:30:00Z") },
      global: { stubs: { QTd: { template: "<td><slot /></td>" } } }
    })
    expect(wrapper.text()).toContain("5 days ago")
  })
})
