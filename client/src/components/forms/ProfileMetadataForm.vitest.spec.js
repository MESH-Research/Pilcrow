import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { useFormState } from "src/use/forms"
import ProfileMetadataForm from "./ProfileMetadataForm.vue"
import { ref } from 'vue'
import { QList } from 'quasar'

import { describe, expect, test, vi } from "vitest"

vi.mock('vue-router');

installQuasarPlugin()

describe("ProfileMetadataForm", () => {
  const makeWrapper = (props = {}) => {
    return mount(ProfileMetadataForm, {
      global: {
        provide: {
          formState: useFormState({ loading: ref(false) }, { loading: ref(false) }),
        },
        components: { QList }
      },
      props: {
        profileMetadata: {
          username: 'testusername',
          name: 'Test Name',
        },
        ...props,
      },
    })
  }

  test("able to mount", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test.concurrent.each([
    ["maxLength", "positionTitle", "1".repeat(257), false],
    ["valid", "positionTitle", "a reasonable valid", true],
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
    ["valid", "orcid_id", "myprofile", true],
    ["maxlength", "orcid_id", "1".repeat(129), false],
    ["valid", "humanities_commons", "myprofile", true],
    ["maxlength", "humanities_commons", "1".repeat(129), false],
  ])(
    "validation rules %s (%s)",
    async (_, fieldRef, testValue, assertValid) => {
      const wrapper = makeWrapper()

      const field = wrapper
        .findComponent({ ref: fieldRef })
        .findComponent({ name: "q-input" })
      expect(field).toBeTruthy()
      await field.setValue(testValue)
      await wrapper.findComponent({ name: "q-form" }).trigger("submit")
      await flushPromises()
      expect(wrapper.emitted().submit).toBeDefined()
      expect(wrapper.emitted()?.save?.length).toBe(assertValid ? 1 : undefined)
    }
  )

  test.concurrent.each([
    ["regex", "websites", ["http://localhost"], false],
    ["valid", "websites", ["http://mywebsite.com"], true],
    ["valid", "interest_keywords", ["keyword1", "keyword2"], true],
  ])(
    "array field rules %s (%s)",
    async (_, fieldRef, testValue, assertValid) => {
      const wrapper = makeWrapper()

      const field = wrapper.findComponent({ ref: fieldRef })
      expect(field).toBeTruthy()
      await field.setValue(testValue)
      await wrapper.findComponent({ name: "q-form" }).trigger("submit")
      await flushPromises()
      expect(wrapper.emitted().submit).toBeDefined()
      expect(wrapper.emitted()?.save?.length).toBe(assertValid ? 1 : undefined)
    }
  )

  test.each([
    ["facebook", "https://www.facebook.com/my.profile", "my.profile"],
    ["twitter", "https://twitter.com/my_profile", "my_profile"],
    ["instagram", "https://instagr.am/my_user", "my_user"],
    ["linkedin", "https://linkedin.com/in/my_profile", "my_profile"],
  ])("url paste feature: %s", async (fieldRef, url, result) => {
    const wrapper = makeWrapper()

    const field = wrapper
      .findComponent({ ref: fieldRef })
      .findComponent({ name: "q-input" })

    await field.setValue(url)
    await flushPromises()

    expect(field.vm.modelValue).toBe(result)
  })
})
