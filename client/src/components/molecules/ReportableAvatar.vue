<template>
  <div class="reportable-avatar">
    <avatar-image :user="user" :variant="variant" v-bind="$attrs" />
    <div
      v-if="shouldShow"
      role="button"
      tabindex="0"
      class="reportable-avatar__trigger"
      :class="{ 'reportable-avatar__trigger--compact': compact }"
      :aria-label="$t('dialog.reportAvatar.button')"
      data-cy="reportable_avatar_trigger"
      @click.stop
      @keydown.enter.prevent="openMenu"
      @keydown.space.prevent="openMenu"
    >
      <q-icon name="more_horiz" :size="compact ? '11px' : '16px'" />
      <q-menu ref="menu">
        <q-list dense style="min-width: 200px">
          <q-item
            v-close-popup
            clickable
            data-cy="reportable_avatar_menu_report"
            @click="openReport"
          >
            <q-item-section avatar>
              <q-icon name="flag" color="negative" />
            </q-item-section>
            <q-item-section>
              {{ $t("dialog.reportAvatar.button") }}
            </q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useTemplateRef } from "vue"
import { Dialog, Notify, type QMenu } from "quasar"
import { useI18n } from "vue-i18n"
import { useMutation } from "@vue/apollo-composable"
import AvatarImage from "../atoms/AvatarImage.vue"
import ReportAvatarDialog from "../dialogs/ReportAvatarDialog.vue"
import { REPORT_USER_AVATAR } from "src/graphql/mutations"
import { useCurrentUser } from "src/use/user"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

interface Props {
  user: avatarImageFragment
  /**
   * Forwarded to AvatarImage. See the same prop there for semantics.
   */
  variant?: "thumb" | "medium" | "original"
  /**
   * Render a smaller trigger tucked closer to the avatar, appropriate
   * for compact contexts like comment headers (30px avatars).
   */
  compact?: boolean
}
const props = withDefaults(defineProps<Props>(), {
  variant: "thumb",
  compact: false
})

defineOptions({ inheritAttrs: false })

const { t } = useI18n()
const { currentUser } = useCurrentUser()

/**
 * Only reveal the trigger when:
 *   - someone is logged in,
 *   - the target user has an uploaded avatar (nothing to report about
 *     an identicon),
 *   - the target is not the current user (you cannot report yourself).
 */
const shouldShow = computed(() => {
  if (!currentUser.value) return false
  if (!props.user.id) return false
  if (!props.user.avatar?.url) return false
  return String(currentUser.value.id) !== String(props.user.id)
})

const menu = useTemplateRef<QMenu>("menu")

/**
 * Open the report menu from the keyboard. The q-menu opens on pointer
 * click of the trigger by default; Enter/Space must drive it explicitly
 * so keyboard users can reach it too.
 */
function openMenu() {
  menu.value?.show()
}

const { mutate: reportAvatar } = useMutation(REPORT_USER_AVATAR)

function openReport() {
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

<style scoped>
.reportable-avatar {
  position: relative;
  display: inline-block;
  line-height: 0;
}
.reportable-avatar__trigger {
  position: absolute;
  right: -10px;
  bottom: -10px;
  z-index: 2;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  color: #555;
  border-radius: 50%;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.15s ease-in-out;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.25);
}
.reportable-avatar__trigger--compact {
  right: -2px;
  bottom: -2px;
  width: 15px;
  height: 15px;
}
.reportable-avatar:hover .reportable-avatar__trigger,
.reportable-avatar:focus-within .reportable-avatar__trigger {
  opacity: 1;
}
/* Always show on touch devices so there's a discoverable tap target. */
@media (hover: none) {
  .reportable-avatar__trigger {
    opacity: 1;
  }
}
</style>
