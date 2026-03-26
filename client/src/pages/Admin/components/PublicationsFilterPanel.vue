<template>
  <q-btn icon="o_filter_alt" label="Filter">
    <q-badge v-if="filterActive" floating rounded color="orange" />
    <q-menu>
      <q-card-section>
        <div class="text-weight-bold q-mb-sm">Visibility</div>
        <q-option-group
          v-model="visibilityFilter"
          :options="visibilityOptions"
          type="checkbox"
        />
        <q-separator class="q-my-sm" />
        <div class="text-weight-bold q-mb-sm">Accepting Submissions</div>
        <q-option-group
          v-model="acceptingFilter"
          :options="acceptingOptions"
          type="checkbox"
        />
      </q-card-section>
      <q-separator />
      <q-card-section class="justify-end row">
        <q-btn no-caps label="Reset Filters" @click="resetFilters" />
      </q-card-section>
    </q-menu>
  </q-btn>
  <q-btn
    v-if="filterActive"
    class="q-mx-md"
    label="Reset filters"
    flat
    no-caps
    @click="resetFilters"
  />
</template>

<script lang="ts">
export const defaultVisibility = ["public", "hidden"]
export const defaultAccepting = ["yes", "no"]
</script>

<script setup lang="ts">
import { computed } from "vue"

const visibilityFilter = defineModel<string[]>("visibilityFilter", {
  default: () => []
})
const acceptingFilter = defineModel<string[]>("acceptingFilter", {
  default: () => []
})

const visibilityOptions = [
  { label: "Public", value: "public" },
  { label: "Hidden", value: "hidden" }
]

const acceptingOptions = [
  { label: "Yes", value: "yes" },
  { label: "No", value: "no" }
]

function isDefault(current: string[], defaults: string[]): boolean {
  return (
    current.length === defaults.length &&
    current.every((v) => defaults.includes(v))
  )
}

const filterActive = computed(
  () =>
    !isDefault(visibilityFilter.value, defaultVisibility) ||
    !isDefault(acceptingFilter.value, defaultAccepting)
)

function resetFilters() {
  visibilityFilter.value = [...defaultVisibility]
  acceptingFilter.value = [...defaultAccepting]
}
</script>
