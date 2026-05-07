<template>
  <q-card-section class="q-pb-none">
    <q-card-section class="text-weight-bold q-pa-none">
      {{ $t("admin.users.details.submissions.filters.status") }}
    </q-card-section>
    <q-option-group v-model="filter" :options="tOptions" type="checkbox" />
  </q-card-section>
</template>

<script lang="ts">
interface StatusOption {
  label: string
  value: string
  default?: boolean
}

const options: StatusOption[] = [
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
import { computed } from "vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const filter = defineModel<string[]>({ default: () => [] })

interface Props {
  allowedValues?: string[]
}
const props = defineProps<Props>()

const tOptions = computed(() =>
  options
    .filter(
      (s) => !props.allowedValues || props.allowedValues.includes(s.value)
    )
    .map((s) => ({
      label: s.label ? t(s.label) : s.label,
      value: s.value
    }))
)
</script>
