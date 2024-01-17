<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row items-center">
        <div class="q-pa-sm q-pr-md column">
          <q-avatar icon="delete" color="primary" text-color="white" />
        </div>
        <span class="q-ml-sm">{{ $t(`dialog.deleteComment.body`) }}</span>
      </q-card-section>
      <q-card-section>
        <!-- eslint-disable vue/no-v-html -->
        <blockquote
          class="q-mt-none"
          v-html="props.comment.content"
        ></blockquote>
        <!--  eslint-enable vue/no-v-html -->
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t(`dialog.deleteComment.cancel`)"
          color="primary"
          data-cy="dirtyCancel"
          @click="onDialogCancel"
        />
        <q-btn
          :label="$t(`dialog.deleteComment.delete`)"
          color="negative"
          data-cy="dirtyDelete"
          @click="onDialogOK(deleteComment())"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { useDialogPluginComponent, useQuasar } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import {
  DELETE_INLINE_COMMENT,
  DELETE_OVERALL_COMMENT,
} from "src/graphql/mutations"
import { useI18n } from "vue-i18n"

const props = defineProps({
  comment: {
    type: Object,
    default: () => {},
    required: false,
  },
  submissionId: {
    type: String,
    required: true,
  },
})
const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const mutation =
  props.comment.__typename.startsWith("InlineComment")
    ? DELETE_INLINE_COMMENT
    : DELETE_OVERALL_COMMENT
const { mutate } = useMutation(mutation)
const { notify } = useQuasar()
const { t } = useI18n()

async function deleteComment() {
  try {
    await mutate(
      {
        comment_id: String(props.comment.id),
        submission_id: String(props.submissionId),
      },
      {
        refetchQueries: ["GetSubmissionReview"],
      },
    )
  } catch (error) {
    notify({
      color: "negative",
      message: t("dialog.deleteComment.failure"),
      icon: "error",
      attrs: {
        "data-cy": "delete_comment_notify",
      },
    })
  }
}

defineEmits([...useDialogPluginComponent.emits])
</script>
