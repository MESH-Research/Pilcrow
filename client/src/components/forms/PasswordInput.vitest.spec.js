import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import PasswordInput from "./PasswordInput.vue"

import { describe, expect, it } from "vitest"

installQuasarPlugin()
describe("PasswordInputComponent", () => {
  const factory = () => mount(PasswordInput, {
  })

  it("mounts without errors", () => {
    const wrapper = factory()
    expect(wrapper).toBeTruthy()
  })

  it("uses input type password", () => {
    const wrapper = factory()
    expect(wrapper.find("input").attributes("type")).toBe("password")
  })

  it("has correct aria attributes", () => {
    const wrapper = factory()
    const i = wrapper.find("i")
    const iAttrs = i.attributes()
    expect(iAttrs.role).toBe("button")
    expect(iAttrs["aria-hidden"]).toBe("false")
    expect(iAttrs["aria-pressed"]).toBe("false")
    expect(iAttrs.tabindex).toBe("0")
    expect(iAttrs["aria-label"]).toBeTruthy()
    expect(i.classes("cursor-pointer")).toBe(true)
  })

  it("switches input type when visibility button clicked", async () => {
    const wrapper = factory()
    await wrapper.findComponent({ name: "q-icon" }).trigger("click")

    expect(wrapper.find("input").attributes("type")).toBe("text")
    expect(wrapper.find("i").attributes("aria-pressed")).toBe("true")
  })

  it("passes input event up the component tree", async () => {
    const wrapper = factory()
    const input = wrapper.findComponent({ name: "q-input" })

    await input.setValue("test")

    const updateEvent = wrapper.emitted("update:modelValue")

    expect(updateEvent).toHaveLength(1)
    expect(updateEvent[0]).toEqual(["test"])
  })

  it("has current-password autocomplete attr by default", () => {
    const wrapper = factory()
    expect(wrapper.find("input").attributes("autocomplete")).toEqual(
      "current-password"
    )
  })
})
