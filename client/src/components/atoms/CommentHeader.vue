<template>
  <q-card-section class="q-py-xs" style="background-color: #bbe2e8">
    <div class="row no-wrap justify-between">
      <div class="row items-center">
        <avatar-image :user="comment.created_by" round size="30px" />
        <div class="text-h4 q-pl-sm">{{ comment.created_by.username }}</div>
      </div>
      <div class="row items-center">
        <div class="text-caption">
          <q-tooltip>
            {{ createdDate.toFormat("LLL dd yyyy hh:mm a") }}
          </q-tooltip>
          {{ relativeTime }}
        </div>
        <comment-actions />
      </div>
    </div>
  </q-card-section>
</template>

<script setup>
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
import TimeAgo from "javascript-time-ago"
import { DateTime } from "luxon"
import { computed } from "vue"

const timeAgo = new TimeAgo("en-US")
const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

const createdDate = computed(() => {
  return DateTime.fromISO(props.comment.created_at)
})

const relativeTime = computed(() => {
  return createdDate.value
    ? timeAgo.format(createdDate.value.toJSDate(), "long")
    : ""
})
</script>
