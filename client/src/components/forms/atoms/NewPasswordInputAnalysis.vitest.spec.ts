import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { merge } from "lodash"
import PasswordFieldAnalysis from "./NewPasswordInputAnalysis.vue"

import { describe, expect, it } from "vitest"

installQuasarPlugin()
describe("NewPasswordInputAnalysis", () => {
  const mergeProps = (props = {}) => {
    return merge(
      {
        complexity: {
          score: 2,
          crack_times_display: {
            offline_slow_hashing_1e4_per_second: "1 week"
          },
          feedback: {
            warning: "warning_message",
            suggestions: ["suggestion_1", "suggestion_2"]
          }
        }
      },
      props
    )
  }

  const factory = () =>
    mount(PasswordFieldAnalysis, {
      props: mergeProps()
    })

  it("mounts without errors", () => {
    const wrapper = factory()
    expect(wrapper).toBeTruthy()
  })

  it("correctly displays suggestions", async () => {
    const wrapper = factory()
    expect(wrapper.findAllComponents(".suggestion").length).toBe(2)
    await wrapper.setProps(
      mergeProps({
        complexity: {
          feedback: { suggestions: ["one", "second suggestions", "three"] }
        }
      })
    )
    expect(wrapper.vm.suggestions.length).toBe(3)
    const suggestions = wrapper.findAllComponents(".suggestion")
    expect(suggestions.length).toBe(3)
    expect(suggestions.at(1).html()).toContain("second suggestion")
  })

  it("correctly displays warnings", async () => {
    const wrapper = factory()
    expect(wrapper.vm.warning).toBe("warning_message")

    const warning = wrapper.findComponent(".warning")
    expect(warning.html()).toContain("warning_message")

    await wrapper.setProps(
      mergeProps({ complexity: { feedback: { warning: "" } } })
    )

    expect(wrapper.findComponent(".warning").exists()).toBe(false)
  })
})
