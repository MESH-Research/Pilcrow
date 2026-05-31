import { computed, ref } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { SetFeatureOptInDocument } from "src/graphql/generated/graphql"
import { useCurrentUser } from "./user"
import { useFeedbackMessages } from "./guiElements"

declare module "vue-router" {
  interface RouteMeta {
    /**
     * Marks a route as a beta feature surfaced under the Labs sub-layout.
     * The `key` matches the server `features.beta` catalog and is passed
     * to `setFeatureOptIn`. `private` features are hidden from users
     * without beta access; a future GA/ungated feature sets it false to
     * stay visible to everyone.
     */
    feature?: {
      key: string
      private: boolean
      // Sort key for the Labs list. Lower renders first; leave gaps
      // (10, 20, 30) so new features slot in without renumbering.
      order?: number
    }
  }
}

/**
 * Read-only reactive accessors for the current user's beta access and
 * beta-feature opt-ins.
 *
 * `beta` access is admin-granted and gates *visibility* of private beta
 * features in the Labs UI; `feature_opt_ins` records which features the
 * user has turned on. A feature is effectively enabled purely by its
 * opt-in record — that's the `isFeatureEnabled` check UI should gate on.
 * The `beta` flag governs only what we advertise (`isBeta`), not what is
 * on, mirroring the server's hasFeatureEnabled().
 *
 * Opt-in mutations live with the UI that triggers them (the Labs page).
 * Apollo's normalized cache merges those mutation responses by user id, so
 * these computed props update automatically without re-querying.
 */
export function useFeatures() {
  const { currentUser } = useCurrentUser()

  const isBeta = computed<boolean>(() => currentUser.value?.beta ?? false)

  const optedInFeatures = computed<readonly string[]>(
    () => currentUser.value?.feature_opt_ins ?? []
  )

  // Reactive computed for whether the user has opted into a specific
  // feature — independent of whether they still have beta access.
  function hasOptedIn(feature: string) {
    return computed(() => optedInFeatures.value.includes(feature))
  }

  // The gate UI should use: feature is on solely by its opt-in record,
  // independent of the `beta` flag. Mirrors the server's
  // hasFeatureEnabled() — the opt-in record is the access grant, so a
  // future key-entry grant enables a feature without beta visibility.
  function isFeatureEnabled(feature: string) {
    return computed(() => optedInFeatures.value.includes(feature))
  }

  return { isBeta, optedInFeatures, hasOptedIn, isFeatureEnabled }
}

/**
 * Per-feature opt-in toggle for a single Labs feature page. Wraps the
 * shared `setFeatureOptIn` mutation, tracks its own saving state, and
 * surfaces a failure message so each feature page stays thin.
 */
export function useLabsFeature(key: string) {
  const { optedInFeatures } = useFeatures()
  const { t } = useI18n()
  const { newStatusMessage } = useFeedbackMessages()
  const { mutate } = useMutation(SetFeatureOptInDocument)

  const optedIn = computed(() => optedInFeatures.value.includes(key))
  const saving = ref(false)

  async function toggle() {
    saving.value = true
    try {
      await mutate({ feature: key, enabled: !optedIn.value })
    } catch {
      newStatusMessage("failure", t("labs.error"))
    } finally {
      saving.value = false
    }
  }

  return { optedIn, saving, toggle }
}
