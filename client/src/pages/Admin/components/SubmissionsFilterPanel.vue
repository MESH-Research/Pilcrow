<template>
  <q-btn
    flat
    dense
    no-caps
    icon="filter_list"
    :label="
      activeCount > 0
        ? $t('admin.filters.active', { count: activeCount })
        : $t('admin.filters.label')
    "
    :aria-label="$t('submissions.filters.aria')"
  >
    <q-menu>
      <q-card-section class="q-pb-none">
        <q-btn-group flat stretch class="full-width">
          <q-btn
            dense
            no-caps
            :label="$t('admin.filters.all')"
            @click="selectAll"
          />
          <q-btn
            dense
            no-caps
            :label="$t('admin.filters.none')"
            @click="selectNone"
          />
          <q-btn
            dense
            no-caps
            :label="$t('admin.filters.invert')"
            @click="invert"
          />
        </q-btn-group>
      </q-card-section>
      <q-separator class="q-mt-sm" />
      <q-card-section :horizontal="true">
        <SubmissionsFilterPanelStatus v-model="statusFilter" :dense="dense" />
        <q-separator vertical />
        <q-card-section>
          <SubmissionsFilterPanelRoles v-model="roleFilter" :dense="dense" />
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
}

withDefaults(defineProps<Props>(), {
  dense: false
})

function isEqual(a: string[], b: string[]): boolean {
  if (a.length !== b.length) return false
  return (
    a.every((value) => b.includes(value)) &&
    b.every((value) => a.includes(value))
  )
}

// Number of dimensions currently restricting results (non-default).
// Surfaced in the button label so the user can tell from the toolbar
// how much their view diverges from the default filter set.
const activeCount = computed(() => {
  let n = 0
  if (!isEqual(statusFilter.value, defaultStatusOptions)) n++
  if (!isEqual(roleFilter.value, defaultRoleOptions)) n++
  if (publicationFilter.value) n++
  return n
})

// "All" restores each dimension's default set — the meaningful
// "everything on" state. Publication is single-select with no default,
// so it clears to null.
function selectAll() {
  statusFilter.value = [...defaultStatusOptions]
  roleFilter.value = [...defaultRoleOptions]
  publicationFilter.value = null
}

function selectNone() {
  statusFilter.value = []
  roleFilter.value = []
  publicationFilter.value = null
}

function invert() {
  statusFilter.value = defaultStatusOptions.filter(
    (v) => !statusFilter.value.includes(v)
  )
  roleFilter.value = defaultRoleOptions.filter(
    (v) => !roleFilter.value.includes(v)
  )
  // Publication is single-select; nothing meaningful to invert.
}

onMounted(() => {
  if (statusFilter.value?.length === 0) {
    statusFilter.value = [...defaultStatusOptions]
  }
  if (roleFilter.value.length === 0) {
    roleFilter.value = [...defaultRoleOptions]
  }
})

defineExpose({ selectAll, selectNone, invert })
</script>
