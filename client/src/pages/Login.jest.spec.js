import LoginPage from "./Login.vue"
import { describe, expect, it } from "@jest/globals"
import {
  installQuasarPlugin,
  qLayoutInjections,
} from "@quasar/quasar-app-extension-testing-unit-jest"
import { mount } from "@vue/test-utils"
import { LOGIN } from "src/graphql/mutations"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import quasar from "quasar"
import flushPromises from "flush-promises"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  SessionStorage: {
    remove: jest.fn(),
    getItem: jest.fn(),
  },
}))
jest.mock("vue-router", () => ({
  useRouter: () => ({
    push: jest.fn(),
  }),
}))

const mockSessionItem = jest.fn()
quasar.SessionStorage.getItem = mockSessionItem

installQuasarPlugin()

describe("LoginPage", () => {
  beforeEach(() => {
    jest.resetAllMocks()
  })
  const mockClient = createMockClient()

  const wrapperFactory = () =>
    mount(LoginPage, {
      global: {
        provide: {
          ...qLayoutInjections(),
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (token) => token,
        },
        stubs: ["router-link"],
      },
    })

  const mutationHandler = jest.fn()

  mockClient.setRequestHandler(LOGIN, mutationHandler)

  it("mounts without errors", () => {
    const wrapper = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  test("login action attempts mutation", async () => {
    const wrapper = wrapperFactory()
    mutationHandler.mockResolvedValue({
      data: { login: { id: 1 } },
    })
    wrapper.findComponent({ ref: "username" }).setValue("user@example.com")
    wrapper.findComponent({ ref: "password" }).setValue("password")
    await wrapper.findComponent({ ref: "submitBtn" }).trigger("submit")
    await flushPromises()

    expect(mutationHandler).toBeCalled()
    expect(wrapper.vm.push).toHaveBeenCalledTimes(1)
    expect(wrapper.vm.push).toHaveBeenCalledWith("/dashboard")
  })

  test("login redirects correctly", async () => {
    mutationHandler.mockResolvedValue({
      data: { login: { id: 1 } },
    })
    mockSessionItem.mockReturnValue("/test-result")
    const wrapper = wrapperFactory()
    wrapper.findComponent({ ref: "username" }).setValue("user@example.com")
    wrapper.findComponent({ ref: "password" }).setValue("password")
    await wrapper.findComponent({ ref: "submitBtn" }).trigger("submit")
    await flushPromises()

    expect(mutationHandler).toBeCalled()
    expect(wrapper.vm.push).toHaveBeenCalledTimes(1)
    expect(wrapper.vm.push).toHaveBeenCalledWith("/test-result")
  })
})
