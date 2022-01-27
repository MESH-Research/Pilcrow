import { mount } from "vue-composable-tester"
import { createMockClient } from "mock-apollo-client"
import { useCurrentUser, useLogin } from "./user"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { CURRENT_USER } from "src/graphql/queries"
import { LOGIN } from "src/graphql/mutations"
import { provide } from "vue"
import * as Q from "quasar"

jest.mock("quasar", () => ({
  SessionStorage: {
    remove: jest.fn(),
    getItem: jest.fn(),
  },
}))
const getItem = Q.SessionStorage.getItem

import flushPromises from "flush-promises"

describe("useCurrentUser composable", () => {
  const mountComposable = (mocks) => {
    const mockClient = createMockClient()
    mocks.forEach((m) => mockClient.setRequestHandler(...m))
    const { result } = mount(() => useCurrentUser(), {
      provider: () => {
        provide(DefaultApolloClient, mockClient)
      },
    })
    return { mockClient, result }
  }

  test("when a user is not logged in", async () => {
    const { result } = mountComposable([
      [
        CURRENT_USER,
        jest.fn().mockResolvedValue({ data: { currentUser: null } }),
      ],
    ])

    await flushPromises()
    expect(result.currentUser.value).toBeNull()
    expect(result.isLoggedIn.value).toBe(false)
  })

  test("when a user is logged in", async () => {
    const response = {
      data: {
        currentUser: {
          __typename: "User",
          id: 1,
          name: "Hello",
          email: "hello@example.com",
          username: "helloUser",
          email_verified_at: "2021-08-14 02:26:32",
          roles: [{ name: "tester" }],
          abilities: [{ name: "doStuff" }],
        },
      },
    }

    const { result } = mountComposable([
      [CURRENT_USER, jest.fn().mockResolvedValue(response)],
    ])
    await flushPromises()
    expect(result.currentUser.value).not.toBeNull()
    expect(result.isLoggedIn.value).toBe(true)
    //TODO: Implement a composable function to return roles and abilities booleans
  })
})

describe("useLogin composable", () => {
  const mountComposable = () => {
    const mockClient = createMockClient()
    const { result } = mount(() => useLogin(), {
      provider: () => {
        provide(DefaultApolloClient, mockClient)
      },
    })
    return { mockClient, result }
  }

  test("validates fields", async () => {
    const { result } = mountComposable()

    await expect(result.loginUser()).rejects.toThrow("FORM_VALIDATION")

    expect(result.v$.value.email.required.$invalid).toBe(true)
    expect(result.v$.value.password.required.$invalid).toBe(true)

    result.v$.value.email.$model = "test"
    await flushPromises()
    expect(result.v$.value.email.email.$invalid).toBe(true)
    expect(result.v$.value.email.required.$invalid).toBe(false)

    result.v$.value.email.$model = "test@example.com"
    result.v$.value.password.$model = "password"
    await flushPromises()

    expect(result.v$.value.$error).toBe(false)
  })

  test("throws exceptions", async () => {
    const { result, mockClient } = mountComposable()

    const mutateHandler = jest.fn()

    mutateHandler.mockResolvedValue({
      errors: [
        {
          message: "Invalid credentials supplied",
          extensions: {
            code: "CREDENTIALS_INVALID",
            category: "authentication",
          },
        },
      ],
    })
    mockClient.setRequestHandler(LOGIN, mutateHandler)

    result.v$.value.email.$model = "test@example.com"
    result.v$.value.password.$model = "password"

    await expect(result.loginUser()).rejects.toThrow("CREDENTIALS_INVALID")

    mutateHandler.mockReset().mockRejectedValue({})

    await expect(result.loginUser()).rejects.toThrow("INTERNAL")

    mutateHandler.mockReset().mockResolvedValue({
      errors: [
        {
          message: "Invalid credentials supplied",
          extensions: {
            code: "CREDENTIALS_INVALID",
            category: "authentication",
          },
        },
        {
          message: "Invalid credentials supplied",
          extensions: {
            code: "ANOTHER_ERROR",
            category: "authentication",
          },
        },
      ],
    })

    await expect(result.loginUser()).rejects.toThrow("MULTIPLE_ERROR_CODES")
  })

  test("fetches redirectUrl from session storage", () => {
    getItem.mockReturnValue("/redirect")
    let { result } = mountComposable()

    expect(result.redirectUrl).toEqual("/redirect")

    getItem.mockReset().mockReturnValue(null)
    ;({ result } = mountComposable())

    expect(result.redirectUrl).toEqual("/dashboard")
  })
})
