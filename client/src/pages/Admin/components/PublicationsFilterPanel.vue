<template>
  <q-btn
    flat
    dense
    no-caps
    icon="filter_list"
    :label="activeCount > 0 ? `Filter · ${activeCount} active` : 'Filter'"
    aria-label="Filter publications"
  >
    <q-menu>
      <q-card-section class="q-pb-none">
        <q-btn-group flat stretch class="full-width">
          <q-btn dense no-caps label="All" @click="selectAll" />
          <q-btn dense no-caps label="None" @click="selectNone" />
          <q-btn dense no-caps label="Invert" @click="invert" />
        </q-btn-group>
      </q-card-section>
      <q-separator class="q-mt-sm" />
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
    </q-menu>
  </q-btn>
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

const activeCount = computed(() => {
  let n = 0
  if (!isDefault(visibilityFilter.value, defaultVisibility)) n++
  if (!isDefault(acceptingFilter.value, defaultAccepting)) n++
  return n
})

function selectAll() {
  visibilityFilter.value = [...defaultVisibility]
  acceptingFilter.value = [...defaultAccepting]
}

function selectNone() {
  visibilityFilter.value = []
  acceptingFilter.value = []
}

function invert() {
  visibilityFilter.value = defaultVisibility.filter(
    (v) => !visibilityFilter.value.includes(v)
  )
  acceptingFilter.value = defaultAccepting.filter(
    (v) => !acceptingFilter.value.includes(v)
  )
}
</script>
