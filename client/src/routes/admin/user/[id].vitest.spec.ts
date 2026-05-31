import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { describe, expect, it, beforeEach, vi } from "vitest"
import {
  getUserDetailDocument,
  SetUserBetaAccessDocument
} from "src/graphql/generated/graphql"
import UserDetailLayout from "./[id].vue"

vi.mock("vue-router", () => ({
  useRoute: () => ({ params: { id: "5" } }),
  useRouter: () => ({ resolve: vi.fn(), push: vi.fn() })
}))

const mockNewStatus = vi.fn()
vi.mock("src/use/guiElements", () => ({
  useFeedbackMessages: () => ({ newStatusMessage: mockNewStatus })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

function userResult(overrides: Record<string, unknown> = {}) {
  return {
    data: {
      user: {
        __typename: "User",
        id: "5",
        username: "jdoe",
        email: "jdoe@example.com",
        name: "Jane Doe",
        created_at: "2024-01-02T15:30:00Z",
        email_verified_at: "2024-01-03T10:00:00Z",
        avatar_color: "#123456",
        roles: [{ __typename: "Role", name: "Reviewer" }],
        beta: false,
        feature_opt_ins: [],
        ...overrides
      }
    }
  }
}

function factory() {
  return mount(UserDetailLayout, {
    global: {
      stubs: {
        AvatarImage: true,
        "q-route-tab": true,
        "router-view": true
      },
      mocks: {
        $t: (token: string) => token
      }
    }
  })
}

describe("admin user detail layout", () => {
  beforeEach(() => {
    mockNewStatus.mockReset()
  })

  it("shows a loading state before the user resolves", () => {
    mockClient.getRequestHandler(getUserDetailDocument).mockResolvedValue({
      data: { user: null }
    })
    const wrapper = factory()
    expect(wrapper.text()).toContain("loading")
  })

  it("renders the user name and username once resolved", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult())
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("Jane Doe")
    expect(wrapper.text()).toContain("@jdoe")
  })

  it("formats the verified-at timestamp when present", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult())
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("Jan 3 2024")
  })

  it("shows the not-verified state when email is unverified", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult({ email_verified_at: null }))
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("admin.users.details.not_verified")
  })

  it("flags application administrators", async () => {
    mockClient.getRequestHandler(getUserDetailDocument).mockResolvedValue(
      userResult({
        roles: [{ __typename: "Role", name: "Application Administrator" }]
      })
    )
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("admin.users.details.isAdmin")
    expect(wrapper.text()).not.toContain("admin.users.details.isNormal")
  })

  it("flags non-administrators as normal users", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult())
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("admin.users.details.isNormal")
  })

  it("falls back to username when name is absent", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult({ name: null }))
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.find(".text-h5").text()).toBe("jdoe")
  })

  it("lists the user's opted-in Labs features as chips", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult({ feature_opt_ins: ["labs_test"] }))
    const wrapper = factory()
    await flushPromises()
    const chips = wrapper.find('[data-cy="user_feature_opt_ins"]')
    expect(chips.exists()).toBe(true)
    // useI18n mock resolves keys to the key string itself.
    expect(chips.text()).toContain("labs.labs_test.label")
  })

  it("shows the empty state when the user has no opt-ins", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult({ feature_opt_ins: [] }))
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.find('[data-cy="user_no_feature_opt_ins"]').exists()).toBe(
      true
    )
  })

  it("grants beta access when the toggle is switched on", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult({ beta: false }))
    const betaHandler = mockClient
      .getRequestHandler(SetUserBetaAccessDocument)
      .mockResolvedValue({
        data: { setUserBetaAccess: { __typename: "User", id: "5", beta: true } }
      })
    const wrapper = factory()
    await flushPromises()

    wrapper
      .findComponent({ name: "QToggle" })
      .vm.$emit("update:modelValue", true)
    await flushPromises()

    expect(betaHandler).toHaveBeenCalledWith({ id: "5", enabled: true })
    expect(mockNewStatus).not.toHaveBeenCalled()
  })

  it("revokes beta access when the toggle is switched off", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult({ beta: true }))
    const betaHandler = mockClient
      .getRequestHandler(SetUserBetaAccessDocument)
      .mockResolvedValue({
        data: {
          setUserBetaAccess: { __typename: "User", id: "5", beta: false }
        }
      })
    const wrapper = factory()
    await flushPromises()

    wrapper
      .findComponent({ name: "QToggle" })
      .vm.$emit("update:modelValue", false)
    await flushPromises()

    expect(betaHandler).toHaveBeenCalledWith({ id: "5", enabled: false })
  })

  it("surfaces a failure message when the beta mutation rejects", async () => {
    mockClient
      .getRequestHandler(getUserDetailDocument)
      .mockResolvedValue(userResult({ beta: false }))
    mockClient
      .getRequestHandler(SetUserBetaAccessDocument)
      .mockRejectedValue(new Error("network"))
    const wrapper = factory()
    await flushPromises()

    wrapper
      .findComponent({ name: "QToggle" })
      .vm.$emit("update:modelValue", true)
    await flushPromises()

    expect(mockNewStatus).toHaveBeenCalledWith(
      "failure",
      "admin.users.details.beta_error"
    )
  })
})
