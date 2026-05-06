import { mount } from "vue-composable-tester"
import { createMockClient } from "app/test/vitest/utils"
import { useUserPreferences } from "./userPreferences"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { CURRENT_USER } from "src/graphql/queries"
import { UserThemePreference } from "src/graphql/generated/graphql"
import type { CurrentUserQuery } from "src/graphql/generated/graphql"
import { provide } from "vue"
import { flushPromises } from "@vue/test-utils"

import { describe, test, expect, vi } from "vitest"

vi.mock("quasar", async (importOriginal) => {
  const original = await importOriginal<typeof import("quasar")>()
  return original
})

function buildUser(
  overrides: Partial<NonNullable<CurrentUserQuery["currentUser"]>> = {}
): CurrentUserQuery {
  return {
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
      feature_opt_ins: [],
      ...overrides
    }
  }
}

function mountComposable(response: { data: CurrentUserQuery | null }) {
  const mockClient = createMockClient({ devtools: { enabled: false } })
  mockClient.setRequestHandler(
    CURRENT_USER,
    vi.fn().mockResolvedValue(response)
  )
  const { result } = mount(() => useUserPreferences(), {
    provider: () => provide(DefaultApolloClient, mockClient)
  })
  return result
}

describe("useUserPreferences composable", () => {
  test("returns null/false defaults when no user is logged in", async () => {
    const result = mountComposable({ data: { currentUser: null } })
    await flushPromises()

    expect(result.theme.value).toBeNull()
    expect(result.a11yColorPatterns.value).toBe(false)
    expect(result.dismissedKeys.value).toEqual([])
    expect(result.optedInFeatures.value).toEqual([])
  })

  test("exposes stored preference values from the current user", async () => {
    const result = mountComposable({
      data: buildUser({
        preferences: {
          __typename: "UserPreferences",
          theme: UserThemePreference.DARK,
          a11y_color_patterns: true
        },
        dismissed_ui: ["manage_ui.opt_in_callout", "team.flag_help"],
        feature_opt_ins: ["manage_ui_v2"]
      })
    })
    await flushPromises()

    expect(result.theme.value).toBe(UserThemePreference.DARK)
    expect(result.a11yColorPatterns.value).toBe(true)
    expect(result.dismissedKeys.value).toEqual([
      "manage_ui.opt_in_callout",
      "team.flag_help"
    ])
    expect(result.optedInFeatures.value).toEqual(["manage_ui_v2"])
  })

  test("a11yColorPatterns falls back to false when preferences are null", async () => {
    const result = mountComposable({
      data: buildUser({ preferences: null })
    })
    await flushPromises()

    expect(result.a11yColorPatterns.value).toBe(false)
  })

  test("isDismissed reports membership in dismissed_ui", async () => {
    const result = mountComposable({
      data: buildUser({ dismissed_ui: ["a", "b"] })
    })
    await flushPromises()

    expect(result.isDismissed("a").value).toBe(true)
    expect(result.isDismissed("b").value).toBe(true)
    expect(result.isDismissed("missing").value).toBe(false)
  })

  test("hasOptedIn reports membership in feature_opt_ins", async () => {
    const result = mountComposable({
      data: buildUser({ feature_opt_ins: ["manage_ui_v2"] })
    })
    await flushPromises()

    expect(result.hasOptedIn("manage_ui_v2").value).toBe(true)
    expect(result.hasOptedIn("not_enabled").value).toBe(false)
  })
})
