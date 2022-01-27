import NewPasswordInput from "./NewPasswordInput.vue"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { mount } from "@vue/test-utils"

installQuasarPlugin()
describe("NewPasswordInput", () => {
  const wrapper = mount(NewPasswordInput, {
    global: {
      mocks: {
        $t: (t) => t,
      },
    },
    props: {
      complexity: {
        score: 2,
        crack_times_display: {
          offline_slow_hashing_1e4_per_second: "1 week",
        },
        feedback: {
          suggestions: [],
          warning: "",
        },
      },
    },
  })

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  it("passes input event up component tree", async () => {
    const input = wrapper.findComponent({ name: "q-input" })

    await input.setValue("test")
    expect(wrapper.emitted("update:modelValue")).toBeTruthy()
    expect(wrapper.emitted("update:modelValue")[0]).toEqual(["test"])
  })

  it("input has new-password auto-complete attr", () => {
    const input = wrapper.find("input")

    expect(input.attributes("autocomplete")).toEqual("new-password")
  })

  it("shows and hides details on click with correct aria", async () => {
    expect(wrapper.findAll(".password-details").length).toBe(0)

    const detailsChip = wrapper.findComponent({ name: "q-chip" })
    expect(detailsChip.attributes("aria-expanded")).toEqual("false")
    expect(detailsChip.attributes("aria-controls").length).toBeGreaterThan(0)
    await detailsChip.trigger("click")
    expect(detailsChip.attributes("aria-expanded")).toEqual("true")
    expect(wrapper.findAll(".password-details").length).toBe(1)
  })
})
