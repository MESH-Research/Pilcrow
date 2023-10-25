import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "test/vitest/utils"
import { VERIFY_EMAIL } from "src/graphql/mutations"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import { useRoute } from "vue-router"
import VerifyEmailPage from "./VerifyEmail.vue"

import { beforeEach, describe, expect, it, test, vi } from "vitest"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

vi.mock("vue-router", () => ({
  useRoute: vi.fn(),
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("VerifyEmailPage", () => {
  const createWrapper = async () => {
    const wrapper = mount(VerifyEmailPage, {
      global: {
        stubs: ["router-link"],
      },
    })
    await flushPromises()
    return wrapper
  }
  const verifyHandler = vi.fn()
  mockClient.setRequestHandler(VERIFY_EMAIL, verifyHandler)

  beforeEach(() => {
    vi.resetAllMocks()
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
      "account.email_verify.verification_success",
    )
  })

  test("renders success", async () => {
    //Apollo throws error upon refetching the currentUser query (which is mocked out)
    const warn = vi.spyOn(console, "warn").mockImplementation(() => {})

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
      "account.email_verify.verification_success",
    )
    expect(warn).toHaveBeenCalledTimes(1)
    expect(warn).toHaveBeenCalledWith(
      expect.stringContaining("message%22%3A33%2C%22"),
    )
    expect(warn).toHaveBeenCalledWith(expect.stringContaining("currentUser"))
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
    const errorUl = wrapper.find("ul[data-cy=errors")
    expect(errorUl.text()).toContain("TEST_ERROR_CODE")
  })
})
