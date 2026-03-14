import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { VERIFY_EMAIL } from "src/graphql/mutations"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import { useRoute } from "vue-router"
import VerifyEmailPage from "./VerifyEmail.vue"

import type { VerifyEmailMutation } from "src/graphql/generated/graphql"
import { type Mock, beforeEach, describe, expect, it, test, vi } from "vitest"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn()
}))

vi.mock("vue-router", () => ({
  useRoute: vi.fn()
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("VerifyEmailPage", () => {
  const createWrapper = async () => {
    const wrapper = mount(VerifyEmailPage, {
      global: {
        stubs: ["router-link"]
      }
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
    ;(useRoute as Mock).mockReturnValue({
      params: { token: "", expires: "" }
    })
    ;(useCurrentUser as Mock).mockReturnValue({
      currentUser: ref({ email_verified_at: null })
    })
    const wrapper = await createWrapper()

    expect(wrapper).toBeTruthy()
  })

  test("renders success immediately if email is already verified", async () => {
    ;(useRoute as Mock).mockReturnValue({
      params: { token: "", expires: "" }
    })
    ;(useCurrentUser as Mock).mockReturnValue({
      currentUser: ref({ email_verified_at: "some value" })
    })
    const wrapper = await createWrapper()
    expect((wrapper.vm as any).status).toBe("success")
    expect(wrapper.text()).toContain(
      "account.email_verify.verification_success"
    )
  })

  test("renders success", async () => {
    const mockVerifyResponse: { data: VerifyEmailMutation } = {
      data: { verifyEmail: { id: "1", email_verified_at: "timestamp" } }
    }
    verifyHandler.mockResolvedValue(mockVerifyResponse)
    ;(useRoute as Mock).mockReturnValue({
      params: { token: "", expires: "" }
    })
    ;(useCurrentUser as Mock).mockReturnValue({
      currentUser: ref({ email_verified_at: null })
    })
    const wrapper = await createWrapper()
    expect(verifyHandler).toHaveBeenCalledWith({ token: "", expires: "" })
    expect((wrapper.vm as any).status).toBe("success")
    expect(wrapper.text()).toContain(
      "account.email_verify.verification_success"
    )
  })

  it("renders errors", async () => {
    verifyHandler.mockResolvedValue({
      errors: [
        {
          extensions: { code: "TEST_ERROR_CODE" }
        }
      ]
    })
    ;(useRoute as Mock).mockReturnValue({
      params: { token: "", expires: "" }
    })
    ;(useCurrentUser as Mock).mockReturnValue({
      currentUser: ref({ email_verified_at: null })
    })
    const wrapper = await createWrapper()
    expect((wrapper.vm as any).status).toBe("failure")
    const errorUl = wrapper.find("ul[data-cy=errors")
    expect(errorUl.text()).toContain("TEST_ERROR_CODE")
  })
})
