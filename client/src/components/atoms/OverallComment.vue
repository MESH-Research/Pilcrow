<template>
  <div data-cy="overallComment">
    <div ref="scrollTarget" />
    <q-card
      square
      :class="{ active: isActive }"
      class="bg-grey-1 shadow-2 q-mt-md"
      :aria-label="
        $t('submissions.comment.ariaLabel', {
          username: comment.created_by.username,
          replies: comment.replies.length,
        })
      "
    >
      <comment-header :comment="comment" bg-color="#eeeeee" class="q-pt-sm" />
      <q-card-section>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-html="comment.content" />
      </q-card-section>

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
          :reply-to="comment"
          @cancel="cancelReply"
          @submit="submitReply"
        />
      </q-card-section>
      <q-card-actions
        v-if="!isReplying || hasReplies"
        class="q-pa-md q-pb-lg"
        align="right"
      >
        <q-btn
          v-if="!isReplying"
          ref="reply_button"
          data-cy="overallCommentReplyButton"
          bordered
          color="primary"
          label="Reply"
          @click="initiateReply()"
        />
        <template v-if="hasReplies">
          <q-btn
            v-if="!isCollapsed"
            aria-label="Hide Replies"
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
    </q-card>
    <section>
      <div v-if="!isCollapsed">
        <overall-comment-reply
          v-for="reply in comment.replies"
          :key="reply.id"
          ref="replyRefs"
          :comment="reply"
          :parent="comment"
          :replies="comment.replies"
        />
      </div>
    </section>
  </div>
</template>
<script setup>
import { computed, inject, ref } from "vue"
import OverallCommentReply from "./OverallCommentReply.vue"
import CommentEditor from "../forms/CommentEditor.vue"
import CommentHeader from "./CommentHeader.vue"
const isCollapsed = ref(false)
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

function submitReply() {
  isReplying.value = false
}
function cancelReply() {
  isReplying.value = false
}
function initiateReply() {
  isReplying.value = true
}

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
