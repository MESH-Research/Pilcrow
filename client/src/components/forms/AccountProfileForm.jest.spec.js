import AccountProfileForm from "./AccountProfileForm.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ref as mockRef } from "vue"
import { useFormState } from "src/use/forms"

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
    expect(wrapper.emitted("save")).toHaveLength(1)
  })
})
