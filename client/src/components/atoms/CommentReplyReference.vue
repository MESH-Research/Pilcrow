<template>
  <q-card-section v-if="referencedComment" class="q-py-none">
    <div class="q-pl-sm">
      <small>
        <q-icon size="sm" name="subdirectory_arrow_right" />
        <div
          style="display: inline-block; height: 18px; width: 18px"
          class="q-mr-sm"
        >
          <avatar-image
            :user="referencedComment.created_by"
            round
            class="fit"
          />
        </div>
        <span>
          <router-link to="#overall-comments">
            Reply to {{ referencedComment.created_by.username }}
          </router-link>
        </span>
      </small>
    </div>
  </q-card-section>
</template>

<script setup>
import { computed } from "vue"
import AvatarImage from "./AvatarImage.vue"

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
  replies: {
    type: Array,
    required: true,
  },
})

const referencedComment = computed(() => {
  return props.comment.reply_to_id
    ? props.replies.find((e) => e.id === props.comment.reply_to_id)
    : null
})
</script>
