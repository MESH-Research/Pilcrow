<template>
  <q-card square class="bg-grey-1 shadow-2 q-mb-md">
    <comment-header :comment="comment" bg-color="#eeeeee" />
    <comment-reply-reference :comment="comment" :replies="replies" />
    <q-card-section>
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
import { ref } from "vue"
import CommentReplyReference from "./CommentReplyReference.vue"
import CommentHeader from "./CommentHeader.vue"
import CommentEditor from "../forms/CommentEditor.vue"
const isReplying = ref(false)

defineProps({
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
</script>
