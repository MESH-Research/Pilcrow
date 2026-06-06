<template>
  <div data-cy="overallComment" class="overall-comment">
    <div ref="scrollTarget" />
    <q-card
      square
      :class="{ active: isActive }"
      class="bg-grey-1 shadow-2 q-mb-md comment"
      :aria-label="
        $t('submissions.comment.ariaLabel', {
          username: comment.created_by.username,
          replies: comment.replies.length
        })
      "
    >
      <comment-header
        :comment="comment"
        class="comment-header"
        @quote-reply-to="initiateQuoteReply"
        @modify-comment="modifyComment(comment)"
        @delete-comment="deleteComment"
      />
      <q-card-section v-if="!isModifying">
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-html="comment.content" />
      </q-card-section>
      <q-card-section v-else ref="modify_comment" class="q-pa-md q-pb-lg">
        <comment-editor
          comment-type="OverallComment"
          data-cy="modifyOverallCommentEditor"
          :comment="commentModify"
          :is-modifying="isModifying"
          @cancel="cancelReply"
          @submit="submitReply"
        />
      </q-card-section>

      <q-card-actions v-if="hasReplies" align="right" class="q-pa-md">
        <q-btn
          v-if="!isCollapsed"
          :aria-label="
            $t(
              `submissions.comment.toggle_replies.hide_reply`,
              comment.replies.length
            )
          "
          data-cy="hideRepliesButton"
          bordered
          color="secondary"
          text-color="white"
          @click="toggleThread"
        >
          <q-icon name="expand_less"></q-icon>
          <span>{{
            $t(
              `submissions.comment.toggle_replies.hide_reply`,
              comment.replies.length
            )
          }}</span>
        </q-btn>
        <q-btn
          v-if="isCollapsed"
          :aria-label="
            $t(
              `submissions.comment.toggle_replies.show_reply`,
              comment.replies.length
            )
          "
          data-cy="showRepliesButton"
          bordered
          color="secondary"
          text-color="white"
          @click="toggleThread"
        >
          <q-icon name="expand_more"></q-icon>
          <span>{{
            $t(
              "submissions.comment.toggle_replies.show_reply",
              comment.replies.length
            )
          }}</span>
        </q-btn>
      </q-card-actions>

      <section class="overall-comment-replies">
        <div v-if="!isCollapsed">
          <CommentReply
            v-for="reply in comment.replies"
            :key="reply.id"
            ref="replyRefs"
            comment-type="OverallCommentReply"
            :comment="reply"
            :parent="comment"
            :replies="comment.replies"
            @quote-reply-to="initiateQuoteReply"
          />
        </div>
      </section>
      <q-card-section
        v-if="isReplying"
        ref="comment_reply"
        class="q-pa-md q-pb-lg"
      >
        <q-separator class="q-mb-md" />
        <span class="text-h4 q-pl-sm">{{
          $t("submissions.comment.reply.title")
        }}</span>
        <comment-editor
          comment-type="OverallCommentReply"
          data-cy="overallCommentReplyEditor"
          :parent="comment"
          :reply-to="commentReply ?? comment"
          :is-quote-replying="isQuoteReplying"
          @cancel="cancelReply"
          @submit="submitReply"
        />
      </q-card-section>
      <q-card-actions v-if="showReplyButton" class="q-pa-md" align="right">
        <q-btn
          v-if="!isReplying"
          ref="reply_button"
          data-cy="overallCommentReplyButton"
          bordered
          color="accent"
          :label="$t(`submissions.comment.reply_btn`)"
          @click="initiateReply()"
        />
      </q-card-actions>
    </q-card>
  </div>
</template>
<script setup lang="ts">
import { computed, ref, provide } from "vue"
import CommentReply from "./CommentReply.vue"
import CommentEditor from "../forms/CommentEditor.vue"
import CommentHeader from "./CommentHeader.vue"
import {
  useCommentReplyState,
  useIsActiveComment
} from "src/use/commentReplyState"

const isCollapsed = ref(true)
const {
  isReplying,
  isQuoteReplying,
  commentReply,
  isModifying,
  commentModify,
  resetReplyState: deleteComment,
  submitReply,
  cancelReply,
  initiateReply,
  initiateQuoteReply,
  modifyComment
} = useCommentReplyState()

function toggleThread() {
  isCollapsed.value = !isCollapsed.value
}

import type { OverallComment as OverallCommentType } from "src/graphql/generated/graphql"

interface Props {
  comment: OverallCommentType
}

const props = defineProps<Props>()

provide("comment", props.comment)

const showReplyButton = computed(() => {
  if (isReplying.value) return false
  if (hasReplies.value && isCollapsed.value) return false
  return true
})

const hasReplies = computed(() => {
  return props.comment.replies.length > 0
})
const replyRefs = ref([])
const scrollTarget = ref(null)

const { isActive } = useIsActiveComment(props.comment)

defineExpose({
  scrollTarget,
  replyRefs,
  comment: props.comment,
  replyIds: props.comment.replies.map((c) => c.id)
})
</script>
