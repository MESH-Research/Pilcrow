import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { describe, expect, it, beforeEach, vi } from "vitest"
import { CURRENT_USER } from "src/graphql/queries"
import {
  SetFeatureOptInDocument,
  UserRoles
} from "src/graphql/generated/graphql"
import LabsFeaturePanel from "./LabsFeaturePanel.vue"

const mockNewStatus = vi.fn()
vi.mock("src/use/guiElements", () => ({
  useFeedbackMessages: () => ({ newStatusMessage: mockNewStatus })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

function currentUser(optIns: string[] = []) {
  return {
    data: {
      currentUser: {
        __typename: "User",
        id: "1",
        display_label: "Beta Tester",
        name: "Beta Tester",
        email: "beta@example.com",
        username: "betaUser",
        avatar_color: "blue",
        email_verified_at: null,
        highest_privileged_role: UserRoles.submitter,
        roles: [{ name: "Submitter" }],
        beta: true,
        feature_opt_ins: optIns
      }
    }
  }
}

function factory() {
  return mount(LabsFeaturePanel, {
    props: { featureKey: "sample_feature", label: "labs.sample_feature.label" },
    slots: { default: "Feature body copy" }
  })
}

describe("LabsFeaturePanel", () => {
  beforeEach(() => {
    mockClient.mockReset()
    mockNewStatus.mockReset()
    mockClient.getRequestHandler(SetFeatureOptInDocument).mockResolvedValue({
      data: {
        setFeatureOptIn: {
          __typename: "User",
          id: "1",
          feature_opt_ins: ["sample_feature"]
        }
      }
    })
  })

  it("renders the label heading and the body slot", async () => {
    mockClient.getRequestHandler(CURRENT_USER).mockResolvedValue(currentUser())
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("labs.sample_feature.label")
    expect(wrapper.text()).toContain("Feature body copy")
  })

  it("shows Activate and opts in on click when not opted in", async () => {
    mockClient.getRequestHandler(CURRENT_USER).mockResolvedValue(currentUser())
    const wrapper = factory()
    await flushPromises()

    const btn = wrapper.find('[data-cy="labs_feature_sample_feature"]')
    expect(btn.text()).toContain("labs.activate")

    await btn.trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(SetFeatureOptInDocument)
    ).toHaveBeenCalledWith({ feature: "sample_feature", enabled: true })
  })

  it("shows Deactivate and opts out on click when opted in", async () => {
    mockClient
      .getRequestHandler(CURRENT_USER)
      .mockResolvedValue(currentUser(["sample_feature"]))
    const wrapper = factory()
    await flushPromises()

    const btn = wrapper.find('[data-cy="labs_feature_sample_feature"]')
    expect(btn.text()).toContain("labs.deactivate")

    await btn.trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(SetFeatureOptInDocument)
    ).toHaveBeenCalledWith({ feature: "sample_feature", enabled: false })
  })

  it("surfaces a failure message when the opt-in mutation rejects", async () => {
    mockClient.getRequestHandler(CURRENT_USER).mockResolvedValue(currentUser())
    mockClient
      .getRequestHandler(SetFeatureOptInDocument)
      .mockRejectedValue(new Error("network"))
    const wrapper = factory()
    await flushPromises()

    await wrapper.find('[data-cy="labs_feature_sample_feature"]').trigger("click")
    await flushPromises()

    expect(mockNewStatus).toHaveBeenCalledWith("failure", "labs.error")
  })

  it("renders a custom title via the title slot", async () => {
    mockClient.getRequestHandler(CURRENT_USER).mockResolvedValue(currentUser())
    const wrapper = mount(LabsFeaturePanel, {
      props: { featureKey: "sample_feature" },
      slots: { title: "Custom Heading", default: "body" }
    })
    await flushPromises()
    expect(wrapper.text()).toContain("Custom Heading")
  })
})
