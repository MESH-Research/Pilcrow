import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { describe, expect, test } from "vitest"
import ExportParticipantSelector from "./ExportParticipantSelector.vue"
import type { exportParticipantSelectorFragment } from "src/graphql/generated/graphql"

installQuasarPlugin()

const mockSubmission: exportParticipantSelectorFragment = {
  commenters: [
    { id: "1", display_label: "User One", email: "one@example.com" },
    { id: "2", display_label: "User Two", email: "two@example.com" },
    { id: "3", display_label: "User Three", email: "three@example.com" }
  ]
}

const emptySubmission: exportParticipantSelectorFragment = {
  commenters: []
}

describe("ExportParticipantSelector", () => {
  const makeWrapper = (submission: exportParticipantSelectorFragment) =>
    mount(ExportParticipantSelector, {
      props: { submission }
    })

  test("renders commenter list", () => {
    const wrapper = makeWrapper(mockSubmission)
    expect(wrapper.text()).toContain("User One")
    expect(wrapper.text()).toContain("User Two")
    expect(wrapper.text()).toContain("User Three")
  })

  test("shows no commenters message when empty", () => {
    const wrapper = makeWrapper(emptySubmission)
    expect(wrapper.findAll(".q-item")).toHaveLength(0)
  })

  test("auto-selects all commenters on mount", async () => {
    const wrapper = makeWrapper(mockSubmission)
    await flushPromises()

    const emitted = wrapper.emitted("update:modelValue")
    expect(emitted).toBeTruthy()
    expect(emitted![0][0]).toHaveLength(3)
  })

  test("select all button selects all commenters", async () => {
    const wrapper = makeWrapper(mockSubmission)
    await flushPromises()

    const buttons = wrapper.findAll(".q-btn")
    const selectAllBtn = buttons[0]
    await selectAllBtn.trigger("click")
    await flushPromises()

    const emitted = wrapper.emitted("update:modelValue")
    const lastEmit = emitted![emitted!.length - 1][0] as unknown[]
    expect(lastEmit).toHaveLength(3)
  })

  test("select none button clears selection", async () => {
    const wrapper = makeWrapper(mockSubmission)
    await flushPromises()

    const buttons = wrapper.findAll(".q-btn")
    const selectNoneBtn = buttons[1]
    await selectNoneBtn.trigger("click")
    await flushPromises()

    const emitted = wrapper.emitted("update:modelValue")
    const lastEmit = emitted![emitted!.length - 1][0] as unknown[]
    expect(lastEmit).toHaveLength(0)
  })
})
