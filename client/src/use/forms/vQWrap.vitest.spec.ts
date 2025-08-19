import { mount } from "@vue/test-utils"
import { useVQWrap } from "./vQWrap"
import { describe, test, expect, vi } from "vitest"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { defineComponent } from "vue"

installQuasarPlugin()

describe("useVQWrap composable", () => {
  const validator = {
    $model: "",
    $path: "testField"
  }

  const factory = (path = false) =>
    defineComponent({
      setup() {
        return {
          ...useVQWrap(validator, path)
        }
      },
      render() {
        return ""
      }
    })

  test("without vqwrap provides present", () => {
    const wrapper = mount(factory())

    expect(wrapper.vm.getTranslationKey("label")).toBe("testField.label")

    wrapper.vm.model = "new Value"
    expect(wrapper.emitted().vqupdate).toHaveLength(1)
    expect(wrapper.emitted().vqupdate[0][1]).toBe("new Value")
  })

  test("update function provider", () => {
    const mockUpdate = vi.fn()

    const wrapper = mount(factory(), {
      global: {
        provide: {
          vqupdate: mockUpdate
        }
      }
    })

    wrapper.vm.model = "parent update"
    expect(mockUpdate).toHaveBeenCalledTimes(1)
    expect(mockUpdate).toHaveBeenCalledWith(validator, "parent update")
  })

  test("provided prefix is used", () => {
    const wrapper = mount(factory(), {
      global: {
        provide: {
          tPrefix: "parentPrefix"
        }
      }
    })

    expect(wrapper.vm.getTranslation("error")).toBe(
      "parentPrefix.testField.error"
    )
  })

  test("local translation path overrides prefix", () => {
    const wrapper = mount(factory("localPath"), {
      global: {
        provide: {
          tPrefix: "parentPrefix"
        }
      }
    })

    expect(wrapper.vm.getTranslation("error")).toBe("localPath.error")
  })
})
