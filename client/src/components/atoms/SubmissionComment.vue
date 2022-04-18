<template>
  <q-card square class="bg-grey-1 shadow-2 q-mb-md">
    <q-separator :color="props.isInlineComment ? `blue-1` : `grey-3`" />
    <q-card-section
      class="q-py-xs"
      :style="
        props.isInlineComment
          ? `background-color: #bbe2e8`
          : `background-color: #eeeeee`
      "
    >
      <div class="row no-wrap justify-between">
        <div class="column justify-center">
          <span>
            <a
              v-if="props.isOverallComment || props.isOverallReply"
              :id="random_id"
              >Overall Comment #
            </a>
            <a v-else :id="random_id">Inline Comment # </a>
            <span>on February 18th, 2021 at 6:35pm</span>
          </span>
        </div>
        <comment-actions />
      </div>
    </q-card-section>
    <q-card-section class="q-py-sm">
      <div class="row">
        <div style="height: 30px; width: 30px">
          <avatar-image :user="user" round class="fit" />
        </div>
        <div class="text-h4 q-pl-sm">Egestas</div>
      </div>
      <div v-if="props.isInlineReply || props.isOverallReply" class="q-pl-sm">
        <small>
          <q-icon size="sm" name="subdirectory_arrow_right" />
          <div
            style="display: inline-block; height: 18px; width: 18px"
            class="q-mr-sm"
          >
            <avatar-image :user="user2" round class="fit" />
          </div>
          <span
            >Replied to
            <router-link to="#inline-comments">Comment #</router-link> by Magna
            Fringilla</span
          >
        </small>
      </div>
    </q-card-section>

    <q-card-section class="q-py-none">
      <p>
        Sagittis eu volutpat odio facilisis. Vitae congue eu consequat ac.
        Cursus sit amet dictum sit amet. Nibh tellus molestie nunc non blandit
        massa enim. Et tortor consequat id porta nibh venenatis. Dictum at
        tempor commodo ullamcorper. Placerat orci nulla pellentesque dignissim.
        Rhoncus dolor purus non enim praesent elementum facilisis.
      </p>
    </q-card-section>

    <q-card-section v-if="props.isInlineComment" class="q-px-sm q-py-none">
      <q-chip size="16px" icon="bookmark"> Relevance </q-chip>
      <q-chip size="16px" icon="bookmark"> Accessibility </q-chip>
      <q-chip size="16px" icon="bookmark"> Coherence </q-chip>
    </q-card-section>

    <q-card-section v-if="isReplying" ref="comment_reply" class="q-pa-md">
      <q-separator class="q-mb-md" />
      <span class="text-h4 q-pl-sm">{{
        $t("submissions.comment.reply.title")
      }}</span>
      <comment-editor :submission="submission" :is-inline-comment="false" />
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
    </q-card-actions>
  </q-card>
  <section v-if="props.isInlineComment" class="q-ml-md">
    <div v-if="!isCollapsed">
      <submission-comment :submission="submission" is-inline-reply />
      <submission-comment :submission="submission" is-inline-reply />
    </div>
  </section>
  <section v-if="props.isOverallComment" class="q-mx-md">
    <div v-if="!isCollapsed">
      <submission-comment :submission="submission" is-overall-reply />
      <submission-comment :submission="submission" is-overall-reply />
    </div>
  </section>
</template>
<script setup>
import { ref } from "vue"
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
import CommentEditor from "../forms/CommentEditor.vue"
const user = { email: "commenter@example.com" }
const user2 = { email: "magnafringilla@example.com" }
const isCollapsed = ref(false)
const isReplying = ref(false)
function toggleThread() {
  isCollapsed.value = !isCollapsed.value
}
const random_id = Math.ceil(Math.random() * 1000000)
const props = defineProps({
  isOverallComment: {
    type: Boolean,
    default: false,
  },
  isOverallReply: {
    type: Boolean,
    default: false,
  },
  isInlineComment: {
    type: Boolean,
    default: false,
  },
  isInlineReply: {
    type: Boolean,
    default: false,
  },
  submission: {
    type: Object,
    default: null,
  },
})

function initiateReply() {
  isReplying.value = true
}
</script>
