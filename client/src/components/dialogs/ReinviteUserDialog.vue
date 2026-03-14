<template>
  <q-dialog
    ref="dialogRef"
    :aria-label="$t(`dialog.reinviteUser.aria_label`)"
    @hide="onDialogHide"
  >
    <q-card>
      <q-card-section class="row no-wrap">
        <div class="q-pa-sm q-pr-md column">
          <q-avatar icon="forward_to_inbox" color="accent" text-color="white" />
        </div>
        <div class="column justify-center q-my-sm q-mb-none">
          <p class="q-mb-none">
            {{
              $t(`dialog.reinviteUser.description`, {
                email: email,
                role: $t(`role.${roleGroup}`, 1)
              })
            }}
          </p>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="column items-center">
          <p>{{ $t(`dialog.reinviteUser.optional_message`) }}</p>
        </div>
        <q-input
          v-model="comment"
          filled
          data-cy="reinvite_comment"
          :label="$t(`submissions.invite_user.message.placeholder`)"
          type="textarea"
        />
      </q-card-section>

      <q-card-actions align="around" class="q-pb-md">
        <q-btn
          data-cy="dirtyYesReinviteUser"
          label="Reinvite User"
          color="primary"
          @click="onDialogOK(reinviteUser())"
        />
        <q-btn
          data-cy="dirtyNoCancelReinviteUser"
          :label="$t('guiElements.form.cancel')"
          color="grey"
          flat
          @click="onDialogCancel"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { useDialogPluginComponent } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import {
  REINVITE_REVIEWER,
  REINVITE_REVIEW_COORDINATOR
} from "src/graphql/mutations"
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"
import { useFeedbackMessages } from "src/use/guiElements"

const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "reinvite_notify",
    role: "alert",
    "aria-label": t("notifications.active_notification")
  }
})

// eslint-disable-next-line vue/define-emits-declaration
defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

interface Props {
  roleGroup: string
  email: string
  submissionId: string
}

const props = defineProps<Props>()

const comment = ref(null)

const opts = {
  variables: {
    id: props.submissionId,
    email: props.email,
    message: null as string | null
  }
}
const mutations = {
  reviewers: REINVITE_REVIEWER,
  review_coordinators: REINVITE_REVIEW_COORDINATOR
}
const setMutationType = computed(() => {
  return mutations[props.roleGroup]
})
const { mutate } = useMutation(setMutationType, opts)

async function reinviteUser() {
  try {
    await mutate({
      id: props.submissionId,
      email: props.email,
      message: comment.value
    })
    newStatusMessage(
      "success",
      t(`dialog.reinviteUser.success`, {
        email: props.email,
        role: t(`role.${props.roleGroup}`, 1)
      })
    )
  } catch (error) {
    newStatusMessage(
      "failure",
      t(`dialog.reinviteUser.failure`, {
        email: props.email
      })
    )
  }
}
</script>
