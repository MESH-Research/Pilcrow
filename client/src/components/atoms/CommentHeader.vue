<template>
  <q-card-section
    v-if="comment.deleted_at != null"
    class="q-py-sm"
    :style="style"
  >
    <div class="row items-center justify-end">
      <span>
        {{
          $t("submissions.comment.dateLabelDeleted", {
            date: relativeDeletedTime,
          })
        }}
        <q-tooltip>
          {{ deletedDate.toFormat("LLL dd yyyy hh:mm a") }}
        </q-tooltip>
      </span>
    </div>
  </q-card-section>
  <q-card-section v-else class="q-py-xs" :style="style">
    <div class="row items-center">
      <avatar-image
        :user="comment.created_by"
        round
        size="30px"
        class="q-mr-sm"
      />
      <div class="row items-center q-pr-sm" style="flex: 1; min-width: 0">
        <div class="text-h4 ellipsis" :title="comment.created_by.display_label">
          {{ comment.created_by.display_label }}
        </div>
      </div>
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
        class="text-caption text-no-wrap"
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
  </q-card-section>
</template>

<script setup>
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
import { useTimeAgo } from "src/use/timeAgo"
import { DateTime } from "luxon"
import { computed } from "vue"

const timeAgo = useTimeAgo()
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

const deletedDate = computed(() => {
  return props.comment?.deleted_at
    ? DateTime.fromISO(props.comment.deleted_at)
    : undefined
})

const relativeDeletedTime = computed(() => {
  return deletedDate.value
    ? timeAgo.format(deletedDate.value.toJSDate(), "long")
    : ""
})
</script>
