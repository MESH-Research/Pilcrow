import { mount } from "vue-composable-tester"
import { createMockClient } from "app/test/vitest/utils"
import { useFeatures } from "./features"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { CURRENT_USER } from "src/graphql/queries"
import { UserRoles } from "src/graphql/generated/graphql"
import type { CurrentUserQuery } from "src/graphql/generated/graphql"
import { provide } from "vue"
import { flushPromises } from "@vue/test-utils"
import { describe, test, expect, vi } from "vitest"

vi.mock("quasar", async (importOriginal) => {
  const original = await importOriginal<typeof import("quasar")>()
  return original
})

type CurrentUser = NonNullable<CurrentUserQuery["currentUser"]>

function userResponse(overrides: Partial<CurrentUser> = {}): {
  data: CurrentUserQuery
} {
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
        email_verified_at: "2021-08-14 02:26:32",
        highest_privileged_role: UserRoles.submitter,
        roles: [{ name: "Submitter" }],
        abilities: {
          publication_create: false,
          user_view: false,
          user_view_any: false,
          user_update: false,
          user_manage_beta: false,
          access_admin: false
        },
        beta: false,
        feature_opt_ins: [],
        ...overrides
      }
    }
  }
}

describe("useFeatures composable", () => {
  const mountComposable = (response: { data: CurrentUserQuery }) => {
    const mockClient = createMockClient({ devtools: { enabled: false } })
    mockClient.setRequestHandler(
      CURRENT_USER,
      vi.fn().mockResolvedValue(response)
    )
    const { result } = mount(() => useFeatures(), {
      provider: () => provide(DefaultApolloClient, mockClient)
    })
    return result
  }

  test("non-beta user with opt-in: not advertised, but feature enabled", async () => {
    const result = mountComposable(
      userResponse({ beta: false, feature_opt_ins: ["sample_feature"] })
    )
    await flushPromises()

    // `beta` governs Labs visibility only, not enablement.
    expect(result.isBeta.value).toBe(false)
    expect(result.hasOptedIn("sample_feature").value).toBe(true)
    // The opt-in record IS the grant — enabled even without beta access.
    // (Mirrors a future key-entry grant for a non-beta user.)
    expect(result.isFeatureEnabled("sample_feature").value).toBe(true)
  })

  test("beta user with opt-in: feature enabled", async () => {
    const result = mountComposable(
      userResponse({ beta: true, feature_opt_ins: ["sample_feature"] })
    )
    await flushPromises()

    expect(result.isBeta.value).toBe(true)
    expect(result.isFeatureEnabled("sample_feature").value).toBe(true)
    // A beta feature the user hasn't opted into stays off.
    expect(result.isFeatureEnabled("other_feature").value).toBe(false)
  })

  test("beta user without opt-in: feature not enabled", async () => {
    const result = mountComposable(
      userResponse({ beta: true, feature_opt_ins: [] })
    )
    await flushPromises()

    expect(result.isBeta.value).toBe(true)
    expect(result.isFeatureEnabled("sample_feature").value).toBe(false)
  })
})
