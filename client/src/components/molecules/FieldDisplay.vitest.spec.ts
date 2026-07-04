import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import FieldDisplay from "./FieldDisplay.vue"
import { describe, expect, it } from "vitest"

installQuasarPlugin()

describe("FieldDisplay", () => {
  it("renders label and value", () => {
    const wrapper = mount(FieldDisplay, {
      props: { label: "Email", value: "a@b.com" }
    })
    expect(wrapper.text()).toContain("Email")
    expect(wrapper.text()).toContain("a@b.com")
  })

  it("renders default slot in place of value", () => {
    const wrapper = mount(FieldDisplay, {
      props: { label: "Email", value: "fallback" },
      slots: { default: "<span data-cy='slot'>slot-value</span>" }
    })
    expect(wrapper.text()).toContain("slot-value")
    expect(wrapper.text()).not.toContain("fallback")
  })

  it("renders icon when provided", () => {
    const wrapper = mount(FieldDisplay, {
      props: { label: "Email", icon: "mail" }
    })
    expect(wrapper.find(".q-icon").exists()).toBe(true)
  })

  it("omits icon when not provided", () => {
    const wrapper = mount(FieldDisplay, {
      props: { label: "Email" }
    })
    expect(wrapper.find(".q-icon").exists()).toBe(false)
  })

  it("applies custom valueClass", () => {
    const wrapper = mount(FieldDisplay, {
      props: { label: "Email", value: "x", valueClass: "text-h6" }
    })
    expect(wrapper.find(".field-display__value").classes()).toContain("text-h6")
  })
})
