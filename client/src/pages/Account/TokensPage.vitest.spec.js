import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { GET_PERSONAL_ACCESS_TOKENS } from "src/graphql/queries"
import TokensPage from "./TokensPage.vue"

import { beforeEach, describe, expect, it, vi } from "vitest"

vi.mock("quasar", async (importOriginal) => {
  const quasar = await importOriginal()
  return {
    ...quasar,
    useQuasar: () => ({
      notify: vi.fn(),
      dialog: vi.fn(() => ({
        onOk: vi.fn(() => ({ onCancel: vi.fn() })),
        onCancel: vi.fn()
      }))
    })
  }
})

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Tokens page", () => {
  const tokensHandler = vi.fn()

  mockClient.setRequestHandler(GET_PERSONAL_ACCESS_TOKENS, tokensHandler)

  const makeWrapper = async () => {
    const wrapper = mount(TokensPage)
    await flushPromises()
    return wrapper
  }

  const mockTokens = [
    {
      id: "1",
      name: "Test Token 1",
      created_at: "2026-01-15T10:30:00.000000Z",
      last_used_at: "2026-02-01T14:00:00.000000Z"
    },
    {
      id: "2",
      name: "Test Token 2",
      created_at: "2026-02-01T09:00:00.000000Z",
      last_used_at: null
    }
  ]

  beforeEach(() => {
    vi.resetAllMocks()
    tokensHandler.mockResolvedValue({
      data: {
        currentUser: {
          id: "1",
          tokens: mockTokens
        }
      }
    })
  })

  it("mounts without errors", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  it("displays page title", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper.find("[data-cy='page_heading']").exists()).toBe(true)
  })

  it("displays list of tokens", async () => {
    const wrapper = await makeWrapper()
    const tokenItems = wrapper.findAll("[data-cy='token_item']")
    expect(tokenItems.length).toBe(2)
  })

  it("displays empty state when no tokens", async () => {
    tokensHandler.mockResolvedValue({
      data: {
        currentUser: {
          id: "1",
          tokens: []
        }
      }
    })
    const wrapper = await makeWrapper()
    expect(wrapper.find("[data-cy='no_tokens']").exists()).toBe(true)
  })

  it("shows create token button", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper.find("[data-cy='create_token_button']").exists()).toBe(true)
  })

  it("shows revoke button for each token", async () => {
    const wrapper = await makeWrapper()
    const revokeButtons = wrapper.findAll("[data-cy='revoke_token_button']")
    expect(revokeButtons.length).toBe(2)
  })

  it("displays token names in list", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper.text()).toContain("Test Token 1")
    expect(wrapper.text()).toContain("Test Token 2")
  })
})
