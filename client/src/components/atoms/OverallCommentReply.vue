<template>
  <q-card square class="bg-grey-1 shadow-2 q-mb-md">
    <q-separator :color="props.isInlineComment ? `blue-1` : `grey-3`" />
    <comment-header :comment="comment" />
    <q-card-section class="q-py-sm">
      <div v-if="referencedComment" class="q-pl-sm">
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

    <q-card-section class="q-py-none">
      <!--  eslint-disable-next-line vue/no-v-html -->
      <div v-html="comment.content" />
    </q-card-section>

    <q-card-section v-if="isReplying" ref="comment_reply" class="q-pa-md">
      <q-separator class="q-mb-md" />
      <span class="text-h4 q-pl-sm">{{
        $t("submissions.comment.reply.title")
      }}</span>
      <comment-editor :is-inline-comment="false" @cancel="cancelReply" />
    </q-card-section>
    <q-card-actions class="q-pa-md q-pb-lg">
      <q-btn
        v-if="!isReplying"
        ref="reply_button"
        bordered
        color="primary"
        label="Reply"
        @click="initiateReply()"
      />
    </q-card-actions>
  </q-card>
</template>
<script setup>
import { computed, ref } from "vue"
import AvatarImage from "./AvatarImage.vue"
import CommentHeader from "./CommentHeader.vue"
import CommentEditor from "../forms/CommentEditor.vue"
const isReplying = ref(false)

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

function cancelReply() {
  isReplying.value = false
}
function initiateReply() {
  isReplying.value = true
}

const referencedComment = computed(() => {
  return props.comment.reply_to_id
    ? props.replies.find((e) => e.id === props.comment.reply_to_id)
    : null
})
</script>
