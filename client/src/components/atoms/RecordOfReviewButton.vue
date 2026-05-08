<template>
  <div v-if="isDisabledByState">
    <q-btn disabled :label="$t('record_of_review.title')" icon="exit_to_app" />
    <q-icon name="info" size="sm" class="q-ml-sm">
      <q-tooltip v-if="isDisabledByState">{{
        $t(`record_of_review.disabled.by_state`)
      }}</q-tooltip>
    </q-icon>
  </div>
  <div v-else>
    <q-btn
      :label="$t('record_of_review.title')"
      color="accent"
      icon="exit_to_app"
      :to="{
        name: 'account:record_of_review',
        params: { id: 100 }
      }"
    />
  </div>
</template>
<script setup lang="ts">
import { useSubmissionExport } from "src/use/guiElements"
import { toRef } from "vue"
import type { Submission } from "src/graphql/generated/graphql"

interface Props {
  submission: Submission
}

const props = defineProps<Props>()
const submissionRef = toRef(props, "submission")
const { isDisabledByState } = useSubmissionExport(submissionRef)
</script>
