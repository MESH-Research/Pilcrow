<template>
  <div data-cy="overallComment">
    <div ref="scrollTarget" />
    <q-card
      square
      :class="{ active: isActive }"
      class="bg-grey-1 shadow-2 q-mb-md"
      :aria-label="
        $t('submissions.comment.ariaLabel', {
          username: comment.created_by.username,
          replies: comment.replies.length,
        })
      "
    >
      <comment-header
        :comment="comment"
        bg-color="#C9E5F8"
        @quote-reply-to="initiateQuoteReply"
      />
      <q-card-section>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-html="comment.content" />
      </q-card-section>

      <q-card-actions v-if="hasReplies" align="right" class="q-pa-md">
        <q-btn
          v-if="!isCollapsed"
          aria-label="Hide Replies"
          data-cy="hideRepliesButton"
          bordered
          color="secondary"
          text-color="white"
          @click="toggleThread"
        >
          <q-icon name="expand_less"></q-icon>
          <span>Hide Replies</span>
        </q-btn>
        <q-btn
          v-if="isCollapsed"
          aria-label="Show Replies"
          data-cy="showRepliesButton"
          bordered
          color="secondary"
          text-color="white"
          @click="toggleThread"
        >
          <q-icon name="expand_more"></q-icon>
          <span>Show Replies</span>
        </q-btn>
      </q-card-actions>

      <section>
        <div v-if="!isCollapsed">
          <overall-comment-reply
            v-for="reply in comment.replies"
            :key="reply.id"
            ref="replyRefs"
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
          label="Reply"
          @click="initiateReply()"
        />
      </q-card-actions>
    </q-card>
  </div>
</template>
<script setup>
import { computed, inject, ref } from "vue"
import OverallCommentReply from "./OverallCommentReply.vue"
import CommentEditor from "../forms/CommentEditor.vue"
import CommentHeader from "./CommentHeader.vue"

const isCollapsed = ref(true)
const isReplying = ref(false)
const isQuoteReplying = ref(false)
const commentReply = ref(null)

function toggleThread() {
  isCollapsed.value = !isCollapsed.value
}

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

function submitReply() {
  isReplying.value = false
  isQuoteReplying.value = false
  commentReply.value = null
}
function cancelReply() {
  isReplying.value = false
  isQuoteReplying.value = false
  commentReply.value = null
}
function initiateReply() {
  isReplying.value = true
  isQuoteReplying.value = false
}
function initiateQuoteReply(comment) {
  isReplying.value = true
  isQuoteReplying.value = true
  commentReply.value = comment
}

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

const activeComment = inject("activeComment")
const isActive = computed(() => {
  return (
    activeComment.value?.__typename === props.comment.__typename &&
    activeComment.value?.id === props.comment.id
  )
})

defineExpose({
  scrollTarget,
  replyRefs,
  comment: props.comment,
  replyIds: props.comment.replies.map((c) => c.id),
})
</script>
