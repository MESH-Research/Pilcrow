<template>
  <submissions-filter-panel-options
    v-model="filter"
    header-label="submissions.filters.status_header"
    :options="options"
    :allowed-values="allowedValues"
  />
</template>

<script lang="ts">
import type { FilterOption } from "./SubmissionsFilterPanelOptions.vue"

const options: FilterOption[] = [
  { label: "submission.status.DRAFT", value: "DRAFT", default: false },
  {
    label: "submission.status.INITIALLY_SUBMITTED",
    value: "INITIALLY_SUBMITTED",
    default: true
  },
  {
    label: "submission.status.RESUBMISSION_REQUESTED",
    value: "RESUBMISSION_REQUESTED",
    default: true
  },
  {
    label: "submission.status.AWAITING_REVIEW",
    value: "AWAITING_REVIEW",
    default: true
  },
  {
    label: "submission.status.REJECTED",
    value: "REJECTED",
    default: true
  },
  {
    label: "submission.status.ACCEPTED_AS_FINAL",
    value: "ACCEPTED_AS_FINAL",
    default: true
  },
  { label: "submission.status.EXPIRED", value: "EXPIRED" },
  { label: "submission.status.ARCHIVED", value: "ARCHIVED" },
  { label: "submission.status.DELETED", value: "DELETED" }
]

export const defaultOptions = options.map((s) => s.value)
</script>

<script setup lang="ts">
import SubmissionsFilterPanelOptions from "./SubmissionsFilterPanelOptions.vue"

const filter = defineModel<string[]>({ default: () => [] })

interface Props {
  allowedValues?: string[]
}
defineProps<Props>()
</script>
