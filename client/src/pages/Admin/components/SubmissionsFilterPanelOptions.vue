<template>
  <q-card-section class="q-pb-none">
    <div class="row items-center justify-between q-mb-xs">
      <div class="text-weight-bold">
        {{ t(headerLabel) }}
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
export interface FilterOption {
  label: string
  value: string
  default?: boolean
}
</script>

<script setup lang="ts">
import { computed } from "vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const filter = defineModel<string[]>({ default: () => [] })

interface Props {
  headerLabel: string
  options: FilterOption[]
  allowedValues?: string[]
}
const props = defineProps<Props>()

const allValues = computed(() => props.options.map((o) => o.value))

const tOptions = computed(() =>
  props.options
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
    ? allValues.value.filter((v) => props.allowedValues!.includes(v))
    : allValues.value
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
