import { computed } from "vue"
import { useCurrentUser } from "./user"
import type { UserThemePreference } from "src/graphql/generated/graphql"

/**
 * Read-only reactive accessors for the current user's stored
 * settings: UI preferences (theme / color-blind patterns), one-shot
 * UI dismissals, and feature-flag opt-ins.
 *
 * Mutations are intentionally *not* exposed here — they live with
 * the UI surface that triggers them (e.g. the user-settings page
 * owns `updateUserPreferences`, ManageInfoCallout owns
 * `dismissUiElement`). Apollo's normalized cache merges those
 * mutation responses by user id, so the values returned by these
 * computed props update automatically without re-querying.
 *
 * Theme application (toggling Quasar's dark mode based on
 * `theme.value`) is intentionally left to the caller.
 */
export function useUserPreferences() {
  const { currentUser } = useCurrentUser()

  const theme = computed<UserThemePreference | null>(
    () => currentUser.value?.preferences?.theme ?? null
  )

  const colorBlindPatterns = computed<boolean>(
    () => currentUser.value?.preferences?.color_blind_patterns ?? false
  )

  const dismissedKeys = computed<readonly string[]>(
    () => currentUser.value?.dismissed_ui ?? []
  )

  const optedInFeatures = computed<readonly string[]>(
    () => currentUser.value?.feature_opt_ins ?? []
  )

  // Returns a reactive computed for a specific key — `isDismissed("foo")`
  // re-evaluates whenever the user's dismissed_ui changes. Cheaper
  // than having every caller wire its own computed off the array.
  function isDismissed(key: string) {
    return computed(() => dismissedKeys.value.includes(key))
  }

  function hasOptedIn(feature: string) {
    return computed(() => optedInFeatures.value.includes(feature))
  }

  return {
    theme,
    colorBlindPatterns,
    dismissedKeys,
    optedInFeatures,
    isDismissed,
    hasOptedIn
  }
}
