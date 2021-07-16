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

  test("applyExternalValidationErrors composition helper", async () => {
    const { result } = mount(() => {
      const data = reactive({ one: "", two: "" })
      const eData = reactive({ one: [], two: [] })

      const applyErrors = (error) => {
        validationHelpers.applyExternalValidationErrors(data, eData, error)
      }
      return { data, eData, applyErrors }
    })

    result.applyErrors({
      graphQLErrors: [
        {
          extensions: {
            validation: {
              one: ["error1", "error2"],
            },
          },
        },
      ],
    })

    expect(result.eData.one.length).toBe(2)
    expect(result.eData.two.length).toBe(0)

    result.data.one = "one"
    await nextTick()

    expect(result.eData.one.length).toBe(0)
  })
})
