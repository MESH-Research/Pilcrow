<template>
  <WithAsideCell :scope="scope" :dark-mode-status="darkModeStatus">
    <template #value>
      {{ absolute }}
    </template>
    <template #aside>
      {{ relative }}
    </template>
  </WithAsideCell>
</template>

<script setup lang="ts">
import WithAsideCell from "./WithAsideCell.vue"
import { DateTime } from "luxon"
import { computed } from "vue"
import { useTimeAgo } from "src/use/timeAgo"
import type { QTableBodyCellScope } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
  darkModeStatus?: boolean
}

const props = defineProps<Props>()
const timeAgo = useTimeAgo()

const value = computed(() => DateTime.fromISO(props.scope.value as string))

const absolute = computed(() => value.value?.toFormat("LLL d yyyy h:mm a"))

const relative = computed(() => timeAgo.format(value.value?.toJSDate(), "long"))
</script>
