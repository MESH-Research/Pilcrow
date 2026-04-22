import { computed } from "vue"
import { useAvatarReportsPendingCount } from "./avatarReports"

/**
 * Single boolean signal for "something in the admin area wants your
 * eyes". Header dot binds to this rather than surfacing a per-
 * feature count badge, so we can add more sources (pending user
 * reports, unreviewed publications, etc.) without multiplying
 * indicators in the header.
 *
 * Each contributing source is a reactive condition; if any of them
 * is truthy, `needsAttention` is true.
 */
export function useAdminNeedsAttention() {
  const { count: pendingAvatarReports } = useAvatarReportsPendingCount()

  const needsAttention = computed(() => pendingAvatarReports.value > 0)

  return { needsAttention }
}
