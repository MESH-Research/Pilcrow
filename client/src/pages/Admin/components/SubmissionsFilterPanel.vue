<template>
  <q-btn
    flat
    dense
    no-caps
    icon="filter_list"
    :label="activeCount > 0 ? `Filter · ${activeCount} active` : 'Filter'"
    aria-label="Filter submissions"
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
      <q-card-section :horizontal="true">
        <SubmissionsFilterPanelStatus
          v-model="statusFilter"
          :allowed-values="allowedStatuses"
          :dense="dense"
        />
        <q-separator vertical />
        <q-card-section>
          <SubmissionsFilterPanelRoles
            v-model="roleFilter"
            :allowed-values="allowedRoles"
            :dense="dense"
          />
          <q-separator />
          <SubmissionsFilterPanelPublication
            v-model="publicationFilter"
            :dense="dense"
          />
        </q-card-section>
      </q-card-section>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import SubmissionsFilterPanelStatus, {
  defaultOptions as defaultStatusOptions
} from "./SubmissionsFilterPanelStatus.vue"
import SubmissionsFilterPanelRoles, {
  defaultOptions as defaultRoleOptions
} from "./SubmissionsFilterPanelRoles.vue"
import SubmissionsFilterPanelPublication from "./SubmissionsFilterPanelPublication.vue"

import { onMounted, computed } from "vue"

const statusFilter = defineModel<string[]>("statusFilter", {
  default: () => []
})
const roleFilter = defineModel<string[]>("roleFilter", {
  default: () => []
})
const publicationFilter = defineModel<string | null>("publicationFilter", {
  default: null
})

interface Props {
  dense?: boolean
  allowedStatuses?: string[]
  allowedRoles?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  dense: false,
  allowedStatuses: undefined,
  allowedRoles: undefined
})

const effectiveStatusDefaults = computed(() =>
  props.allowedStatuses
    ? defaultStatusOptions.filter((v) => props.allowedStatuses!.includes(v))
    : defaultStatusOptions
)

const effectiveRoleDefaults = computed(() =>
  props.allowedRoles
    ? defaultRoleOptions.filter((v) => props.allowedRoles!.includes(v))
    : defaultRoleOptions
)

function isEqual(a: string[], b: string[]): boolean {
  if (a.length !== b.length) return false
  return (
    a.every((value) => b.includes(value)) &&
    b.every((value) => a.includes(value))
  )
}

const activeCount = computed(() => {
  let n = 0
  if (!isEqual(statusFilter.value, effectiveStatusDefaults.value)) n++
  if (!isEqual(roleFilter.value, effectiveRoleDefaults.value)) n++
  if (publicationFilter.value) n++
  return n
})

function selectAll() {
  statusFilter.value = [...effectiveStatusDefaults.value]
  roleFilter.value = [...effectiveRoleDefaults.value]
  publicationFilter.value = null
}

function selectNone() {
  statusFilter.value = []
  roleFilter.value = []
  publicationFilter.value = null
}

function invert() {
  statusFilter.value = effectiveStatusDefaults.value.filter(
    (v) => !statusFilter.value.includes(v)
  )
  roleFilter.value = effectiveRoleDefaults.value.filter(
    (v) => !roleFilter.value.includes(v)
  )
}

onMounted(() => {
  if (statusFilter.value?.length === 0) {
    statusFilter.value = [...effectiveStatusDefaults.value]
  }
  if (roleFilter.value.length === 0) {
    roleFilter.value = [...effectiveRoleDefaults.value]
  }
})

defineExpose({ selectAll, selectNone, invert })
</script>
