<template>
  <q-header reveal class="bg-grey-9 text-white">
    <q-toolbar>
      <q-btn
        dense
        aria-label="Back to Submission Details"
        flat
        round
        icon="arrow_back_ios_new"
        :to="{
          name: 'submission_details',
          params: { id: props.id },
        }"
      />
      <q-toolbar-title>
        {{ submission.title }}
      </q-toolbar-title>

      <q-btn
        aria-label="Toggle Annotation Highlights"
        dense
        flat
        round
        icon="power_input"
        @click="toggleAnnotationHighlights"
      />
      <q-btn
        aria-label="Toggle Inline Comments"
        dense
        flat
        round
        icon="question_answer"
        data-cy="toggleInlineCommentsButton"
        @click="toggleCommentDrawer"
      />
    </q-toolbar>
  </q-header>
</template>
<script setup>
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
</script>
