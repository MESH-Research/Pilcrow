import { watchEffect } from "vue"
import { useQuasar } from "quasar"
import { useUserPreferences } from "./userPreferences"
import { UserThemePreference } from "src/graphql/generated/graphql"

/**
 * Apply the authenticated user's stored preferences to the running
 * app: dark/light mode via Quasar, and a `body--color-blind` class
 * for any CSS that wants to swap colored cues for high-contrast
 * patterns.
 *
 * Called once from the root component (App.vue) so it runs as soon
 * as the current-user query resolves and re-applies whenever the
 * user updates their preferences.
 *
 * Falls back to `auto` and patterns-off when no preferences are
 * stored (or no user is logged in), matching the application's
 * default behavior.
 */
export function useApplyUserPreferences() {
  const $q = useQuasar()
  const { theme, colorBlindPatterns } = useUserPreferences()

  watchEffect(() => {
    // Quasar accepts `true | false | "auto"`. AUTO mirrors the OS
    // preference; LIGHT and DARK pin the mode regardless.
    const value = theme.value
    if (value === UserThemePreference.DARK) {
      $q.dark.set(true)
    } else if (value === UserThemePreference.LIGHT) {
      $q.dark.set(false)
    } else {
      $q.dark.set("auto")
    }
  })

  watchEffect(() => {
    if (typeof document === "undefined") return
    document.body.classList.toggle(
      "body--color-blind",
      colorBlindPatterns.value
    )
  })
}
