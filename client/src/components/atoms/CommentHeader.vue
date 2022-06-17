<template>
  <q-card-section class="q-py-xs" :style="style">
    <div class="row no-wrap justify-between">
      <div class="row items-center">
        <avatar-image :user="comment.created_by" round size="30px" />
        <div class="text-h4 q-pl-sm">{{ comment.created_by.username }}</div>
      </div>
      <div class="row items-center">
        <div
          class="text-caption"
          :aria-label="
            $t('submissions.comment.dateLabel', { date: relativeTime })
          "
        >
          <q-tooltip>
            {{ createdDate.toFormat("LLL dd yyyy hh:mm a") }}
          </q-tooltip>
          {{ relativeTime }}
        </div>
        <comment-actions @reply="$emit('reply')" />
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
  bgColor: {
    type: String,
    required: false,
    default: null,
  },
})
defineEmits(["reply"])
const style = computed(() => {
  const style = {}
  if (props.bgColor) {
    style.backgroundColor = props.bgColor
  }

  return style
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
