<template>
  <div ref="scrollTarget" />
  <q-card
    square
    :class="{ active: isActive }"
    class="bg-grey-1 shadow-2 q-mb-md"
    :aria-label="
      $t('submissions.comment.reply.ariaLabel', {
        username: comment.created_by.username,
      })
    "
  >
    <comment-header :comment="comment" bg-color="#eeeeee" />
    <comment-reply-reference :comment="comment" :replies="replies" />
    <q-card-section>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="comment.content" />
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
    </q-card-actions>
  </q-card>
</template>
<script setup>
import { computed, inject, ref } from "vue"
import CommentReplyReference from "./CommentReplyReference.vue"
import CommentHeader from "./CommentHeader.vue"
const isReplying = ref(false)
const props = defineProps({
  comment: {
    required: true,
    type: Object,
  },
  replies: {
    required: true,
    type: Array,
  },
})

const activeComment = inject("activeComment")
const isActive = computed(() => {
  return (
    activeComment.value?.__typename === props.comment.__typename &&
    activeComment.value?.id === props.comment.id
  )
})
const scrollTarget = ref(null)
defineExpose({
  scrollTarget,
  comment: props.comment,
})
</script>

<style lang="sass" scoped>
.q-card.active
  border: 2px solid yellow
</style>
