<template>
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
    data-cy="inlineComment"
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
      <template v-if="hasReplies">
        <q-btn
          v-if="!props.isInlineReply && !props.isOverallReply && !isCollapsed"
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
          v-if="!props.isInlineReply && !props.isOverallReply && isCollapsed"
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
  <section class="q-ml-md">
    <div v-if="!isCollapsed">
      <inline-comment-reply
        v-for="reply in comment.replies"
        :key="reply.id"
        ref="replyRefs"
        :comment="reply"
        :replies="comment.replies"
      />
    </div>
  </section>
</template>
<script setup>
import { ref, computed, inject } from "vue"
import CommentHeader from "./CommentHeader.vue"
import InlineCommentReply from "./InlineCommentReply.vue"
import CommentEditor from "../forms/CommentEditor.vue"
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

const hasReplies = computed(() => {
  return props.comment.replies.length > 0
})

function cancelReply() {
  isReplying.value = false
}
function initiateReply() {
  isReplying.value = true
}

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
