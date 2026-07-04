import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import SubmissionsFilterPanelStatus, {
  defaultOptions
} from "./SubmissionsFilterPanelStatus.vue"

installQuasarPlugin()

const allValues = [
  "DRAFT",
  "INITIALLY_SUBMITTED",
  "RESUBMISSION_REQUESTED",
  "AWAITING_REVIEW",
  "REJECTED",
  "ACCEPTED_AS_FINAL",
  "EXPIRED",
  "ARCHIVED",
  "DELETED"
]

function factory(modelValue: string[]) {
  return mount(SubmissionsFilterPanelStatus, {
    props: { modelValue, "onUpdate:modelValue": () => {} }
  })
}

describe("SubmissionsFilterPanelStatus", () => {
  it("exports every option value as defaultOptions", () => {
    expect(defaultOptions).toEqual(allValues)
  })

  it("All selects every status", async () => {
    const wrapper = factory([])
    await wrapper
      .findAll("button")
      .find((b) => b.text() === "admin.filters.all")!
      .trigger("click")
    const events = wrapper.emitted("update:modelValue")
    expect(events?.[0]?.[0]).toEqual(allValues)
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
    const selected = ["DRAFT", "REJECTED"]
    const wrapper = factory(selected)
    await wrapper
      .findAll("button")
      .find((b) => b.text() === "admin.filters.invert")!
      .trigger("click")
    const result = wrapper.emitted("update:modelValue")?.[0]?.[0] as string[]
    expect(result).toEqual(allValues.filter((v) => !selected.includes(v)))
  })
})
