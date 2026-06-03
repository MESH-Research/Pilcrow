<template>
  <q-card-section class="q-pb-none">
    <div class="row items-center justify-between q-mb-xs">
      <div class="text-weight-bold">
        {{ $t("submissions.filters.status_header") }}
      </div>
      <q-btn-group flat>
        <q-btn
          dense
          flat
          no-caps
          size="sm"
          :label="$t('admin.filters.all')"
          @click="selectAll"
        />
        <q-btn
          dense
          flat
          no-caps
          size="sm"
          :label="$t('admin.filters.none')"
          @click="selectNone"
        />
        <q-btn
          dense
          flat
          no-caps
          size="sm"
          :label="$t('admin.filters.invert')"
          @click="invert"
        />
      </q-btn-group>
    </div>
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

const allowedDefaults = computed(() =>
  props.allowedValues
    ? defaultOptions.filter((v) => props.allowedValues!.includes(v))
    : defaultOptions
)

function selectAll() {
  filter.value = [...allowedDefaults.value]
}
function selectNone() {
  filter.value = []
}
function invert() {
  filter.value = allowedDefaults.value.filter((v) => !filter.value.includes(v))
}
</script>
