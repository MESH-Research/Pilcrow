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
const colorBlindRef = ref(false)

vi.mock("./userPreferences", () => ({
  useUserPreferences: () => ({
    theme: themeRef,
    colorBlindPatterns: colorBlindRef
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
    colorBlindRef.value = false
    document.body.classList.remove("body--color-blind")
  })

  afterEach(() => {
    document.body.classList.remove("body--color-blind")
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

  test("toggles body--color-blind class to mirror the preference", async () => {
    mount(() => useApplyUserPreferences())
    expect(document.body.classList.contains("body--color-blind")).toBe(false)

    colorBlindRef.value = true
    await nextTick()
    expect(document.body.classList.contains("body--color-blind")).toBe(true)

    colorBlindRef.value = false
    await nextTick()
    expect(document.body.classList.contains("body--color-blind")).toBe(false)
  })
})
