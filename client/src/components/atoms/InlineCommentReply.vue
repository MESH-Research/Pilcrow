<template>
  <div ref="scrollTarget" />
  <q-card
    square
    :class="{ active: isActive }"
    class="bg-grey-1 shadow-2 q-mb-md"
    :aria-label="
      $t('submissions.comment.reply.ariaLabel', {
        username: comment.created_by.username,
      })
    "
    data-cy="inlineCommentReply"
  >
    <comment-header :comment="comment" bg-color="#eeeeee" />
    <comment-reply-reference :comment="comment" :replies="replies" />
    <q-card-section>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="comment.content" />
    </q-card-section>

    <q-card-section
      v-if="isReplying"
      ref="comment_reply"
      data-cy="inlineCommentReplyForm"
      class="q-pa-md q-pb-lg"
    >
      <q-separator class="q-mb-md" />
      <span class="text-h4 q-pl-sm">{{
        $t("submissions.comment.reply.title")
      }}</span>
      <comment-editor
        data-cy="inlineCommentReplyEditor"
        comment-type="inlineReply"
        :parent="parent"
        :reply-to="comment"
        @cancel="cancelReply"
        @submit="submitReply"
      />
    </q-card-section>
    <q-card-actions v-if="!isReplying" class="q-pa-md q-pb-lg">
      <q-btn
        ref="reply_button"
        data-cy="inlineCommentReplyButton"
        bordered
        color="primary"
        label="Reply"
        @click="initiateReply()"
      />
    </q-card-actions>
  </q-card>
</template>
<script setup>
import { computed, inject, ref } from "vue"
import CommentReplyReference from "./CommentReplyReference.vue"
import CommentHeader from "./CommentHeader.vue"
import CommentEditor from "../forms/CommentEditor.vue"
const isReplying = ref(false)
const props = defineProps({
  parent: {
    type: Object,
    required: true,
  },
  comment: {
    required: true,
    type: Object,
  },
  replies: {
    required: true,
    type: Array,
  },
})

function submitReply() {
  isReplying.value = false
}
function cancelReply() {
  isReplying.value = false
}
function initiateReply() {
  isReplying.value = true
}
const activeComment = inject("activeComment")
const isActive = computed(() => {
  return (
    activeComment.value?.__typename === props.comment.__typename &&
    activeComment.value?.id === props.comment.id
  )
})
const scrollTarget = ref(null)
defineExpose({
  scrollTarget,
  comment: props.comment,
})
</script>

<style lang="sass" scoped>
.q-card.active
  box-shadow: inset 0 0 5px 2px yellow, 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12)
.q-card.active > .q-card__section:first-child
  background-color: #edf0c6 !important
</style>
