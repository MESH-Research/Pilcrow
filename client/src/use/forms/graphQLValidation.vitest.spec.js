import { useGraphQLValidation } from "./graphQLValidation"
import { describe, test, expect } from "vitest"
import { ref } from "vue"

describe("useGraphQLValidation composable", () => {
  const error = ref({
    graphQLErrors: [
      {
        message: "Validation failed for the field [updateUser].",
        extensions: {
          validation: {
            "user.username": ["USERNAME_IN_USE"]
          },
          category: "validation"
        },
        locations: [
          {
            line: 2,
            column: 3
          }
        ],
        path: ["updateUser"]
      },
      {
        message: "Something else happened"
      }
    ],
    clientErrors: [],
    networkError: null,
    message: "Validation failed for the field [updateUser]."
  })

  test("correctly extracts only validation errors", () => {
    const result = useGraphQLValidation(error)

    const { hasValidationErrors, validationErrors } = result

    expect(hasValidationErrors.value).toBe(true)
    expect(validationErrors.value).toEqual({
      user: { username: ["USERNAME_IN_USE"] }
    })

    error.value = {}

    expect(hasValidationErrors.value).toBe(false)
    expect(validationErrors.value).toEqual({})
  })
})
