<template>
  <q-card-section class="q-pb-none">
    <q-card-section class="text-weight-bold q-pa-none">
      {{ $t("admin.users.details.submissions.filters.status") }}
    </q-card-section>
    <q-option-group v-model="filter" :options="tOptions" type="checkbox" />
    <q-separator />
    <q-card-section class="q-pa-none">
      <div>
        Select:
        <q-btn-group flat>
          <q-btn dense no-caps label="All" @click="setAll" />
          <q-btn dense label="None" no-caps @click="setNone" />
        </q-btn-group>
      </div>
    </q-card-section>
  </q-card-section>
</template>

<script setup>
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const filter = defineModel({
  type: Array,
  default: () => [],
  required: true
})

const tOptions = options.map((s) => ({
  label: s.label ? t(s.label) : s.label,
  value: s.value
}))

const defaultFilter = defaultOptions
const allFilter = options.map((s) => s.value)

function reset() {
  filter.value = defaultFilter
}
function setAll() {
  filter.value = allFilter
}

function setNone() {
  filter.value = []
}

defineExpose({ reset, setAll, setNone })
</script>
<script>
const options = [
  { label: "submission_status.draft", value: "DRAFT", default: false },
  {
    label: "submission_status.initially_submitted",
    value: "INITIALLY_SUBMITTED",
    default: true
  },
  {
    label: "submission_status.resubmission_requested",
    value: "RESUBMISSION_REQUESTED",
    default: true
  },
  {
    label: "submission_status.awaiting_review",
    value: "AWAITING_REVIEW",
    default: true
  },
  { label: "submission_status.rejected", value: "REJECTED", default: true },
  {
    label: "submission_status.accepted_as_final",
    value: "ACCEPTED_AS_FINAL",
    default: true
  },
  { label: "submission_status.expired", value: "EXPIRED" },
  { label: "submission_status.archived", value: "ARCHIVED" },
  { label: "submission_status.deleted", value: "DELETED" }
]

export const defaultOptions = options
  .filter((s) => s.default)
  .map((s) => s.value)
</script>
