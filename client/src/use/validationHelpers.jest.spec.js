import * as validationHelpers from "./validationHelpers"
import { mount } from "vue-composable-tester"
import { nextTick, provide, reactive } from "@vue/composition-api"
import useVuelidate from "@vuelidate/core"
import { required } from "@vuelidate/validators"
import Vue from "vue"
Vue.config.productionTip = false
Vue.config.devtools = false

describe("test validation helpers", () => {
  test("getErrorMessageKey function", () => {
    const getErrorMessageKey = validationHelpers.getErrorMessageKey
    expect(
      getErrorMessageKey({
        $validator: "$externalResults",
        $message: "externalMessage",
      })
    ).toEqual("externalMessage")
    expect(
      getErrorMessageKey({ $validator: "required", $message: "messgage" })
    ).toEqual("required")
  })

  test("hasErrorKeyFunction", () => {
    const errors = []
    const hasErrorKey = validationHelpers.hasErrorKey
    errors.push({ $validator: "$externalResults", $message: "externalError" })
    errors.push({ $validator: "required", $message: "requires field" })

    expect(hasErrorKey(errors, "externalError")).toBe(true)
    expect(hasErrorKey(errors, "required")).toBe(true)
    expect(hasErrorKey(errors, "requires field")).toBe(false)
    expect(hasErrorKey(errors, "anotherError")).toBe(false)
  })
})

describe("test composables", () => {
  test("hasErrorKey computed property", async () => {
    const { result } = mount(() => {
      const fields = reactive({ one: "", two: "" })
      const $v = useVuelidate({ one: { required }, two: {} }, fields)
      provide("validator", $v)
      return {
        hasErrorKey: validationHelpers.useHasErrorKey(),
        $v,
        fields,
      }
    })
    await result.$v.value.$touch()
    expect(result.hasErrorKey.value("one", "required")).toBe(true)
    expect(result.hasErrorKey.value("two", "required")).toBe(false)
    expect(result.hasErrorKey.value("one", "odd")).toBe(false)
    result.fields.one = "hello"

    await result.$v.value.$touch()
    expect(result.hasErrorKey.value("one", "required")).toBe(false)
  })
})

describe("applyExternalValidationErrors composition helper", () => {
  const factory = () => {
    return mount(() => {
      const data = reactive({ one: "", two: "" })
      const eData = reactive({ one: [], two: [] })

      const applyErrors = (error, strip = "") => {
        return validationHelpers.applyExternalValidationErrors(
          data,
          eData,
          error,
          strip
        )
      }
      return { data, eData, applyErrors }
    }).result
  }

  test("single error strip prefix", async () => {
    const result = factory()
    const returnValue = result.applyErrors(
      {
        graphQLErrors: [
          {
            extensions: {
              validation: {
                "user.one": ["error1", "error2"],
              },
            },
          },
        ],
      },
      /^user\./
    )
    expect(returnValue).toBe(true)
    expect(result.eData.one.length).toBe(2)
    expect(result.eData.two.length).toBe(0)

    result.data.one = "one"
    await nextTick()

    expect(result.eData.one.length).toBe(0)
  })
  test("no validtion errors", () => {
    const result = factory()
    const returnValue = result.applyErrors({
      graphQLErrors: [
        {
          extentions: {
            authentication: "Failed",
          },
        },
      ],
    })

    expect(returnValue).toBe(false)
    expect(result.eData.one.length).toBe(0)
    expect(result.eData.two.length).toBe(0)
  })
  test("multiple validation errors", async () => {
    // Note: This probably doesn't occur in the wild, but the structure suggests that it may, so... here we are.

    const result = factory()
    const returnValue = result.applyErrors({
      graphQLErrors: [
        {
          extensions: {
            validation: {
              one: ["error1", "error2"],
            },
          },
        },
        {
          extensions: {
            validation: {
              two: ["error1"],
            },
          },
        },
      ],
    })

    expect(returnValue).toBe(true)
    expect(result.eData.one.length).toBe(2)
    expect(result.eData.two.length).toBe(1)
  })

  test("multiple different errors", async () => {
    const result = factory()
    const returnValue = result.applyErrors({
      graphQLErrors: [
        {
          extensions: {
            validation: {
              one: ["error1", "error2"],
            },
          },
        },
        {
          extentions: {
            authentication: "Failed",
          },
        },
      ],
    })

    expect(returnValue).toBe(true)
    expect(result.eData.one.length).toBe(2)
    expect(result.eData.two.length).toBe(0)
  })
})
