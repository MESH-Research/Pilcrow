import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { useFormState } from "src/use/forms"
import { ref as mockRef } from "vue"
import AccountProfileForm from "./AccountProfileForm.vue"

jest.mock("src/use/forms", () => ({
  ...jest.requireActual("src/use/forms"),
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
describe("AccountProfileForm", () => {
  const makeWrapper = () => {
    return mount(AccountProfileForm, {
      global: {
        mocks: {
          $t: (t) => t,
        },
        provide: {
          formState: useFormState(),
        },
      },
      props: {
        accountProfile: {},
      },
    })
  }

  test("able to save", async () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
    //Validation rules are tested as part userValidation, we only need to confirm that password can be empty.

    wrapper
      .findComponent({ ref: "usernameInput" })
      .findComponent({ name: "q-input" })
      .setValue("testuser")
    wrapper
      .findComponent({ ref: "nameInput" })
      .findComponent({ name: "q-input" })
      .setValue("Test User")
    wrapper
      .findComponent({ ref: "emailInput" })
      .findComponent({ name: "q-input" })
      .setValue("testemail@example.com")

    await wrapper.findComponent({ name: "q-form" }).trigger("submit")
    await flushPromises()
    expect(wrapper.emitted("save")).toHaveLength(1)
  })
})
