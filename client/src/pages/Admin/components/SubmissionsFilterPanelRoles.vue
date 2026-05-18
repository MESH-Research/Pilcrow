<template>
  <q-card-section class="column justify-between q-pb-none">
    <div>
      <div class="text-weight-bold">
        {{ $t("submissions.filters.roles_header") }}
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
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const filter = defineModel<string[]>({ default: () => [] })

const tOptions = options.map((s) => ({
  label: s.label ? t(s.label) : s.label,
  value: s.value
}))
</script>
