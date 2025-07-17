<template>
  <q-card-section class="column justify-between q-pb-none">
    <div>
      <div class="text-weight-bold">
        {{ $t("admin.users.details.submissions.filters.roles") }}
      </div>
      <div>
        <q-option-group v-model="filter" :options="tOptions" type="checkbox" />
      </div>

      Select:
      <q-btn-group flat>
        <q-btn dense no-caps label="All" @click="setAll" />

        <q-btn dense label="None" no-caps @click="setNone" />
      </q-btn-group>
    </div>
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

function reset() {
  filter.value = defaultOptions
}

function setAll() {
  filter.value = defaultOptions
}

function setNone() {
  filter.value = []
}

defineExpose({ reset, setAll, setNone })
</script>

<script>
const options = [
  { label: "admin.users.details.roles.submitter", value: "submitter" },
  { label: "admin.users.details.roles.reviewer", value: "reviewer" },
  {
    label: "admin.users.details.roles.review_coordinator",
    value: "review_coordinator"
  }
]

export const defaultOptions = options.map((o) => o.value)
</script>
