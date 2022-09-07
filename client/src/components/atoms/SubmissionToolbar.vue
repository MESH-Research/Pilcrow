<template>
  <q-header reveal class="info text-black">
    <q-toolbar>
      <q-btn
        :aria-label="$t('submission.toolbar.back')"
        dense
        flat
        round
        icon="arrow_back_ios_new"
        :to="{
          name: 'submission_details',
          params: { id: props.submission.id },
        }"
      />
      <q-toolbar-title class="q-pt-xs q-pb-sm">
        <div>
          <h1 class="text-h3 q-ma-none">{{ submission.title }}</h1>
          <q-chip
            data-cy="submission_status"
            class="q-ma-none"
            icon="radio_button_checked"
            color="primary"
            text-color="white"
          >
            {{ $t(`submission.status.${submission.status}`) }}
          </q-chip>
        </div>
      </q-toolbar-title>
      <div
        v-if="submission.status != 'AWAITING_REVIEW'"
        data-cy="decision_options"
      >
        <q-btn
          v-if="submission.status == 'INITIALLY_SUBMITTED'"
          data-cy="accept_for_review"
          rounded
          color="positive"
          :label="$t(`submission.action.accept_for_review`)"
          class="q-ml-md"
          @click="confirmHandler('accept_for_review')"
        >
        </q-btn>
        <q-btn
          v-if="submission.status != 'INITIALLY_SUBMITTED'"
          data-cy="accept_as_final"
          rounded
          color="positive"
          :label="$t(`submission.action.accept_as_final`)"
          class="q-ml-md"
          @click="confirmHandler('accept_as_final')"
        >
        </q-btn>
        <q-btn
          rounded
          :label="$t(`submission.action.request_resubmission`)"
          class="dark-grey q-ml-md text-white"
          @click="confirmHandler('request_resubmission')"
        >
        </q-btn>
        <q-btn
          rounded
          color="negative"
          :label="$t(`submission.action.reject`)"
          class="q-ml-md"
          @click="confirmHandler('reject')"
        >
        </q-btn>
      </div>
      <q-btn
        v-if="submission.status == 'AWAITING_REVIEW'"
        data-cy="open_for_review"
        rounded
        color="black"
        :label="$t(`submission.action.open`)"
        class="q-ml-md"
        @click="confirmHandler('open')"
      >
      </q-btn>
      <q-btn
        v-if="submission.status == 'UNDER_REVIEW'"
        data-cy="close_for_review"
        rounded
        color="black"
        :label="$t(`submission.action.close`)"
        class="q-ml-md"
        @click="confirmHandler('close')"
      >
      </q-btn>
      <q-space></q-space>
      <q-btn
        :aria-label="$t('submission.toolbar.toggle_annotation_highlights')"
        dense
        flat
        round
        icon="power_input"
        @click="toggleAnnotationHighlights"
      >
        <q-tooltip>{{
          $t("submission.toolbar.toggle_annotation_highlights")
        }}</q-tooltip>
      </q-btn>
      <q-btn
        :aria-label="$t('submission.toolbar.toggle_inline_comments')"
        dense
        flat
        round
        icon="question_answer"
        data-cy="toggleInlineCommentsButton"
        @click="toggleCommentDrawer"
      >
        <q-tooltip>{{
          $t("submission.toolbar.toggle_inline_comments")
        }}</q-tooltip>
      </q-btn>
    </q-toolbar>
  </q-header>
</template>
<script setup>
import ConfirmStatusChangeDialog from "../dialogs/ConfirmStatusChangeDialog.vue"
import { useQuasar } from "quasar"

const { dialog } = useQuasar()

const props = defineProps({
  // Drawer status
  commentDrawerOpen: {
    type: Boolean,
    default: null,
  },
  highlightVisibility: {
    type: Boolean,
    default: true,
  },
  submission: {
    type: Object,
    default: null,
  },
})
const emit = defineEmits([
  "update:commentDrawerOpen",
  "update:highlightVisibility",
])
function toggleCommentDrawer() {
  emit("update:commentDrawerOpen", !props.commentDrawerOpen)
}
function toggleAnnotationHighlights() {
  emit("update:highlightVisibility", !props.highlightVisibility)
}
async function confirmHandler(action) {
  await new Promise((resolve) => {
    dirtyDialog(action)
      .onOk(function () {
        resolve(true)
      })
      .onCancel(function () {
        resolve(false)
      })
  })
  {
    return
  }
}
function dirtyDialog(action) {
  return dialog({
    component: ConfirmStatusChangeDialog,
    componentProps: {
      action: action,
      submissionId: props.submission.id,
    },
  })
}
</script>
