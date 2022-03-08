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
        aria-label="Toggle Annotations"
        dense
        flat
        round
        icon="power_input"
      />
      <q-btn
        aria-label="Toggle Inline Comments"
        dense
        flat
        round
        icon="question_answer"
        @click="toggleCommentDrawer"
      />
    </q-toolbar>
  </q-header>
</template>
<script setup>
const props = defineProps({
  // Drawer status
  modelValue: {
    type: Boolean,
    default: null,
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
const emit = defineEmits(["update:modelValue"])
function toggleCommentDrawer() {
  emit("update:modelValue", !props.modelValue)
}
</script>
