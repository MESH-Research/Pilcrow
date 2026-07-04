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
      <q-card-section :horizontal="true">
        <SubmissionsFilterPanelStatus
          v-model="statusFilter"
          :allowed-values="allowedStatuses"
          :dense="dense"
        />
        <q-separator vertical />
        <div class="column">
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
        </div>
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

onMounted(() => {
  if (statusFilter.value?.length === 0) {
    statusFilter.value = [...effectiveStatusDefaults.value]
  }
  if (roleFilter.value.length === 0) {
    roleFilter.value = [...effectiveRoleDefaults.value]
  }
})
</script>
