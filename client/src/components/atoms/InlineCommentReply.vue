<template>
  <div ref="scrollTarget" />
  <div
    square
    :class="{ active: isActive }"
    :aria-label="
      $t('submissions.comment.reply.ariaLabel', {
        username: comment.created_by.username,
      })
    "
    data-cy="inlineCommentReply"
  >
    <q-separator />
    <comment-header
      :comment="comment"
      class="q-pt-sm"
      @quote-reply-to="$emit('quoteReplyTo', comment)"
    />
    <comment-reply-reference :comment="comment" :replies="replies" />
    <q-card-section class="q-pt-xs">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div v-html="comment.content" />
    </q-card-section>
  </div>
</template>
<script setup>
import { computed, inject, ref } from "vue"
import CommentReplyReference from "./CommentReplyReference.vue"
import CommentHeader from "./CommentHeader.vue"
const props = defineProps({
  parent: {
    type: Object,
    required: true,
  },
  comment: {
    required: true,
    type: Object,
  },
  replies: {
    required: true,
    type: Array,
  },
})
defineEmits(["quoteReplyTo"])
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
div.active
  box-shadow: inset 0 0 5px 2px yellow, 0 1px 5px rgba(0, 0, 0, 0.2), 0 2px 2px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.12)
  > .q-card__section:first-child
    background-color: #F8DB8B !important

div :deep(blockquote)
  border-left: 4px solid #888888
  margin-inline-start: 1em
  padding-left: 0.5em
  margin-block-start: 0
</style>
