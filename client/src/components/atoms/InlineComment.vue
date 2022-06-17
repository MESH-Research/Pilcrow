<template>
  <div data-cy="inlineComment">
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
      <comment-header :comment="comment" bg-color="#bbe2e8" />

      <q-card-section>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-html="comment.content" />
      </q-card-section>

      <q-card-section class="q-px-sm q-py-none">
        <q-chip
          v-for="criteria in comment.style_criteria"
          :key="comment.id + criteria.icon"
          size="16px"
          :icon="criteria.icon"
          data-cy="styleCriteria"
        >
          {{ criteria.name }}
        </q-chip>
      </q-card-section>
      <q-card-actions v-if="hasReplies" class="q-pa-md q-pb-lg">
        <template v-if="hasReplies">
          <q-btn
            v-if="!isCollapsed"
            aria-label="Hide Replies"
            bordered
            color="grey-3"
            text-color="black"
            @click="toggleThread"
          >
            <q-icon name="expand_less"></q-icon>
            <span>Hide Replies</span>
          </q-btn>
          <q-btn
            v-if="isCollapsed"
            aria-label="Show Replies"
            bordered
            color="secondary"
            text-color="white"
            @click="toggleThread"
          >
            <q-icon name="expand_more"></q-icon>
            <span>Show Replies</span>
          </q-btn>
        </template>
      </q-card-actions>
      <section>
        <div v-if="!isCollapsed">
          <inline-comment-reply
            v-for="reply in comment.replies"
            :key="reply.id"
            ref="replyRefs"
            :comment="reply"
            :parent="comment"
            :replies="comment.replies"
            @reply-to="nestedReply"
          />
        </div>
      </section>
      <q-card-section v-if="isReplying" ref="comment_reply" class="q-pa-md">
        <q-separator class="q-mb-md" />
        <span class="text-h4 q-pl-sm">{{
          $t("submissions.comment.reply.title")
        }}</span>
        <comment-editor
          data-cy="inlineCommentReplyEditor"
          comment-type="InlineCommentReply"
          :parent="comment"
          :reply-to="commentReply ?? comment"
          @cancel="cancelReply"
          @submit="submitReply"
        />
      </q-card-section>
      <q-card-section v-if="showReplyButton" class="q-pt-none">
        <q-btn
          ref="reply_button"
          data-cy="inlineCommentReplyButton"
          bordered
          color="primary"
          label="Reply"
          @click="initiateReply"
        />
      </q-card-section>
    </q-card>
  </div>
</template>
<script setup>
import { ref, computed, inject } from "vue"
import CommentHeader from "./CommentHeader.vue"
import InlineCommentReply from "./InlineCommentReply.vue"
import CommentEditor from "../forms/CommentEditor.vue"
const isCollapsed = ref(true)
const isReplying = ref(false)

function toggleThread() {
  isCollapsed.value = !isCollapsed.value
}
const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

const hasReplies = computed(() => {
  return props.comment.replies.length > 0
})

function submitReply() {
  isReplying.value = false
  commentReply.value = null
}
function cancelReply() {
  isReplying.value = false
  commentReply.value = null
}
function initiateReply() {
  isReplying.value = true
}
const commentReply = ref(null)
function nestedReply(comment) {
  isReplying.value = true
  console.log(comment)
  commentReply.value = comment
}

const showReplyButton = computed(() => {
  if (isReplying.value) return false
  if (hasReplies.value && isCollapsed.value) return false
  return true
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

<style lang="sass" scoped>
.q-card.active
  box-shadow: inset 0 0 5px 2px yellow, 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12)
  > .q-card__section:first-child
    background-color: #edf0c6 !important
</style>
