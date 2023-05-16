<template>
  <div v-if="isDisabledByRole || isDisabledByState">
    <q-btn
      disabled
      :label="$t(`export.call_to_action`)"
      icon="exit_to_app"
      data-cy="submission_export_btn"
    >
    </q-btn>
    <q-icon name="info" size="sm" class="q-ml-sm">
      <q-tooltip v-if="isDisabledByRole" class="text-body1">{{
        $t(`export.disabled.by_role`)
      }}</q-tooltip>
      <q-tooltip v-else-if="isDisabledByState" class="text-body1">{{
        $t(`export.disabled.by_state`)
      }}</q-tooltip>
    </q-icon>
  </div>
  <q-btn
    v-else
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
import { toRef } from "vue"
const props = defineProps({
  submission: {
    type: Object,
    required: true,
  },
})
const submissionRef = toRef(props, "submission")
const { isDisabledByRole, isDisabledByState } =
  useSubmissionExport(submissionRef)
</script>
