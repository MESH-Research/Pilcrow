<template>
  <div ref="scrollTarget" />
  <div
    square
    :class="{ active: isActive }"
    :aria-label="
      $t('submissions.comment.reply.ariaLabel', {
        username: comment.created_by.username
      })
    "
    data-cy="inlineCommentReply"
  >
    <q-separator />
    <comment-header
      :comment="comment"
      class="q-pt-sm"
      @quote-reply-to="$emit('quoteReplyTo', comment)"
      @modify-comment="modifyComment(comment)"
    />
    <comment-reply-reference :comment="comment" :replies="replies" />
    <q-card-section v-if="!isModifying" class="q-pt-xs">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="comment.content" />
    </q-card-section>
    <q-card-section v-else ref="modify_comment" class="q-pa-md q-pb-lg">
      <comment-editor
        comment-type="InlineCommentReply"
        data-cy="modifyInlineCommentReplyEditor"
        :comment="commentModify"
        :is-modifying="isModifying"
        @cancel="cancelReply"
        @submit="submitReply"
      />
    </q-card-section>
  </div>
</template>
<script setup>
import { computed, inject, ref, provide } from "vue"
import CommentReplyReference from "./CommentReplyReference.vue"
import CommentHeader from "./CommentHeader.vue"
import CommentEditor from "../forms/CommentEditor.vue"

const isReplying = ref(false)
const isQuoteReplying = ref(false)
const commentReply = ref(null)
const isModifying = ref(null)
const commentModify = ref(null)

const props = defineProps({
  parent: {
    type: Object,
    required: true
  },
  comment: {
    required: true,
    type: Object
  },
  replies: {
    required: true,
    type: Array
  }
})
defineEmits(["quoteReplyTo", "replyTo"])

provide("comment", props.comment)

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
  comment: props.comment
})

function submitReply() {
  isReplying.value = false
  isModifying.value = false
  isQuoteReplying.value = false
  commentReply.value = null
}
function cancelReply() {
  isReplying.value = false
  isModifying.value = false
  isQuoteReplying.value = false
  commentReply.value = null
}
function modifyComment(comment) {
  isReplying.value = false
  isQuoteReplying.value = false
  isModifying.value = true
  commentModify.value = comment
}
</script>

<style lang="sass" scoped>
div.active
  box-shadow: inset 0 0 0 1px #F8DB8B

div :deep(blockquote)
  border-left: 4px solid #888888
  margin-inline-start: 1em
  padding-left: 0.5em
  margin-block-start: 0
</style>
