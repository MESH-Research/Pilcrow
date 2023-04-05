<template>
  <q-header
    reveal
    class="bg-light-grey text-black"
    data-cy="submission-toolbar"
  >
    <q-toolbar class="shadow-3 review-header">
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
      <q-toolbar-title class="q-pt-xs q-pb-sm col-grow">
        <div>
          <h1 data-cy="submussion_title" class="text-h3 q-ma-none">
            {{ submission.title }}
          </h1>
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

      <q-btn-dropdown
        label="Options"
        flat
        menu-anchor="bottom middle"
        menu-self="top middle"
      >
        <div v-if="submission.status == 'DRAFT'">
          <q-btn
            data-cy="initially_submit"
            rounded
            color="positive"
            :label="$t(`submission.action.submit_for_review`)"
            class="q-ml-md"
            @click="confirmHandler('submit_for_review')"
          >
          </q-btn>
        </div>
        <q-btn-group
          flat
          square
          v-else-if="
            submission.status != 'AWAITING_REVIEW' &&
            submission.status != 'REJECTED' &&
            submission.status != 'RESUBMISSION_REQUESTED'
          "
          data-cy="decision_options"
          class="column"
        >
          <q-btn
            v-if="submission.status == 'INITIALLY_SUBMITTED'"
            data-cy="accept_for_review"
            color="positive"
            :label="$t(`submission.action.accept_for_review`)"
            class=""
            @click="confirmHandler('accept_for_review')"
          >
          </q-btn>

          <q-btn
            v-if="submission.status != 'INITIALLY_SUBMITTED'"
            data-cy="accept_as_final"
            rounded
            color="positive"
            :label="$t(`submission.action.accept_as_final`)"
            class=""
            @click="confirmHandler('accept_as_final')"
          >
          </q-btn>
          <q-btn
            rounded
            :label="$t(`submission.action.request_resubmission`)"
            class="text-white request-resubmission"
            color="dark-grey"
            @click="confirmHandler('request_resubmission')"
          >
          </q-btn>
          <q-btn
            rounded
            color="negative"
            :label="$t(`submission.action.reject`)"
            class=""
            @click="confirmHandler('reject')"
          >
          </q-btn>

          <q-btn
            v-if="submission.status == 'AWAITING_REVIEW'"
            data-cy="open_for_review"
            rounded
            color="black"
            :label="$t(`submission.action.open`)"
            class=""
            @click="confirmHandler('open')"
          >
          </q-btn>
          <q-btn
            v-if="submission.status == 'UNDER_REVIEW'"
            data-cy="close_for_review"
            rounded
            color="black"
            :label="$t(`submission.action.close`)"
            class=""
            @click="confirmHandler('close')"
          >
          </q-btn>
        </q-btn-group>
      </q-btn-dropdown>

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
