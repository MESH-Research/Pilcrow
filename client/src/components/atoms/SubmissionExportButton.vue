<template>
  <q-btn
    v-if="isDisabledByRole || isDisabledByState"
    :label="$t(`export.call_to_action`)"
    color="grey-7"
    icon="exit_to_app"
    class="cursor-not-allowed"
  >
    <q-tooltip v-if="isDisabledByState" class="text-body1">{{
      $t(`export.disabled.by_state`)
    }}</q-tooltip>
    <q-tooltip v-else-if="isDisabledByRole" class="text-body1">{{
      $t(`export.disabled.by_role`)
    }}</q-tooltip>
  </q-btn>
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
    default: () => {},
  },
})
const submissionRef = toRef(props, "submission")
const { isDisabledByRole, isDisabledByState } =
  useSubmissionExport(submissionRef)
</script>
