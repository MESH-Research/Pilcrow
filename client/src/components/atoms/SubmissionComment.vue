<template>
  <q-card square class="bg-grey-1 shadow-2 q-mb-md">
    <q-separator :color="props.isReply ? `grey-3` : `blue-1`" />
    <q-card-section
      class="q-py-xs"
      :style="
        props.isReply
          ? `background-color: #eeeeee`
          : `background-color: #bbe2e8`
      "
    >
      <div class="row no-wrap justify-between">
        <div class="column justify-center">
          <span>
            <a :id="random_id">Inline Comment # </a>
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
      <div v-if="props.isReply" class="q-pl-sm">
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

    <div v-if="!props.isReply" class="q-px-sm">
      <q-chip size="16px" icon="bookmark"> Relevance </q-chip>
      <q-chip size="16px" icon="bookmark"> Accessibility </q-chip>
      <q-chip size="16px" icon="bookmark"> Coherence </q-chip>
    </div>

    <q-card-actions class="q-pa-md q-pb-lg">
      <q-btn bordered color="primary" label="Reply" />
      <q-btn
        v-if="!props.isReply && isCollapsed"
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
        v-if="!props.isReply && !isCollapsed"
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
  <div v-if="!props.isReply && isCollapsed" class="q-ml-md">
    <submission-comment is-reply />
    <submission-comment is-reply />
  </div>
</template>
<script setup>
import { ref } from "vue"
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
const user = { email: "commenter@example.com" }
const user2 = { email: "magnafringilla@example.com" }
const isCollapsed = ref(false)
function toggleThread() {
  isCollapsed.value = !isCollapsed.value
}
const random_id = Math.ceil(Math.random() * 1000000)
const props = defineProps({
  isReply: {
    type: Boolean,
    default: false,
  },
})
</script>
