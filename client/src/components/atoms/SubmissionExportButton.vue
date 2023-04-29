<template>
  <q-btn
    v-if="isExportVisible"
    :disable="!isExportEnabled"
    :label="$t(`export.call_to_action`)"
    color="accent"
    icon="exit_to_app"
    :to="{
      name: 'submission_export',
      params: { id: submission.id },
    }"
  />
</template>
<script setup>
import { useSubmissionExport } from "src/use/guiElements.js"
import { ref } from "vue"
const props = defineProps({
  submission: {
    type: Object,
    default: () => {},
  },
})
const submissionRef = ref(props.submission)
const { isExportVisible, isExportEnabled } = useSubmissionExport(submissionRef.value)
</script>
