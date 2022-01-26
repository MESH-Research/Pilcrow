import { mount } from "@vue/test-utils"
import {
  installQuasarPlugin,
  qLayoutInjections,
} from "@quasar/quasar-app-extension-testing-unit-jest"
import VerifyEmailPage from "./VerifyEmail.vue"
import { useRoute } from "vue-router"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import { VERIFY_EMAIL } from "src/graphql/mutations"
jest.mock("src/use/user", () => ({
  useCurrentUser: jest.fn(),
}))

jest.mock("vue-router", () => ({
  useRoute: jest.fn(),
}))

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    t: (t) => t,
  }),
}))

installQuasarPlugin()
describe("VerifyEmailPage", () => {
  const mockClient = createMockClient()
  const createWrapper = async () => {
    const wrapper = mount(VerifyEmailPage, {
      global: {
        provide: {
          ...qLayoutInjections(),
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
        },
        stubs: ["router-link"],
      },
    })
    await flushPromises()
    return wrapper
  }
  const verifyHandler = jest.fn()
  mockClient.setRequestHandler(VERIFY_EMAIL, verifyHandler)

  beforeEach(() => {
    jest.resetAllMocks()
  })

  it("mounts without errors", async () => {
    useRoute.mockReturnValue({
      params: { token: "", expires: "" },
    })
    useCurrentUser.mockReturnValue({
      currentUser: ref({ email_verified_at: null }),
    })
    const wrapper = await createWrapper()

    expect(wrapper).toBeTruthy()
  })

  test("renders success immediately if email is already verified", async () => {
    useRoute.mockReturnValue({
      params: { token: "", expires: "" },
    })

    useCurrentUser.mockReturnValue({
      currentUser: ref({ email_verified_at: "some value" }),
    })
    const wrapper = await createWrapper()
    expect(wrapper.vm.status).toBe("success")
    expect(wrapper.text()).toContain(
      "account.email_verify.verification_success"
    )
  })

  test("renders success", async () => {
    //Apollo throws error upon refetching the currentUser query (which is mocked out)
    const warn = jest.spyOn(console, "warn").mockImplementation(() => {})

    verifyHandler.mockResolvedValue({
      data: { verifyEmail: { email_verified_at: "timestamp" } },
    })

    useRoute.mockReturnValue({
      params: { token: "", expires: "" },
    })
    useCurrentUser.mockReturnValue({
      currentUser: ref({ email_verified_at: null }),
    })
    const wrapper = await createWrapper()

    expect(verifyHandler).toHaveBeenCalledWith({ token: "", expires: "" })
    expect(wrapper.vm.status).toBe("success")
    expect(wrapper.text()).toContain(
      "account.email_verify.verification_success"
    )
    expect(warn).toBeCalledTimes(1)
    expect(warn).toBeCalledWith(
      expect.stringContaining('Unknown query named "currentUser"')
    )
  })

  it("renders errors", async () => {
    verifyHandler.mockResolvedValue({
      errors: [
        {
          extensions: { code: "TEST_ERROR_CODE" },
        },
      ],
    })

    useRoute.mockReturnValue({
      params: { token: "", expires: "" },
    })

    useCurrentUser.mockReturnValue({
      currentUser: ref({ email_verified_at: null }),
    })

    const wrapper = await createWrapper()

    expect(wrapper.vm.status).toBe("failure")
    const errorUl = wrapper.find("ul.errors")

    expect(errorUl.text()).toContain("TEST_ERROR_CODE")
  })
})
