import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { CURRENT_USER } from "src/graphql/queries"
import { SetFeatureOptInDocument } from "src/graphql/generated/graphql"
import type { CurrentUserQuery } from "src/graphql/generated/graphql"
import LabFeaturesPage from "./LabFeaturesPage.vue"

import { beforeEach, describe, expect, test, vi } from "vitest"

vi.mock("quasar", async (importOriginal) => {
  const quasar = await importOriginal<typeof import("quasar")>()
  return {
    ...quasar,
    useQuasar: () => ({ notify: vi.fn(), dark: { isActive: false } })
  }
})

installQuasarPlugin()
const mockClient = installApolloClient()

function buildCurrentUserResponse(optedInFeatures: string[] = []): {
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
        dismissed_ui: [],
        feature_opt_ins: optedInFeatures
      }
    }
  }
}

describe("LabFeaturesPage", () => {
  const setOptInHandler = vi.fn()
  const currentUserHandler = vi.fn()

  mockClient.setRequestHandler(SetFeatureOptInDocument, setOptInHandler)
  mockClient.setRequestHandler(CURRENT_USER, currentUserHandler)

  beforeEach(() => {
    vi.resetAllMocks()
    currentUserHandler.mockResolvedValue(buildCurrentUserResponse())
  })

  test("renders the lab feature catalog", async () => {
    const wrapper = mount(LabFeaturesPage)
    await flushPromises()

    // The hardcoded `manage_ui_v2` lab feature shows its label/help
    // text via i18n keys (mocked to return the key itself).
    expect(wrapper.text()).toContain("lab_features.manage_ui_v2.label")
    expect(wrapper.text()).toContain("lab_features.manage_ui_v2.description")
  })

  test("button reads 'Activate' when the user has not opted into the feature", async () => {
    const wrapper = mount(LabFeaturesPage)
    await flushPromises()

    const btn = wrapper.find("[data-cy=lab_feature_manage_ui_v2]")
    expect(btn.exists()).toBe(true)
    expect(btn.text()).toContain("lab_features.activate")
  })

  test("button reads 'Deactivate' when the user is already opted in", async () => {
    currentUserHandler.mockResolvedValue(
      buildCurrentUserResponse(["manage_ui_v2"])
    )

    const wrapper = mount(LabFeaturesPage)
    await flushPromises()

    const btn = wrapper.find("[data-cy=lab_feature_manage_ui_v2]")
    expect(btn.text()).toContain("lab_features.deactivate")
  })

  test("clicking the button fires setFeatureOptIn(enabled=true) when not opted in", async () => {
    setOptInHandler.mockResolvedValue({
      data: {
        setFeatureOptIn: {
          __typename: "User",
          id: "1",
          feature_opt_ins: ["manage_ui_v2"]
        }
      }
    })

    const wrapper = mount(LabFeaturesPage)
    await flushPromises()

    await wrapper.find("[data-cy=lab_feature_manage_ui_v2]").trigger("click")
    await flushPromises()

    expect(setOptInHandler).toHaveBeenCalledTimes(1)
    expect(setOptInHandler).toHaveBeenCalledWith({
      feature: "manage_ui_v2",
      enabled: true
    })
  })

  test("clicking the button fires setFeatureOptIn(enabled=false) when already opted in", async () => {
    currentUserHandler.mockResolvedValue(
      buildCurrentUserResponse(["manage_ui_v2"])
    )
    setOptInHandler.mockResolvedValue({
      data: {
        setFeatureOptIn: {
          __typename: "User",
          id: "1",
          feature_opt_ins: []
        }
      }
    })

    const wrapper = mount(LabFeaturesPage)
    await flushPromises()

    await wrapper.find("[data-cy=lab_feature_manage_ui_v2]").trigger("click")
    await flushPromises()

    expect(setOptInHandler).toHaveBeenCalledWith({
      feature: "manage_ui_v2",
      enabled: false
    })
  })
})
