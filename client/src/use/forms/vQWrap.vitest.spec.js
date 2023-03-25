import { mount } from "@vue/test-utils"
import {
  useVQWrap,
} from "./vQWrap"
import { describe, test, expect, vi } from 'vitest'
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { defineComponent } from 'vue'

installQuasarPlugin()


describe("useVQWrap composable", () => {
  const validator = {
    $model: "",
    $path: "testField",
  }

  const factory = (path = false) => defineComponent({
    setup() {
      return {
        ...useVQWrap(validator, path)
      }
    }
  })

  test("without vqwrap provides present", () => {

    const wrapper = mount(factory())

    expect(wrapper.vm.getTranslationKey("label")).toBe("testField.label")

    wrapper.vm.model = "new Value"
    expect(wrapper.emitted().vqupdate).toHaveLength(1)
    expect(wrapper.emitted().vqupdate[0][1]).toEqual("new Value")
  })

  test("update function provider", () => {
    const mockUpdate = vi.fn()


    const wrapper = mount(factory(), {
      global: {
        provide: {
          vqupdate: mockUpdate
        }
      },
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

    expect(wrapper.vm.getTranslation("error")).toBe("parentPrefix.testField.error")
  })

  test("local translation path overrides prefix", () => {
    const wrapper = mount(factory('localPath'), {
      global: {
        provide: {
          tPrefix: "parentPrefix"

        }
      }
      })

    expect(wrapper.vm.getTranslation("error")).toBe("localPath.error")
  })
})

// describe("useGraphQLValidation composable", () => {
//   const error = ref({
//     graphQLErrors: [
//       {
//         message: "Validation failed for the field [updateUser].",
//         extensions: {
//           validation: {
//             "user.username": ["USERNAME_IN_USE"],
//           },
//           category: "validation",
//         },
//         locations: [
//           {
//             line: 2,
//             column: 3,
//           },
//         ],
//         path: ["updateUser"],
//       },
//       {
//         message: "Something else happened",
//       },
//     ],
//     clientErrors: [],
//     networkError: null,
//     message: "Validation failed for the field [updateUser].",
//   })

//   test("correctly extracts only validation errors", () => {
//     const { result } = mount(() => useGraphQLValidation(error))

//     const { hasValidationErrors, validationErrors } = result

//     expect(hasValidationErrors.value).toBe(true)
//     expect(validationErrors.value).toEqual({
//       user: { username: ["USERNAME_IN_USE"] },
//     })

//     error.value = {}

//     expect(hasValidationErrors.value).toBe(false)
//     expect(validationErrors.value).toEqual({})
//   })
// })
