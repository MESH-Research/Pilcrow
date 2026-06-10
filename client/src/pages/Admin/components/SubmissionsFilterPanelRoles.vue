<template>
  <submissions-filter-panel-options
    v-model="filter"
    class="column justify-between"
    header-label="submissions.filters.roles_header"
    :options="options"
    :allowed-values="allowedValues"
  />
</template>

<script lang="ts">
import type { FilterOption } from "./SubmissionsFilterPanelOptions.vue"

const options: FilterOption[] = [
  { label: "submissions.filters.role_options.submitter", value: "submitter" },
  { label: "submissions.filters.role_options.reviewer", value: "reviewer" },
  {
    label: "submissions.filters.role_options.review_coordinator",
    value: "review_coordinator"
  }
]

export const defaultOptions = options.map((o) => o.value)
</script>

<script setup lang="ts">
import SubmissionsFilterPanelOptions from "./SubmissionsFilterPanelOptions.vue"

const filter = defineModel<string[]>({ default: () => [] })

interface Props {
  allowedValues?: string[]
}
defineProps<Props>()
</script>
