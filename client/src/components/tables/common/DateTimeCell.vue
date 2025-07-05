<template>
  <WithAsideCell :scope="scope">
    <template #value>
      {{ absolute }}
    </template>
    <template #aside>
      {{ relative }}
    </template>
  </WithAsideCell>
</template>

<script setup>
import WithAsideCell from "./WithAsideCell.vue"
import { DateTime } from "luxon"
import { computed } from "vue"
import { useTimeAgo } from "src/use/timeAgo"
const props = defineProps({
  scope: {
    type: Object,
    required: true
  }
})
const timeAgo = useTimeAgo()

const value = computed(() => DateTime.fromISO(props.scope.value))

const absolute = computed(() => value.value?.toFormat("LLL d yyyy h:mm a"))

const relative = computed(() => timeAgo.format(value.value?.toJSDate(), "long"))
</script>
