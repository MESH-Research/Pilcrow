<template>
  <q-card-section class="column justify-between q-pb-none">
    <div>
      <div class="row items-center justify-between q-mb-xs">
        <div class="text-weight-bold">
          {{ $t("submissions.filters.roles_header") }}
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
      <div>
        <q-option-group v-model="filter" :options="tOptions" type="checkbox" />
      </div>
    </div>
  </q-card-section>
</template>

<script lang="ts">
interface RoleOption {
  label: string
  value: string
}

const options: RoleOption[] = [
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
