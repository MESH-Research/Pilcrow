import FormActions from "./FormActions.vue"
import { mount } from "@vue/test-utils"
import {
  installQuasarPlugin,
  qLayoutInjections,
} from "@quasar/quasar-app-extension-testing-unit-jest"

installQuasarPlugin()
describe("Formactions", () => {
  const factory = (props = {}) => {
    return mount(FormActions, {
      global: {
        provide: {
          ...qLayoutInjections(),
        },
        mocks: {
          $t: (token) => token,
        },
      },
      propsData: {
        ...props,
      },
    })
  }
  test("wrapper is valid", async () => {
    const wrapper = factory()

    expect(wrapper).toBeTruthy()
  })

  test("hidden when form is idle", () => {
    const wrapper = factory({ formState: "idle" })
    expect(wrapper.findComponent({ name: "q-page-sticky" }).exists()).toBe(
      false
    )
  })

  test("dirty form state", async () => {
    const wrapper = factory({ formState: "dirty" })
    expect(wrapper.findComponent({ name: "q-page-sticky" }).exists()).toBe(true)
  })
})
