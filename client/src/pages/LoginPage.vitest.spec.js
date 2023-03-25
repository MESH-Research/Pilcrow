import {
  installQuasarPlugin,
} from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount, flushPromises } from "@vue/test-utils"
import { createMockClient } from "test/vitest/apolloClient"
import { SessionStorage } from "quasar"
import { LOGIN } from "src/graphql/mutations"
import { beforeEach, describe, expect, it, test, vi } from "vitest"
import LoginPage from "./LoginPage.vue"

const mockSessionItem = vi.spyOn(SessionStorage, 'getItem').mockImplementation(() => vi.fn())
vi.spyOn(SessionStorage, 'remove').mockImplementation(() => vi.fn())

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}))

installQuasarPlugin()

describe("LoginPage", () => {
  beforeEach(() => {
    vi.resetAllMocks()
  })
  const mockClient = createMockClient()

  const wrapperFactory = () =>
    mount(LoginPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        stubs: ["router-link"],
      },
    })

  const mutationHandler = vi.fn()

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

    expect(mutationHandler).toHaveBeenCalled()
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

    expect(mutationHandler).toHaveBeenCalled()
    expect(wrapper.vm.push).toHaveBeenCalledTimes(1)
    expect(wrapper.vm.push).toHaveBeenCalledWith("/test-result")
  })
})
