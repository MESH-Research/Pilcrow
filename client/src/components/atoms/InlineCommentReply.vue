<template>
  <q-card square class="bg-grey-1 shadow-2 q-mb-md">
    <comment-header :comment="comment" bg-color="#bbe2e8" />
    <comment-reply-reference :comment="comment" :replies="replies" />
    <q-card-section>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="comment.content" />
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
const isReplying = ref(false)
defineProps({
  comment: {
    required: true,
    type: Object,
  },
  replies: {
    required: true,
    type: Array,
  },
})
</script>
