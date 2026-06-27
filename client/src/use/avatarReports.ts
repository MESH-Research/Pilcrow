import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { GetPendingAvatarReportCountDocument } from "src/graphql/generated/graphql"
import { useCurrentUser } from "./user"

/**
 * Reactive count of pending avatar reports, for badge UIs on the
 * admin dashboard, the header's administration menu, and the
 * Pending filter on the moderation queue itself.
 *
 * The underlying `GetPendingAvatarReportCount` query is colocated in
 * AdminDashboard.vue (where codegen can see it); we just import the
 * generated Document here.
 *
 * Query is skipped when the current user doesn't have the
 * `avatar_moderate` ability — the server would reject it anyway, and
 * there is no badge to render for non-moderators.
 */
export function useAvatarReportsPendingCount() {
  const { can } = useCurrentUser()
  const canModerate = computed(() => can("avatar_moderate"))

  const { result, refetch } = useQuery(
    GetPendingAvatarReportCountDocument,
    undefined,
    () => ({
      enabled: canModerate.value,
      fetchPolicy: "cache-and-network"
    })
  )

  const count = computed(
    () => result.value?.avatarReports?.paginatorInfo?.total ?? 0
  )

  return { count, canModerate, refetch }
}
