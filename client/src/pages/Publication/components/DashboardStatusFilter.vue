<template>
  <q-card flat bordered>
    <q-card-section class="q-pb-none">
      <div class="text-weight-bold">
        {{ $t("publication.dashboard.filters.status") }}
      </div>
      <q-option-group v-model="filter" :options="tOptions" type="checkbox" />
      <q-separator />
      <div class="q-py-xs">
        Select:
        <q-btn-group flat>
          <q-btn dense no-caps label="All" @click="setAll" />
          <q-btn dense no-caps label="None" @click="setNone" />
          <q-btn dense no-caps label="Active" @click="resetToDefault" />
        </q-btn-group>
      </div>
    </q-card-section>
  </q-card>
</template>

<script lang="ts">
interface StatusOption {
  label: string
  value: string
  default?: boolean
}

const options: StatusOption[] = [
  { label: "submission.status.DRAFT", value: "DRAFT" },
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
    label: "submission.status.RESUBMITTED",
    value: "RESUBMITTED",
    default: true
  },
  {
    label: "submission.status.AWAITING_REVIEW",
    value: "AWAITING_REVIEW",
    default: true
  },
  {
    label: "submission.status.UNDER_REVIEW",
    value: "UNDER_REVIEW",
    default: true
  },
  {
    label: "submission.status.AWAITING_DECISION",
    value: "AWAITING_DECISION",
    default: true
  },
  {
    label: "submission.status.REVISION_REQUESTED",
    value: "REVISION_REQUESTED",
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

export const defaultStatuses = options
  .filter((s) => s.default)
  .map((s) => s.value)

const allStatuses = options.map((s) => s.value)
</script>

<script setup lang="ts">
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const filter = defineModel<string[]>({ default: () => [] })

const tOptions = options.map((s) => ({
  label: t(s.label),
  value: s.value
}))

function resetToDefault() {
  filter.value = [...defaultStatuses]
}

function setAll() {
  filter.value = [...allStatuses]
}

function setNone() {
  filter.value = []
}

defineExpose({ resetToDefault, setAll, setNone })
</script>
