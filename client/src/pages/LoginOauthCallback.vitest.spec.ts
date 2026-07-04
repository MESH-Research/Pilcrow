import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import {
  LOGIN_OAUTH_CALLBACK,
  REGISTER_OAUTH_USER
} from "src/graphql/mutations"
import { CURRENT_USER } from "src/graphql/queries"
import { useRoute, useRouter } from "vue-router"
import { type Mock, beforeEach, describe, expect, it, test, vi } from "vitest"
import LoginOauthCallback from "./LoginOauthCallback.vue"

vi.mock("vue-router", () => ({
  useRoute: vi.fn(),
  useRouter: vi.fn()
}))

installQuasarPlugin()
const mockClient = installApolloClient()

const callbackResponse = (action: string) => ({
  data: {
    loginOauthCallback: {
      __typename: "OauthLoginResponse",
      action,
      user: {
        __typename: "User",
        name: "Ada Lovelace",
        username: "ada",
        email: "ada@example.com"
      },
      provider: {
        __typename: "UserProvider",
        provider_name: "orcid",
        provider_id: "0000-0001"
      }
    }
  }
})

describe("LoginOauthCallback", () => {
  const callbackHandler = vi.fn()
  const registerHandler = vi.fn()
  const currentUserHandler = vi.fn()
  const push = vi.fn()

  mockClient.setRequestHandler(LOGIN_OAUTH_CALLBACK, callbackHandler)
  mockClient.setRequestHandler(REGISTER_OAUTH_USER, registerHandler)
  mockClient.setRequestHandler(CURRENT_USER, currentUserHandler)

  beforeEach(() => {
    vi.resetAllMocks()
    ;(useRoute as Mock).mockReturnValue({ query: { code: "auth-code-123" } })
    ;(useRouter as Mock).mockReturnValue({ push })
    currentUserHandler.mockResolvedValue({
      data: { currentUser: null }
    })
  })

  const createWrapper = async (providerName = "orcid") => {
    const wrapper = mount(LoginOauthCallback, {
      props: { providerName },
      global: { stubs: ["router-link"] }
    })
    await flushPromises()
    return wrapper
  }

  it("mounts without errors", async () => {
    callbackHandler.mockResolvedValue(callbackResponse("register"))
    const wrapper = await createWrapper()
    expect(wrapper).toBeTruthy()
  })

  test.each(["orcid", "google"])(
    "passes the %s provider name and code to the callback mutation",
    async (providerName) => {
      callbackHandler.mockResolvedValue(callbackResponse("register"))
      await createWrapper(providerName)
      expect(callbackHandler).toHaveBeenCalledWith({
        provider_name: providerName,
        code: "auth-code-123"
      })
    }
  )

  test("renders the registration form when action is register", async () => {
    callbackHandler.mockResolvedValue(callbackResponse("register"))
    const wrapper = await createWrapper()
    expect((wrapper.vm as any).status).toBe("loaded")
    expect((wrapper.vm as any).action).toBe("register")
    expect(wrapper.find("[data-cy=username_field]").exists()).toBe(true)
  })

  test("polls then redirects to the dashboard when action is auth", async () => {
    vi.useFakeTimers()
    callbackHandler.mockResolvedValue(callbackResponse("auth"))
    // current user is null until the callback establishes the session; the
    // poll then sees the authenticated user and the redirect fires
    currentUserHandler.mockResolvedValueOnce({ data: { currentUser: null } })
    currentUserHandler.mockResolvedValue({
      data: { currentUser: { __typename: "User", id: "1" } }
    })
    mount(LoginOauthCallback, {
      props: { providerName: "orcid" },
      global: { stubs: ["router-link"] }
    })
    await vi.advanceTimersByTimeAsync(1100)
    expect(push).toHaveBeenCalledWith({ path: "/dashboard/" })
    vi.useRealTimers()
  })

  test("shows the redirect error banner when the callback fails", async () => {
    callbackHandler.mockRejectedValue(new Error("boom"))
    const wrapper = await createWrapper()
    expect((wrapper.vm as any).status).toBe("redirect_error")
    expect(wrapper.text()).toContain("auth.failures.INTERNAL")
  })
})
