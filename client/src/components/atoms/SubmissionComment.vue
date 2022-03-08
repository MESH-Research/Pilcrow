<template>
  <q-card square class="bg-grey-1 shadow-2">
    <q-separator color="grey-7" />
    <q-card-section
      :style="
        props.isReply
          ? `background-color: #e6e6e6`
          : `background-color: #bbe2e8`
      "
    >
      <div class="row items-start no-wrap">
        <div class="col">
          <small id="comment" class="text-uppercase">Inline Comment #</small>
          <div class="row items-start no-wrap q-pt-xs">
            <div style="width: 30px">
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
                <avatar-image :user="user" round class="fit" />
              </div>
              <span
                >Reply to
                <router-link to="#inline-comments">Comment #</router-link> by
                Magna Fringilla</span
              >
            </small>
          </div>
          <small> February 18th, 2021 at 6:35pm</small>
        </div>
        <comment-actions />
      </div>
    </q-card-section>

    <q-separator color="grey-4" />
    <q-card-section class="q-pb-none">
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
      <q-btn v-if="!props.isReply" bordered color="grey-3" text-color="black">
        <span>Collapse</span>
        <q-icon name="expand_less"></q-icon>
      </q-btn>
      <q-btn bordered color="primary" label="Reply" />
    </q-card-actions>
  </q-card>
  <div v-if="!props.isReply" class="q-ml-md q-mb-md">
    <submission-comment is-reply />
    <submission-comment is-reply />
  </div>
</template>
<script setup>
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
const user = { email: "commenter@example.com" }
const props = defineProps({
  isReply: {
    type: Boolean,
    default: false,
  },
})
</script>
