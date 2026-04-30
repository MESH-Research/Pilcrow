import { mount } from "vue-composable-tester"
import { useApplyUserPreferences } from "./applyUserPreferences"
import { UserThemePreference } from "src/graphql/generated/graphql"
import { ref, nextTick } from "vue"
import { describe, test, expect, vi, beforeEach, afterEach } from "vitest"

const darkSet = vi.fn()

// `useUserPreferences` is mocked rather than the underlying Apollo
// query: this test only cares about the projection from preference
// values to body class / Quasar dark mode, not the data layer.
const themeRef = ref<UserThemePreference | null>(null)
const a11yRef = ref(false)

vi.mock("./userPreferences", () => ({
  useUserPreferences: () => ({
    theme: themeRef,
    a11yColorPatterns: a11yRef
  })
}))

vi.mock("quasar", () => ({
  useQuasar: () => ({
    dark: { set: darkSet }
  })
}))

describe("useApplyUserPreferences composable", () => {
  beforeEach(() => {
    darkSet.mockReset()
    themeRef.value = null
    a11yRef.value = false
    document.body.classList.remove("a11y-patterns")
  })

  afterEach(() => {
    document.body.classList.remove("a11y-patterns")
  })

  test("sets dark mode to auto when no theme is stored", () => {
    mount(() => useApplyUserPreferences())
    expect(darkSet).toHaveBeenLastCalledWith("auto")
  })

  test("pins dark mode true on DARK preference", () => {
    themeRef.value = UserThemePreference.DARK
    mount(() => useApplyUserPreferences())
    expect(darkSet).toHaveBeenLastCalledWith(true)
  })

  test("pins dark mode false on LIGHT preference", () => {
    themeRef.value = UserThemePreference.LIGHT
    mount(() => useApplyUserPreferences())
    expect(darkSet).toHaveBeenLastCalledWith(false)
  })

  test("falls back to auto on AUTO preference", () => {
    themeRef.value = UserThemePreference.AUTO
    mount(() => useApplyUserPreferences())
    expect(darkSet).toHaveBeenLastCalledWith("auto")
  })

  test("re-applies dark mode when theme preference changes", async () => {
    mount(() => useApplyUserPreferences())
    expect(darkSet).toHaveBeenLastCalledWith("auto")

    themeRef.value = UserThemePreference.DARK
    await nextTick()
    expect(darkSet).toHaveBeenLastCalledWith(true)

    themeRef.value = UserThemePreference.LIGHT
    await nextTick()
    expect(darkSet).toHaveBeenLastCalledWith(false)
  })

  test("toggles a11y-patterns class to mirror the preference", async () => {
    mount(() => useApplyUserPreferences())
    expect(document.body.classList.contains("a11y-patterns")).toBe(false)

    a11yRef.value = true
    await nextTick()
    expect(document.body.classList.contains("a11y-patterns")).toBe(true)

    a11yRef.value = false
    await nextTick()
    expect(document.body.classList.contains("a11y-patterns")).toBe(false)
  })
})
