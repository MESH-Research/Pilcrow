<template>
  <q-dialog
    ref="dialogRef"
    :aria-label="$t(`dialog.deleteComment.aria_label`)"
    @hide="onDialogHide"
  >
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

<script setup lang="ts">
import { useDialogPluginComponent } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import {
  DELETE_INLINE_COMMENT,
  DELETE_OVERALL_COMMENT
} from "src/graphql/mutations"
import { useI18n } from "vue-i18n"
import { useFeedbackMessages } from "src/use/guiElements"
import type {
  InlineComment,
  InlineCommentReply,
  OverallComment,
  OverallCommentReply
} from "src/graphql/generated/graphql"

type CommentWithTypename =
  | InlineComment
  | InlineCommentReply
  | OverallComment
  | OverallCommentReply

interface Props {
  comment?: CommentWithTypename
  submissionId: string
}

const props = withDefaults(defineProps<Props>(), {
  comment: undefined
})
const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const mutation = props.comment.__typename.startsWith("InlineComment")
  ? DELETE_INLINE_COMMENT
  : DELETE_OVERALL_COMMENT
const { mutate } = useMutation(mutation)
const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "delete_comment_notify"
  }
})

async function deleteComment() {
  try {
    await mutate(
      {
        comment_id: String(props.comment.id),
        submission_id: String(props.submissionId)
      },
      {
        refetchQueries: ["GetSubmissionReview"]
      }
    )
  } catch (error) {
    newStatusMessage("failure", t("dialog.deleteComment.failure"))
  }
}

// eslint-disable-next-line vue/define-emits-declaration
defineEmits([...useDialogPluginComponent.emits])
</script>
