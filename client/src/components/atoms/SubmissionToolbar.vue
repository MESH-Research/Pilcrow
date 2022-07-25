<template>
  <q-header reveal class="bg-grey-9 text-white">
    <q-toolbar>
      <q-btn
        :aria-label="$t('submission.toolbar.back')"
        dense
        flat
        round
        icon="arrow_back_ios_new"
        :to="{ name: 'submission_details', params: { id: props.id } }"
      />
      <q-toolbar-title class="q-pt-xs q-pb-sm">
        <div class="flex">
          {{ submission.title }}
        </div>
        <div class="flex">
          <q-badge align="middle">Initially Submitted</q-badge>
        </div>
      </q-toolbar-title>
      <q-btn
        rounded
        color="positive"
        label="Accept for Review"
        class="q-ml-md"
        @click="confirmHandler('accept_for_review')"
      >
      </q-btn>
      <q-btn
        rounded
        color="deep-orange"
        label="Request Resubmit"
        class="q-ml-md"
        @click="confirmHandler('request_resubmission')"
      >
      </q-btn>
      <q-btn
        rounded
        color="negative"
        label="Reject"
        class="q-ml-md"
        @click="confirmHandler('reject')"
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
  id: {
    type: String,
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
  // fire dialog
  // todo conditional logic
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
    },
  })
}
</script>
