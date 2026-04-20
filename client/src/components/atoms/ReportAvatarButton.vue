<template>
  <q-btn
    v-if="shouldShow"
    flat
    dense
    round
    icon="flag"
    color="grey-7"
    :aria-label="$t('dialog.reportAvatar.button')"
    data-cy="report_avatar_button"
    @click.stop="open"
  >
    <q-tooltip>{{ $t("dialog.reportAvatar.button") }}</q-tooltip>
  </q-btn>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { Dialog, Notify } from "quasar"
import { useI18n } from "vue-i18n"
import { useMutation } from "@vue/apollo-composable"
import ReportAvatarDialog from "../dialogs/ReportAvatarDialog.vue"
import { REPORT_USER_AVATAR } from "src/graphql/mutations"
import { useCurrentUser } from "src/use/user"

interface AvatarData {
  url?: string | null
}
interface Props {
  user: { id?: string | number; avatar?: AvatarData | null }
}
const props = defineProps<Props>()

const { t } = useI18n()
const { currentUser } = useCurrentUser()

/**
 * Only show the button when:
 *   - a current user is logged in,
 *   - the target user has an uploaded avatar (nothing to report otherwise),
 *   - the target is not the current user (you cannot report yourself).
 */
const shouldShow = computed(() => {
  if (!currentUser.value) return false
  if (!props.user.id) return false
  if (!props.user.avatar?.url) return false
  return String(currentUser.value.id) !== String(props.user.id)
})

const { mutate: reportAvatar } = useMutation(REPORT_USER_AVATAR)

function open() {
  Dialog.create({ component: ReportAvatarDialog }).onOk(
    async ({ reason }: { reason: string | null }) => {
      try {
        await reportAvatar({ userId: props.user.id, reason })
        Notify.create({
          type: "positive",
          message: t("dialog.reportAvatar.success")
        })
      } catch {
        Notify.create({
          type: "negative",
          message: t("dialog.reportAvatar.failure")
        })
      }
    }
  )
}
</script>
