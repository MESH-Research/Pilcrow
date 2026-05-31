import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import { SetUserBetaAccessDocument } from "src/graphql/generated/graphql"

const push = vi.fn()
vi.mock("vue-router", () => ({
  useRouter: () => ({ push })
}))

// QueryTable owns the user listing/refetch; stub it so the spec stays
// focused on the grant/revoke wiring and exposes a spy refetch.
const refetch = vi.fn()
const QueryTableStub = defineComponent({
  name: "QueryTable",
  setup(_, { expose }) {
    expose({ refetch })
    return () => h("div", { class: "query-table-stub" })
  }
})

// FindUserSelect is a v-model input; stub lets a test emit a chosen user.
const FindUserSelectStub = defineComponent({
  name: "FindUserSelect",
  emits: ["update:modelValue"],
  setup: () => () => h("div", { class: "find-user-stub" })
})

installQuasarPlugin()
const mockClient = installApolloClient()

import BetaUsersPage from "./beta-users.vue"

function factory() {
  return mount(BetaUsersPage, {
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { QueryTable: QueryTableStub, FindUserSelect: FindUserSelectStub }
    }
  })
}

describe("admin beta-users page", () => {
  beforeEach(() => {
    mockClient.mockReset()
    refetch.mockReset()
    push.mockReset()
    mockClient.getRequestHandler(SetUserBetaAccessDocument).mockResolvedValue({
      data: { setUserBetaAccess: { __typename: "User", id: "7", beta: true } }
    })
  })

  it("disables the grant button until a user is selected", async () => {
    const wrapper = factory()
    const btn = wrapper.find('[data-cy="beta_user_add_btn"]')
    expect(btn.attributes("disabled")).toBeDefined()
  })

  it("grants beta access to the chosen user and refetches", async () => {
    const wrapper = factory()
    wrapper
      .findComponent(FindUserSelectStub)
      .vm.$emit("update:modelValue", { id: "7", name: "Jane" })
    await flushPromises()

    await wrapper.find('[data-cy="beta_user_add_btn"]').trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(SetUserBetaAccessDocument)
    ).toHaveBeenCalledWith({ id: "7", enabled: true })
    expect(refetch).toHaveBeenCalled()
  })
})
