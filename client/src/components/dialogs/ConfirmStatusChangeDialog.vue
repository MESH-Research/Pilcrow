<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row no-wrap">
        <div class="q-pa-sm q-pr-md column">
          <q-avatar
            :icon="icons[props.action]"
            :color="colors[props.action]"
            text-color="white"
          />
        </div>
        <div class="column justify-center q-my-sm q-mb-none">
          <p class="q-mb-none">
            <i18n-t
              :keypath="`dialog.confirmStatusChange.body.${props.action}`"
              tag="span"
            >
            </i18n-t>
          </p>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="column items-center">
          <p>
            <i18n-t
              :keypath="`dialog.confirmStatusChange.comment`"
              tag="span"
            />
          </p>
        </div>
        <q-input
          v-model="comment"
          filled
          data-cy="status_change_comment"
          :label="$t('dialog.confirmStatusChange.comment_placeholder')"
          type="textarea"
        />
      </q-card-section>

      <q-card-actions align="around" class="q-pb-md">
        <q-btn
          data-cy="dirtyYesChangeStatus"
          :label="$t(`dialog.confirmStatusChange.action.${props.action}`)"
          color="primary"
          @click="onDialogOK(updateStatus())"
        />
        <q-btn
          data-cy="dirtyNoCancelChangeStatus"
          :label="$t('dialog.confirmStatusChange.action.cancel')"
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
import { UPDATE_SUBMISSION_STATUS } from "src/graphql/mutations"
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

const statuses = {
  submit_for_review: "INITIALLY_SUBMITTED",
  accept_for_review: "AWAITING_REVIEW",
  reject: "REJECTED",
  request_resubmission: "RESUBMISSION_REQUESTED",
  open: "UNDER_REVIEW",
  accept_as_final: "ACCEPTED_AS_FINAL",
  close: "AWAITING_DECISION",
  archive: "ARCHIVED",
  delete: "DELETED"
}

const icons = {
  submit_for_review: "edit_document",
  accept_for_review: "done",
  reject: "do_not_disturb",
  request_resubmission: "refresh",
  open: "grading",
  close: "grading",
  accept_as_final: "done",
  archive: "archive",
  delete: "delete"
}

const colors = {
  submit_for_review: "positive",
  accept_for_review: "positive",
  reject: "negative",
  request_resubmission: "dark-grey",
  open: "black",
  close: "black",
  accept_as_final: "positive",
  archive: "dark-grey",
  delete: "negative"
}
const comment = ref(null)

const { mutate } = useMutation(UPDATE_SUBMISSION_STATUS)

async function updateStatus() {
  try {
    await mutate({
      id: String(props.submissionId),
      status: statuses[props.action],
      status_change_comment: comment.value,
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
