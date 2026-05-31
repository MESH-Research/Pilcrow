import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { describe, expect, it, beforeEach } from "vitest"
import { CURRENT_USER } from "src/graphql/queries"
import {
  SetFeatureOptInDocument,
  UserRoles
} from "src/graphql/generated/graphql"
import LabsFeaturePanel from "./LabsFeaturePanel.vue"

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
    props: { featureKey: "labs_test", label: "labs.labs_test.label" },
    slots: { default: "Feature body copy" }
  })
}

describe("LabsFeaturePanel", () => {
  beforeEach(() => {
    mockClient.mockReset()
    mockClient.getRequestHandler(SetFeatureOptInDocument).mockResolvedValue({
      data: {
        setFeatureOptIn: {
          __typename: "User",
          id: "1",
          feature_opt_ins: ["labs_test"]
        }
      }
    })
  })

  it("renders the label heading and the body slot", async () => {
    mockClient.getRequestHandler(CURRENT_USER).mockResolvedValue(currentUser())
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("labs.labs_test.label")
    expect(wrapper.text()).toContain("Feature body copy")
  })

  it("shows Activate and opts in on click when not opted in", async () => {
    mockClient.getRequestHandler(CURRENT_USER).mockResolvedValue(currentUser())
    const wrapper = factory()
    await flushPromises()

    const btn = wrapper.find('[data-cy="labs_feature_labs_test"]')
    expect(btn.text()).toContain("labs.activate")

    await btn.trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(SetFeatureOptInDocument)
    ).toHaveBeenCalledWith({ feature: "labs_test", enabled: true })
  })

  it("shows Deactivate and opts out on click when opted in", async () => {
    mockClient
      .getRequestHandler(CURRENT_USER)
      .mockResolvedValue(currentUser(["labs_test"]))
    const wrapper = factory()
    await flushPromises()

    const btn = wrapper.find('[data-cy="labs_feature_labs_test"]')
    expect(btn.text()).toContain("labs.deactivate")

    await btn.trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(SetFeatureOptInDocument)
    ).toHaveBeenCalledWith({ feature: "labs_test", enabled: false })
  })

  it("renders a custom title via the title slot", async () => {
    mockClient.getRequestHandler(CURRENT_USER).mockResolvedValue(currentUser())
    const wrapper = mount(LabsFeaturePanel, {
      props: { featureKey: "labs_test" },
      slots: { title: "Custom Heading", default: "body" }
    })
    await flushPromises()
    expect(wrapper.text()).toContain("Custom Heading")
  })
})
