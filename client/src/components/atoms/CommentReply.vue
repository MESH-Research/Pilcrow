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
    :data-cy="dataCy"
  >
    <q-separator />
    <comment-header
      data-cy="CommentHeader"
      :comment="comment"
      class="comment-header q-pt-sm"
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
        :comment-type="commentType"
        :data-cy="`modify${commentType}Editor`"
        :comment="commentModify"
        :is-modifying="isModifying"
        @cancel="cancelReply"
        @submit="submitReply"
      />
    </q-card-section>
  </div>
</template>
<script setup lang="ts">
import { computed, ref, provide } from "vue"
import CommentReplyReference from "./CommentReplyReference.vue"
import CommentHeader from "./CommentHeader.vue"
import CommentEditor from "../forms/CommentEditor.vue"
import {
  useCommentReplyState,
  useIsActiveComment
} from "src/use/commentReplyState"

const { isModifying, commentModify, submitReply, cancelReply, modifyComment } =
  useCommentReplyState()

import type {
  InlineComment,
  InlineCommentReply,
  OverallComment,
  OverallCommentReply
} from "src/graphql/generated/graphql"

type CommentReplyType = InlineCommentReply | OverallCommentReply

interface Props {
  commentType: "InlineCommentReply" | "OverallCommentReply"
  parent: InlineComment | OverallComment
  comment: CommentReplyType
  replies: CommentReplyType[]
}

const props = defineProps<Props>()
interface Emits {
  quoteReplyTo: [comment: CommentReplyType]
  replyTo: []
}
defineEmits<Emits>()

provide("comment", props.comment)

const dataCy = computed(
  () => props.commentType.charAt(0).toLowerCase() + props.commentType.slice(1)
)

const { isActive } = useIsActiveComment(props.comment)
const scrollTarget = ref(null)
defineExpose({
  scrollTarget,
  comment: props.comment
})
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
