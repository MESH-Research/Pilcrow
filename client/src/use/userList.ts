import { useFeedbackMessages } from "src/use/guiElements"
import type { ComposerTranslation } from "vue-i18n"
import type { userListItemFragment } from "src/graphql/generated/graphql"

interface UnassignUserOptions {
  enabled: () => boolean
  mutate: (variables: { disconnect: string[] }) => Promise<unknown>
  pt: ComposerTranslation
}

/**
 * UserList action-click handler shared by the assigned-users rosters:
 * disconnect the clicked user via the roster's mutation and report the
 * outcome under the caller's i18n prefix.
 */
export function useUnassignUser({ enabled, mutate, pt }: UnassignUserOptions) {
  const { newStatusMessage } = useFeedbackMessages()

  return async function handleUserListClick({
    user
  }: {
    user: userListItemFragment
  }) {
    if (!enabled()) return
    try {
      await mutate({ disconnect: [user.id] })
      newStatusMessage(
        "success",
        pt("unassign.success", {
          display_name: user.name ? user.name : user.username
        })
      )
    } catch (error) {
      newStatusMessage("failure", pt("unassign.error"))
    }
  }
}
