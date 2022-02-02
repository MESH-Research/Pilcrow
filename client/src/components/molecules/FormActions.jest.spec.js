import FormActions from "./FormActions.vue"
import { mount } from "@vue/test-utils"
import {
  installQuasarPlugin,
  qLayoutInjections,
} from "@quasar/quasar-app-extension-testing-unit-jest"
import { nextTick, ref as mockRef } from "vue"
import { useFormState } from "src/use/forms"

jest.mock("src/use/forms", () => ({
  useDirtyGuard: () => {},
  useFormState: () => ({
    dirty: mockRef(false),
    saved: mockRef(false),
    state: mockRef("idle"),
    queryLoading: mockRef(false),
    mutationLoading: mockRef(false),
    errorMessage: mockRef(""),
  }),
}))

installQuasarPlugin()
describe("Formactions", () => {
  const factory = (props = {}) => {
    return mount(FormActions, {
      global: {
        provide: {
          ...qLayoutInjections(),
          formState: useFormState(),
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
    const wrapper = factory()
    wrapper.vm.state = "idle"
    expect(wrapper.findComponent({ name: "q-page-sticky" }).exists()).toBe(
      false
    )
  })

  test("dirty form state", async () => {
    const wrapper = factory()
    wrapper.vm.state = "dirty"
    await nextTick()
    expect(wrapper.findComponent({ name: "q-page-sticky" }).exists()).toBe(true)
  })
})
