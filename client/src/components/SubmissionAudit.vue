<template>
  {{ audit.event }} by
  <router-link
    :to="{
      name: 'user_details',
      params: { id: audit.user.id },
    }"
    >{{ audit.user.name || audit.user.username }}
  </router-link>

  <span
    :aria-label="$t('submissions.comment.dateLabel', { date: relativeTime })"
  >
    <q-tooltip anchor="top middle" self="center middle">
      {{ createdDate.toFormat("LLL dd yyyy hh:mm a") }}
    </q-tooltip>
    {{ relativeTime }}
  </span>
  <span v-if="audit.event == 'updated' && audit.old_values.status != null">
    <span>
      from {{ $t(`submission.status.${audit.old_values.status}`) }} to
      <b>{{ $t(`submission.status.${audit.new_values.status}`) }}</b>
    </span>
    <p v-if="audit.new_values.status_change_comment != null">
      Comment: {{ audit.new_values.status_change_comment }}
    </p>
  </span>
  <q-separator class="q-my-md" />
</template>

<script setup>
import TimeAgo from "javascript-time-ago"
import { DateTime } from "luxon"
import { computed } from "vue"
const props = defineProps({
  audit: {
    type: Object,
    required: true,
  },
})
const timeAgo = new TimeAgo("en-US")
const createdDate = computed(() => {
  return DateTime.fromISO(props.audit.created_at)
})
const relativeTime = computed(() => {
  return createdDate.value
    ? timeAgo.format(createdDate.value.toJSDate(), "long")
    : ""
})
</script>
