<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row no-wrap">
        <div class="q-pa-sm q-pr-md column">
          <q-avatar icon="forward_to_inbox" color="accent" text-color="white" />
        </div>
        <div class="column justify-center q-my-sm q-mb-none">
          <p class="q-mb-none">
            You are about to reinvite a user to this submission
          </p>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="column items-center">
          <p>Add an optional message with your invitation.</p>
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

<script setup>
import { useDialogPluginComponent, useQuasar } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import { INVITE_REVIEWER } from "src/graphql/mutations"
import { ref } from "vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const { notify } = useQuasar()

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const props = defineProps({
  action: {
    type: String,
    required: false,
    default: null,
  },
  submissionId: {
    type: String,
    required: true,
  },
})

const comment = ref(null)

const { mutate } = useMutation(INVITE_REVIEWER)

async function reinviteUser() {
  try {
    await mutate({
      id: String(props.submissionId),
      email: "",
      message: comment.value,
    })
    notify({
      color: "positive",
      message: t(`dialog.confirmStatusChange.statusChanged.${props.action}`),
      icon: "done",
      attrs: {
        "data-cy": "change_status_notify",
      },
    })
  } catch (error) {
    notify({
      color: "negative",
      message: t("dialog.confirmStatusChange.unauthorized"),
      icon: "error",
      attrs: {
        "data-cy": "change_status_notify",
      },
    })
  }
}
</script>
