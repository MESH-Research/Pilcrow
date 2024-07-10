<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row no-wrap">
        <div class="q-pa-sm q-pr-md column">
          <q-avatar
            :icon="state.icon"
            :color="state.attrs.color"
            text-color="white"
          />
        </div>
        <div class="column justify-center q-my-sm q-mb-none">
          <p class="q-mb-none">
            <i18n-t
              :keypath="`dialog.confirmStatusChange.body.${props.action}`"
              tag="span"
              scope="global"
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
              scope="global"
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
import { useDialogPluginComponent } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import { UPDATE_SUBMISSION_STATUS } from "src/graphql/mutations"
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"
import {
  useFeedbackMessages,
  submissionStateButtons,
} from "src/use/guiElements"
import { useRouter } from "vue-router"

const { t } = useI18n()

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
  currentStatus: {
    type: String,
    required: true,
  },
})
const state = computed(() => {
  const match = Object.entries(submissionStateButtons).find(
    ([, value]) => value.action === props.action,
  )
  if (match === undefined) {
    return {}
  }
  return {
    ...match[1],
    status: match[0],
  }
})

const comment = ref(null)

const { mutate } = useMutation(UPDATE_SUBMISSION_STATUS)
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "change_status_notify",
  },
})
const { push } = useRouter()

async function updateStatus() {
  try {
    await mutate({
      id: String(props.submissionId),
      status: state.value.status,
      status_change_comment: comment.value,
    }).then(() => {
      if (props.currentStatus == "DRAFT") {
        push({ path: `/submission/${props.submissionId}/view/` })
      }
      if (props.currentStatus == "INITIALLY_SUBMITTED") {
        push({ path: `/submission/${props.submissionId}/review/` })
      }
    })
    newStatusMessage(
      "success",
      t(`dialog.confirmStatusChange.statusChanged.${props.action}`),
    )
  } catch (error) {
    newStatusMessage("failure", t("dialog.confirmStatusChange.unauthorized"))
  }
}
</script>
