<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-badge
      :color="style.color"
      :class="['text-weight-medium q-pa-sm', style.textClass, style.pattern]"
    >
      <q-icon :name="style.icon" size="xs" />
      <q-separator vertical class="q-mx-xs" />
      <span class="pattern-text-mask">{{ label }}</span>
    </q-badge>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useI18n } from "vue-i18n"
import type { QTableBodyCellScope } from "src/components/tables/QueryTable.vue"
import { statusStyleMap } from "./statusCategories"

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()
const { t } = useI18n()

const status = computed(() => props.scope.row.status as string)
const style = computed(
  () =>
    statusStyleMap[status.value] ?? {
      color: "grey",
      textClass: "text-white",
      icon: "help",
      pattern: ""
    }
)
const label = computed(() => t(`submission.status.${status.value}`))
</script>
