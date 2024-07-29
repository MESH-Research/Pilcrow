<template>
  <q-card-section class="q-py-xs">
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
    </div>
  </q-card-section>
</template>

<script setup>
import AvatarImage from "./AvatarImage.vue"
import { useTimeAgo } from "src/use/timeAgo"
import { DateTime } from "luxon"
import { computed } from "vue"

const timeAgo = useTimeAgo()
const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
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
