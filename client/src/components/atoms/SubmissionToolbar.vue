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

      <status-change-dropdown :submission />

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
import StatusChangeDropdown from "./StatusChangeDropdown.vue"
import {
  useSubmissionExport,
  useStatusChangeControls,
} from "src/use/guiElements.js"
import { ref } from "vue"

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

const submissionRef = ref(props.submission)
const { isDisabledByRole, isDisabledByState } =
  useSubmissionExport(submissionRef)
  useStatusChangeControls(submissionRef)
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
</script>
