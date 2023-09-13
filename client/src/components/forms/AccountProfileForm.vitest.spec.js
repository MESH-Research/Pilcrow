import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { useFormState } from "src/use/forms"
import { ref } from "vue"
import AccountProfileForm from "./AccountProfileForm.vue"

import { describe, expect, test } from 'vitest'




installQuasarPlugin()
describe("AccountProfileForm", () => {
  const makeWrapper = () => {
    return mount(AccountProfileForm, {
      global: {
        provide: {
          formState: useFormState({ loading: ref(false) }, { loading: ref(false) }),
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
      .findComponent({ ref: "emailInput" })
      .findComponent({ name: "q-input" })
      .setValue("testemail@example.com")

    await wrapper.findComponent({ name: "q-form" }).trigger("submit")
    await flushPromises()
    expect(wrapper.emitted("save")).toHaveLength(1)
  })
})
