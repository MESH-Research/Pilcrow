<template>
  <i18n-t keypath="submission.activity_record.description" tag="span">
    <template #event>{{
      $t(`submission.activity_record.events.${audit.event}`)
    }}</template>
    <template #user>
      <router-link
        :to="{
          name: 'user_details',
          params: { id: audit.user.id },
        }"
        >{{ audit.user.name || audit.user.username }}
      </router-link>
    </template>
    <template #datetime>
      <span
        :aria-label="
          $t('submissions.comment.dateLabel', { date: relativeTime })
        "
      >
        <q-tooltip anchor="top middle" self="center middle">
          {{ createdDate.toFormat("LLL dd yyyy hh:mm a") }}
        </q-tooltip>
        {{ relativeTime }}
      </span>
    </template>
  </i18n-t>

  <i18n-t
    v-if="audit.event == 'updated' && audit.old_values.status != null"
    keypath="submission.activity_record.status_change"
    tag="span"
  >
    <template #previous_status>{{
      $t(`submission.status.${audit.old_values.status}`)
    }}</template>
    <template #current_status>
      <b>{{ $t(`submission.status.${audit.new_values.status}`) }}</b>
    </template>
  </i18n-t>
  <p v-if="audit.new_values.status_change_comment != null">
    {{ $t("submission.activity_record.comment_title") }}:
    {{ audit.new_values.status_change_comment }}
  </p>
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
