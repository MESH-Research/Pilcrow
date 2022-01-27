import PasswordFieldAnalysis from "./NewPasswordInputAnalysis.vue"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { mount } from "@vue/test-utils"
import { merge } from "lodash"

installQuasarPlugin()
describe("NewPasswordInputAnalysis", () => {
  const mergeProps = (props = {}) => {
    return merge(
      {
        complexity: {
          score: 2,
          crack_times_display: {
            offline_slow_hashing_1e4_per_second: "1 week",
          },
          feedback: {
            warning: "warning_message",
            suggestions: ["suggestion_1", "suggestion_2"],
          },
        },
      },
      props
    )
  }

  const wrapper = mount(PasswordFieldAnalysis, {
    global: {
      mocks: {
        $t: (token) => token,
      },
    },
    props: mergeProps(),
  })

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  it("correctly displays suggestions", async () => {
    expect(wrapper.findAllComponents(".suggestion").length).toBe(2)
    await wrapper.setProps(
      mergeProps({
        complexity: {
          feedback: { suggestions: ["one", "second suggestions", "three"] },
        },
      })
    )
    expect(wrapper.vm.suggestions.length).toBe(3)
    const suggestions = wrapper.findAllComponents(".suggestion")
    expect(suggestions.length).toBe(3)
    expect(suggestions.at(1).html()).toContain("second suggestion")
  })

  it("correctly displays warnings", async () => {
    expect(wrapper.vm.warning).toBe("warning_message")

    const warning = wrapper.findComponent(".warning")
    expect(warning.html()).toContain("warning_message")

    await wrapper.setProps(
      mergeProps({ complexity: { feedback: { warning: "" } } })
    )

    expect(wrapper.findComponent(".warning").exists()).toBe(false)
  })
})
