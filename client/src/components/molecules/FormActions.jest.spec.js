import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import FormActions from "./FormActions"
import { QBtn, QPageSticky, QIcon, QSpinner } from "quasar"
import { nextTick } from "vue"

const factory = (props = {}) => {
  return mountQuasar(FormActions, {
    quasar: { components: { QBtn, QPageSticky, QIcon, QSpinner } },
    mount: {
      type: "full",
    },
    propsData: {
      ...props,
    },
  })
}

describe("Formactions", () => {
  test("wrapper is valid", () => {
    const wrapper = factory({ formState: "idle" })
    expect(wrapper).toBeTruthy()
  })

  test("hidden when form is idle", () => {
    const wrapper = factory({ formState: "idle" })
    expect(wrapper.findComponent(QPageSticky).exists()).toBe(false)
  })

  test("dirty form state", async () => {
    const wrapper = factory({ formState: "dirty" })
    await nextTick()
    console.log(wrapper.vm.$children)
    console.log(wrapper.findComponent(QPageSticky))
    expect(wrapper.findComponent(QPageSticky).exists()).toBe(true)
  })
})
