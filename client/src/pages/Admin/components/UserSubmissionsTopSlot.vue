<template>
  <q-btn icon="o_filter_alt" label="Filter">
    <q-badge v-if="filterActive" floating rounded color="orange" />
    <q-menu>
      <q-card-section :horizontal="true">
        <UserSubmissionsTopSlotStatusFilter
          ref="statusRef"
          v-model="statusFilter"
          :dense="dense"
        />
        <q-separator vertical />
        <q-card-section>
          <UserSubmissionsTopSlotRolesFilter
            v-model="roleFilter"
            :dense="dense"
          />
          <q-separator />
          <UserSubmissionsTopSlotPublicationFilter
            v-model="publicationFilter"
            :dense="dense"
          />
        </q-card-section>
      </q-card-section>
      <q-separator />
      <q-card-section class="justify-end row">
        <q-btn no-caps label="Reset Filters" @click="resetFilters" />
      </q-card-section>
    </q-menu>
  </q-btn>

  <q-btn
    v-if="filterActive"
    v-close-popup
    class="q-mx-md"
    label="Reset filters to default"
    flat
    no-caps
    @click="resetFilters"
  />
</template>

<script setup>
import UserSubmissionsTopSlotStatusFilter, {
  defaultOptions as defaultStatusOptions
} from "./UserSubmissionsTopSlotStatusFilter.vue"
import UserSubmissionsTopSlotRolesFilter, {
  defaultOptions as defaultRoleOptions
} from "./UserSubmissionsTopSlotRolesFilter.vue"
import UserSubmissionsTopSlotPublicationFilter from "./UserSubmissionsTopSlotPublicationFilter.vue"

import { onMounted, computed } from "vue"

const statusFilter = defineModel("statusFilter", {
  type: Array,
  default: () => [],
  required: true
})
const roleFilter = defineModel("roleFilter", {
  type: Array,
  default: () => [],
  required: true
})
const publicationFilter = defineModel("publicationFilter", {
  type: String
})

defineProps({
  dense: {
    type: Boolean,
    default: false,
    required: false
  }
})

function isEqual(a, b) {
  if (a.length !== b.length) return false
  return (
    a.every((value) => b.includes(value)) &&
    b.every((value) => a.includes(value))
  )
}

const filterActive = computed(() => {
  return !(
    isEqual(statusFilter.value, defaultStatusOptions) &&
    isEqual(roleFilter.value, defaultRoleOptions) &&
    !publicationFilter.value
  )
})

const resetFilters = () => {
  statusFilter.value = defaultStatusOptions
  roleFilter.value = defaultRoleOptions
  publicationFilter.value = null
}

onMounted(() => {
  if (statusFilter.value?.length == 0) {
    statusFilter.value = defaultStatusOptions
  }
  if (roleFilter.value.length == 0) {
    roleFilter.value = defaultRoleOptions
  }
})

defineExpose({ resetFilters })
</script>
