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
          name: 'submission:details',
          params: { id: props.submission.id },
        }"
      />
      <q-toolbar-title class="q-pt-xs q-pb-sm col-grow">
        <div>
          <h1 data-cy="submission_title" class="text-h3 q-ma-none">
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
        v-if="!statusChangingDisabledByRole && !statusChangingDisabledByState"
        :label="$t(`submission.toolbar.status_options`)"
        flat
        menu-anchor="bottom right"
        menu-self="top right"
        data-cy="status-dropdown"
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
          v-else-if="
            submission.status != 'AWAITING_REVIEW' &&
            submission.status != 'REJECTED' &&
            submission.status != 'RESUBMISSION_REQUESTED' &&
            submission.status != 'ACCEPTED_AS_FINAL' &&
            submission.status != 'ARCHIVED'
          "
          flat
          square
          data-cy="decision_options"
          class="column q-pa-sm"
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

        <q-btn-group
          v-if="submission.status == 'AWAITING_REVIEW'"
          flat
          square
          class="column q-pa-sm"
        >
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
        </q-btn-group>

        <q-btn-group
          v-if="submission.status == 'ACCEPTED_AS_FINAL'"
          data-cy="decision_options"
          flat
          square
          class="column q-pa-sm"
        >
          <q-btn
            v-if="submission.status == 'ACCEPTED_AS_FINAL'"
            data-cy="archive"
            rounded
            color="dark-grey"
            :label="$t(`submission.action.archive`)"
            class=""
            @click="confirmHandler('archive')"
          >
          </q-btn>
          <q-btn
            v-if="submission.status == 'ACCEPTED_AS_FINAL'"
            data-cy="delete"
            rounded
            color="negative"
            :label="$t(`submission.action.delete`)"
            class=""
            @click="confirmHandler('delete')"
          >
          </q-btn>
        </q-btn-group>

        <q-btn-group
          v-if="submission.status == 'ARCHIVED'"
          data-cy="decision_options"
          flat
          square
          class="column q-pa-sm"
        >
          <q-btn
            v-if="submission.status == 'ARCHIVED'"
            data-cy="delete"
            rounded
            color="negative"
            :label="$t(`submission.action.delete`)"
            class=""
            @click="confirmHandler('delete')"
          >
          </q-btn>
        </q-btn-group>
      </q-btn-dropdown>
      <q-icon
        v-if="isDisabledByRole || isDisabledByState"
        data-cy="submission_export_btn"
        name="exit_to_app"
        size="sm"
        color="disabled"
        class="q-ma-xs cursor-not-allowed"
        style="opacity: 0.5"
      >
        <q-tooltip v-if="isDisabledByRole">{{
          $t(`export.disabled.by_role`)
        }}</q-tooltip>
        <q-tooltip v-else-if="isDisabledByState">{{
          $t(`export.disabled.by_state`)
        }}</q-tooltip>
      </q-icon>
      <q-btn
        v-else
        data-cy="submission_export_btn"
        :aria-label="$t(`export.call_to_action`)"
        icon="exit_to_app"
        dense
        flat
        round
        :to="{
          name: 'submission:export',
          params: { id: submission.id },
        }"
      >
        <q-tooltip>{{ $t(`export.call_to_action`) }}</q-tooltip>
      </q-btn>
      <q-btn
        :aria-label="$t('submission.toolbar.toggle_annotation_highlights')"
        dense
        flat
        round
        icon="power_input"
        @click="toggleAnnotationHighlights"
      >
        <q-tooltip class="text-center" max-width="100px">{{
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
        <q-tooltip class="text-center" max-width="100px">{{
          $t("submission.toolbar.toggle_inline_comments")
        }}</q-tooltip>
      </q-btn>
    </q-toolbar>
  </q-header>
</template>
<script setup>
import ConfirmStatusChangeDialog from "../dialogs/ConfirmStatusChangeDialog.vue"
import { useQuasar } from "quasar"
import {
  useSubmissionExport,
  useStatusChangeControls,
} from "src/use/guiElements.js"
import { ref } from "vue"

const { dialog } = useQuasar()

const submissionRef = ref(props.submission)
const { isDisabledByRole, isDisabledByState } =
  useSubmissionExport(submissionRef)
const { statusChangingDisabledByRole, statusChangingDisabledByState } =
  useStatusChangeControls(submissionRef)

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
