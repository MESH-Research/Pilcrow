import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { CURRENT_USER } from "src/graphql/queries"
import { DismissUiElementDocument } from "src/graphql/generated/graphql"
import type { CurrentUserQuery } from "src/graphql/generated/graphql"
import ManageInfoCallout from "./ManageInfoCallout.vue"

import { beforeEach, describe, expect, test, vi } from "vitest"

installQuasarPlugin()
const mockClient = installApolloClient()

function buildCurrentUserResponse(dismissedKeys: string[] = []): {
  data: CurrentUserQuery
} {
  return {
    data: {
      currentUser: {
        __typename: "User",
        id: "1",
        display_label: "Hello",
        name: "Hello",
        email: "hello@example.com",
        username: "helloUser",
        email_verified_at: null,
        highest_privileged_role: null,
        roles: [],
        preferences: null,
        dismissed_ui: dismissedKeys,
        feature_opt_ins: []
      }
    }
  }
}

describe("ManageInfoCallout", () => {
  const dismissHandler = vi.fn()
  const currentUserHandler = vi.fn()

  mockClient.setRequestHandler(DismissUiElementDocument, dismissHandler)
  mockClient.setRequestHandler(CURRENT_USER, currentUserHandler)

  beforeEach(() => {
    vi.resetAllMocks()
    currentUserHandler.mockResolvedValue(buildCurrentUserResponse())
  })

  const baseProps = {
    title: "Welcome to Manage",
    body: "This is what the manage view does.",
    dismissKey: "manage_ui.opt_in_callout"
  }

  test("renders title and body", async () => {
    const wrapper = mount(ManageInfoCallout, { props: baseProps })
    await flushPromises()

    expect(wrapper.text()).toContain(baseProps.title)
    expect(wrapper.text()).toContain(baseProps.body)
  })

  test("hides the dismiss button when no dismissKey is provided", async () => {
    const wrapper = mount(ManageInfoCallout, {
      props: { title: "T", body: "B" }
    })
    await flushPromises()

    expect(wrapper.find(".callout-dismiss").exists()).toBe(false)
  })

  test("shows the dismiss button when a dismissKey is provided", async () => {
    const wrapper = mount(ManageInfoCallout, { props: baseProps })
    await flushPromises()

    expect(wrapper.find(".callout-dismiss").exists()).toBe(true)
  })

  test("hides the callout entirely when the key is already dismissed server-side", async () => {
    currentUserHandler.mockResolvedValue(
      buildCurrentUserResponse(["manage_ui.opt_in_callout"])
    )
    const wrapper = mount(ManageInfoCallout, { props: baseProps })
    await flushPromises()

    expect(wrapper.find(".manage-info-callout").exists()).toBe(false)
  })

  test("clicking dismiss fires the mutation with the configured key and hides optimistically", async () => {
    dismissHandler.mockResolvedValue({
      data: {
        dismissUiElement: {
          __typename: "User",
          id: "1",
          dismissed_ui: ["manage_ui.opt_in_callout"]
        }
      }
    })
    const wrapper = mount(ManageInfoCallout, { props: baseProps })
    await flushPromises()

    await wrapper.find(".callout-dismiss").trigger("click")
    // The optimistic flag flips synchronously on click — the callout
    // should disappear before the network call resolves.
    await wrapper.vm.$nextTick()
    expect(wrapper.find(".manage-info-callout").exists()).toBe(false)

    await flushPromises()
    expect(dismissHandler).toHaveBeenCalledTimes(1)
    expect(dismissHandler).toHaveBeenCalledWith({
      key: "manage_ui.opt_in_callout"
    })
  })

  test("rolls back the optimistic hide when the mutation rejects", async () => {
    dismissHandler.mockRejectedValue(new Error("network down"))
    const wrapper = mount(ManageInfoCallout, { props: baseProps })
    await flushPromises()

    await wrapper.find(".callout-dismiss").trigger("click")
    await flushPromises()

    // Mutation failed, so the callout reappears for the user to
    // try again rather than vanishing silently.
    expect(wrapper.find(".manage-info-callout").exists()).toBe(true)
  })
})
