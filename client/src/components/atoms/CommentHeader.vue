<template>
  <q-card-section class="q-py-xs" :style="style">
    <div class="row no-wrap justify-between">
      <div class="row items-center">
        <avatar-image :user="comment.created_by" round size="30px" />
        <div class="text-h4 q-pl-sm">{{ comment.created_by.username }}</div>
      </div>
      <div class="row items-center">
        <div
          v-if="comment.updated_at != comment.created_at"
          data-cy="timestampUpdated"
          class="text-caption"
          :aria-label="
            $t('submissions.comment.dateLabelUpdated', {
              date: relativeUpdatedTime,
            })
          "
        >
          <q-tooltip
            >{{ createdDate.toFormat("LLL dd yyyy hh:mm a") }} <br />
            {{ $t("submissions.comment.updatedLabel") }}
            {{ updatedDate.toFormat("LLL dd yyyy hh:mm a") }}
          </q-tooltip>
          {{ $t("submissions.comment.updatedLabel") }}
          {{ relativeUpdatedTime }}
        </div>
        <div
          v-else
          data-cy="timestampCreated"
          class="text-caption"
          :aria-label="
            $t('submissions.comment.dateLabel', { date: relativeCreatedTime })
          "
        >
          <q-tooltip>
            {{ createdDate.toFormat("LLL dd yyyy hh:mm a") }}
          </q-tooltip>
          {{ relativeCreatedTime }}
        </div>
        <comment-actions
          @quote-reply-to="$emit('quoteReplyTo')"
          @modify-comment="$emit('modifyComment')"
        />
      </div>
    </div>
  </q-card-section>
</template>

<script setup>
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
import { useTimeAgo } from "src/use/timeAgo"
import { DateTime } from "luxon"
import { computed } from "vue"

const timeAgo = new useTimeAgo()
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
defineEmits(["quoteReplyTo", "modifyComment"])
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

const relativeCreatedTime = computed(() => {
  return createdDate.value
    ? timeAgo.format(createdDate.value.toJSDate(), "long")
    : ""
})

const updatedDate = computed(() => {
  return props.comment?.updated_at
    ? DateTime.fromISO(props.comment.updated_at)
    : undefined
})

const relativeUpdatedTime = computed(() => {
  return updatedDate.value
    ? timeAgo.format(updatedDate.value.toJSDate(), "long")
    : ""
})
</script>
