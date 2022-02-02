import ProfileMetadataForm from "./ProfileMetadataForm.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { useFormState } from "src/use/forms"
import flushPromises from "flush-promises"
import { ref as mockRef } from "vue"

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
describe("ProfileMetadataForm", () => {
  const makeWrapper = (props = {}) => {
    return mount(ProfileMetadataForm, {
      global: {
        mocks: {
          $t: (t) => t,
        },
        provide: {
          formState: useFormState(),
        },
      },
      props: {
        profileMetadata: {},
        ...props,
      },
    })
  }

  test("able to mount", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test.concurrent.each([
    ["maxLength", "professionalTitle", "1".repeat(257), false],
    ["valid", "professionalTitle", "a reasonable valid", true],
    ["maxLength", "specialization", "1".repeat(257), false],
    ["valid", "specialization", "a reasonable valid", true],
    ["maxLength", "affiliation", "1".repeat(257), false],
    ["valid", "affiliation", "a reasonable valid", true],
    ["maxLength", "biography", "1".repeat(4097), false],
    ["valid", "biography", "a reasonable valid", true],
    ["invalid", "facebook", "not@profile", false],
    ["valid", "facebook", "myprofile", true],
    ["maxlength", "facebook", "1".repeat(129), false],
    ["invalid", "twitter", "not@profile", false],
    ["valid", "twitter", "myprofile", true],
    ["maxlength", "twitter", "1".repeat(129), false],
    ["invalid", "instagram", "not@profile", false],
    ["valid", "instagram", "myprofile", true],
    ["maxlength", "instagram", "1".repeat(129), false],
    ["invalid", "linkedin", "not@profile", false],
    ["valid", "linkedin", "myprofile", true],
    ["maxlength", "linkedin", "1".repeat(129), false],
    ["valid", "orcid", "myprofile", true],
    ["maxlength", "orcid", "1".repeat(129), false],
    ["valid", "academia_edu_id", "myprofile", true],
    ["maxlength", "academia_edu_id", "1".repeat(129), false],
    ["valid", "humanities_commons", "myprofile", true],
    ["maxlength", "humanities_commons", "1".repeat(129), false],
  ])(
    "test validation rules %s (%s)",
    async (_, fieldRef, testValue, assertValid) => {
      const wrapper = makeWrapper()

      const field = wrapper
        .findComponent({ ref: fieldRef })
        .findComponent({ name: "q-input" })
      expect(field).toBeTruthy()
      await field.setValue(testValue)
      await wrapper.findComponent({ name: "q-form" }).trigger("submit")
      expect(wrapper.emitted().submit).toBeDefined()
      expect(wrapper.emitted()?.save?.length).toBe(assertValid ? 1 : undefined)
    }
  )

  test.concurrent.each([
    ["regex", "websites", ["http://localhost"], false],
    ["valid", "websites", ["http://mywebsite.com"], true],
    ["valid", "interest_keywords", ["keyword1", "keyword2"], true],
    ["valid", "disinterest_keywords", ["keyword1", "keyword2"], true],
  ])(
    "test array field rules %s (%s)",
    async (_, fieldRef, testValue, assertValid) => {
      const wrapper = makeWrapper()

      const field = wrapper.findComponent({ ref: fieldRef })
      expect(field).toBeTruthy()
      await field.setValue(testValue)
      await wrapper.findComponent({ name: "q-form" }).trigger("submit")
      expect(wrapper.emitted().submit).toBeDefined()
      expect(wrapper.emitted()?.save?.length).toBe(assertValid ? 1 : undefined)
    }
  )

  test.each([
    ["facebook", "https://www.facebook.com/my.profile", "my.profile"],
    ["twitter", "https://twitter.com/my_profile", "my_profile"],
    ["instagram", "https://instagr.am/my_user", "my_user"],
    ["linkedin", "https://linkedin.com/in/my_profile", "my_profile"],
  ])("test url paste feature: %s", async (fieldRef, url, result) => {
    const wrapper = makeWrapper()

    const field = wrapper
      .findComponent({ ref: fieldRef })
      .findComponent({ name: "q-input" })

    await field.setValue(url)
    await flushPromises()

    expect(field.vm.modelValue).toBe(result)
  })
})
