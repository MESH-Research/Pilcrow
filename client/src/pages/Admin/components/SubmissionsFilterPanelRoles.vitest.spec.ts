import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import SubmissionsFilterPanelRoles, {
  defaultOptions
} from "./SubmissionsFilterPanelRoles.vue"

installQuasarPlugin()

const allValues = ["submitter", "reviewer", "review_coordinator"]

function factory(modelValue: string[]) {
  return mount(SubmissionsFilterPanelRoles, {
    props: { modelValue, "onUpdate:modelValue": () => {} }
  })
}

describe("SubmissionsFilterPanelRoles", () => {
  it("exports every option value as defaultOptions", () => {
    expect(defaultOptions).toEqual(allValues)
  })

  it("All selects every role", async () => {
    const wrapper = factory([])
    await wrapper
      .findAll("button")
      .find((b) => b.text() === "admin.filters.all")!
      .trigger("click")
    expect(wrapper.emitted("update:modelValue")?.[0]?.[0]).toEqual(allValues)
  })

  it("None clears the selection", async () => {
    const wrapper = factory([...allValues])
    await wrapper
      .findAll("button")
      .find((b) => b.text() === "admin.filters.none")!
      .trigger("click")
    expect(wrapper.emitted("update:modelValue")?.[0]?.[0]).toEqual([])
  })

  it("Invert flips selected and unselected", async () => {
    const wrapper = factory(["reviewer"])
    await wrapper
      .findAll("button")
      .find((b) => b.text() === "admin.filters.invert")!
      .trigger("click")
    expect(wrapper.emitted("update:modelValue")?.[0]?.[0]).toEqual([
      "submitter",
      "review_coordinator"
    ])
  })
})
