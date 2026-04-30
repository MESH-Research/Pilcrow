import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import UserSettings from "./settings.vue"
import {
  getUserSettingsDocument,
  type getUserSettingsQuery,
  UserThemePreference
} from "src/graphql/generated/graphql"
import { describe, expect, it, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn()
  }),
  useRoute: () => ({
    query: {},
    params: { id: "1" }
  })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("UserDetails settings tab", () => {
  function mockResponse(
    overrides: Partial<NonNullable<getUserSettingsQuery["user"]>> = {}
  ): { data: getUserSettingsQuery } {
    return {
      data: {
        user: {
          id: "1",
          preferences: null,
          dismissed_ui: [],
          feature_opt_ins: [],
          ...overrides
        }
      }
    }
  }

  const wrapperFactory = () =>
    mount(UserSettings, { global: { stubs: ["router-link"] } })

  it("mounts without errors", () => {
    expect(wrapperFactory()).toBeTruthy()
  })

  // Note: vitest setup mocks `$t` / `useI18n.t` to return the raw key,
  // so assertions check the i18n keys rather than the translated copy.

  it("falls back to the auto theme when no preferences are stored", async () => {
    mockClient
      .getRequestHandler(getUserSettingsDocument)
      .mockResolvedValue(mockResponse())

    const wrapper = wrapperFactory()
    await flushPromises()

    expect(wrapper.text()).toContain(
      "admin.users.details.settings.preferences.theme_auto"
    )
    expect(wrapper.text()).toContain(
      "admin.users.details.settings.preferences.not_set"
    )
    expect(wrapper.text()).toContain(
      "admin.users.details.settings.dismissed.none"
    )
    expect(wrapper.text()).toContain(
      "admin.users.details.settings.feature_opt_ins.none"
    )
  })

  it("displays stored preferences and key lists", async () => {
    mockClient.getRequestHandler(getUserSettingsDocument).mockResolvedValue(
      mockResponse({
        preferences: {
          theme: UserThemePreference.DARK,
          color_blind_patterns: true
        },
        dismissed_ui: ["manage_ui.opt_in_callout", "team.flag_help"],
        feature_opt_ins: ["manage_ui_v2"]
      })
    )

    const wrapper = wrapperFactory()
    await flushPromises()

    expect(wrapper.text()).toContain(
      "admin.users.details.settings.preferences.theme_dark"
    )
    expect(wrapper.text()).toContain(
      "admin.users.details.settings.preferences.enabled"
    )
    expect(wrapper.text()).toContain("manage_ui.opt_in_callout")
    expect(wrapper.text()).toContain("team.flag_help")
    expect(wrapper.text()).toContain("manage_ui_v2")
  })
})
