<template>
  <q-card square class="bg-grey-1 shadow-2 q-mb-md">
    <q-separator color="blue-1" />
    <q-card-section class="q-py-xs" style="background-color: #bbe2e8">
      <div class="row no-wrap justify-between">
        <div class="row items-center">
          <avatar-image :user="comment.created_by" round size="30px" />
          <div class="text-h4 q-pl-sm">{{ comment.created_by.username }}</div>
        </div>
        <div class="row items-center">
          <div class="text-caption">
            {{ createdDate.toFormat("yyyy LLL dd") }} ({{ relativeTime }})
          </div>
          <comment-actions />
        </div>
      </div>
    </q-card-section>
    <q-card-section class="q-py-sm"> </q-card-section>

    <q-card-section class="q-py-none">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="comment.content" />
    </q-card-section>

    <q-card-section class="q-px-sm q-py-none">
      <q-chip
        v-for="criteria in comment.style_criteria"
        :key="comment.id + criteria.icon"
        size="16px"
        :icon="criteria.icon"
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
        :comment="reply"
        :replies="comment.replies"
      />
    </div>
  </section>
</template>
<script setup>
import { ref, computed } from "vue"
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
import InlineCommentReply from "./InlineCommentReply.vue"
import CommentEditor from "../forms/CommentEditor.vue"
import { DateTime } from "luxon"
import TimeAgo from "javascript-time-ago"
const isCollapsed = ref(false)
const isReplying = ref(false)
const timeAgo = new TimeAgo("en-US")

function toggleThread() {
  isCollapsed.value = !isCollapsed.value
}
const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

const createdDate = computed(() => {
  return DateTime.fromISO(props.comment.created_at)
})

const relativeTime = computed(() => {
  return createdDate.value
    ? timeAgo.format(createdDate.value.toJSDate(), "long")
    : ""
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
</script>
