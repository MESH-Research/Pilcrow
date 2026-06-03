import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import { SetUserBetaAccessDocument } from "src/graphql/generated/graphql"

const push = vi.fn()
vi.mock("vue-router", () => ({
  useRouter: () => ({ push })
}))

const mockNewStatus = vi.fn()
vi.mock("src/use/guiElements", () => ({
  useFeedbackMessages: () => ({ newStatusMessage: mockNewStatus })
}))

// QueryTable owns the user listing/refetch; stub it so the spec stays
// focused on the grant/revoke wiring. The stub exposes a spy refetch,
// re-emits row-click, and renders the body-cell-actions slot with a fake
// row so the per-row remove button is reachable.
const refetch = vi.fn()
const QueryTableStub = defineComponent({
  name: "QueryTable",
  emits: ["row-click"],
  setup(_, { expose, slots, emit }) {
    expose({ refetch })
    return () =>
      h("div", { class: "query-table-stub" }, [
        h(
          "div",
          {
            class: "row-click-trigger",
            onClick: () => emit("row-click", new Event("click"), { id: "7" })
          },
          "row"
        ),
        slots["body-cell-actions"]?.({ row: { id: "7" } })
      ])
  }
})

// FindUserSelect is a v-model input; stub lets a test emit a chosen user.
const FindUserSelectStub = defineComponent({
  name: "FindUserSelect",
  emits: ["update:modelValue"],
  setup: () => () => h("div", { class: "find-user-stub" })
})

// QTd reads QTable-internal column metadata it never receives outside a
// real table; stub it down to a passthrough so the slot's remove button
// renders.
const QTdStub = defineComponent({
  name: "QTd",
  setup:
    (_, { slots }) =>
    () =>
      h("td", slots.default?.())
})

installQuasarPlugin()
const mockClient = installApolloClient()

import BetaUsersPage from "./beta-users.vue"

function factory() {
  return mount(BetaUsersPage, {
    global: {
      mocks: { $t: (t: string) => t },
      stubs: {
        QueryTable: QueryTableStub,
        FindUserSelect: FindUserSelectStub,
        QTd: QTdStub
      }
    }
  })
}

describe("admin beta-users page", () => {
  beforeEach(() => {
    mockClient.mockReset()
    refetch.mockReset()
    push.mockReset()
    mockNewStatus.mockReset()
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

  it("surfaces a failure message when granting rejects", async () => {
    mockClient
      .getRequestHandler(SetUserBetaAccessDocument)
      .mockRejectedValue(new Error("network"))
    const wrapper = factory()
    wrapper
      .findComponent(FindUserSelectStub)
      .vm.$emit("update:modelValue", { id: "7", name: "Jane" })
    await flushPromises()

    await wrapper.find('[data-cy="beta_user_add_btn"]').trigger("click")
    await flushPromises()

    expect(mockNewStatus).toHaveBeenCalledWith(
      "failure",
      "admin.beta_users.error"
    )
    expect(refetch).not.toHaveBeenCalled()
  })

  it("revokes beta access via the per-row remove button and refetches", async () => {
    const wrapper = factory()
    await wrapper.find('[data-cy="beta_user_remove_7"]').trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(SetUserBetaAccessDocument)
    ).toHaveBeenCalledWith({ id: "7", enabled: false })
    expect(refetch).toHaveBeenCalled()
  })

  it("surfaces a failure message when revoking rejects", async () => {
    mockClient
      .getRequestHandler(SetUserBetaAccessDocument)
      .mockRejectedValue(new Error("network"))
    const wrapper = factory()
    await wrapper.find('[data-cy="beta_user_remove_7"]').trigger("click")
    await flushPromises()

    expect(mockNewStatus).toHaveBeenCalledWith(
      "failure",
      "admin.beta_users.error"
    )
  })

  it("navigates to the user detail page on row click", async () => {
    const wrapper = factory()
    await wrapper.find(".row-click-trigger").trigger("click")

    expect(push).toHaveBeenCalledWith({
      name: "user_details",
      params: { id: "7" }
    })
  })
})
