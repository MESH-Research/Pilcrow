import { mount } from "vue-composable-tester"
import { createMockClient } from "mock-apollo-client"
import { useCurrentUser, useLogin } from "./user"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { CURRENT_USER } from "src/graphql/queries"
import { LOGIN } from "src/graphql/mutations"
import { provide } from "@vue/composition-api"
import * as Q from "quasar"

jest.mock("quasar", () => ({
  SessionStorage: {
    remove: jest.fn(),
    getItem: jest.fn(),
  },
}))
const getItem = Q.SessionStorage.getItem

import Vue from "vue"
import { nextTick } from "vue"
Vue.config.productionTip = false
Vue.config.devtools = false

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

  test("when a user is not logged in", () => {
    const { result } = mountComposable([
      [
        CURRENT_USER,
        jest.fn().mockResolvedValue({ data: { currentUser: null } }),
      ],
    ])

    expect(result.currentUser.value).toBeNull()
    expect(result.isLoggedIn.value).toBe(false)
    expect(result.can.value("doSomething")).toBe(false)
    expect(result.hasRole.value("someRole")).toBe(false)
  })

  test("when a user is logged in", async () => {
    const response = {
      data: {
        currentUser: {
          id: 1,
          name: "Hello",
          email: "hello@example.com",
          roles: ["tester"],
          abilities: ["doStuff"],
        },
      },
    }

    const { result } = mountComposable([
      [CURRENT_USER, jest.fn().mockResolvedValue(response)],
    ])
    await nextTick()

    expect(result.currentUser.value).not.toBeNull()
    expect(result.isLoggedIn.value).toBe(true)
    expect(result.can.value("doSomething")).toBe(false)
    expect(result.hasRole.value("someRole")).toBe(false)
    expect(result.can.value("doStuff")).toBe(true)
    expect(result.hasRole.value("tester")).toBe(true)
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

    expect(result.loginUser()).rejects.toThrow("FORM_VALIDATION")

    expect(result.$v.value.email.required.$invalid).toBe(true)
    expect(result.$v.value.password.required.$invalid).toBe(true)

    result.$v.value.email.$model = "test"
    await nextTick()
    expect(result.$v.value.email.email.$invalid).toBe(true)
    expect(result.$v.value.email.required.$invalid).toBe(false)

    result.$v.value.email.$model = "test@example.com"
    result.$v.value.password.$model = "password"
    await nextTick()

    expect(result.$v.value.$error).toBe(false)
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

    result.$v.value.email.$model = "test@example.com"
    result.$v.value.password.$model = "password"

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
