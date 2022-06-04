<template>
  <q-scroll-area class="fit col bg-grey-4">
    <section>
      <div id="inline_comments_section" class="q-pa-md">
        <span class="text-h3"> Inline Comments </span>
      </div>
      <q-card
        class="q-ma-md q-pa-md bg-grey-1"
        bordered
        style="border-color: rgb(56, 118, 187)"
      >
        <comment-editor :is-inline-comment="true" />
      </q-card>
      <inline-comment
        v-for="comment in inline_comments"
        :key="comment.id"
        ref="commentRefs"
        :comment="comment"
      />
      <div class="row justify-center q-pa-md q-pb-xl">
        <q-btn color="dark" icon="arrow_upward">Scroll to Top</q-btn>
      </div>
    </section>
  </q-scroll-area>
</template>

<script setup>
import { ref, watch, inject, computed, nextTick } from "vue"
import CommentEditor from "src/components/forms/CommentEditor.vue"
import InlineComment from "src/components/atoms/InlineComment.vue"
import { scroll } from "quasar"
const { getScrollTarget, setVerticalScrollPosition } = scroll

const submission = inject("submission")
const activeComment = inject("activeComment")

const commentRefs = ref([])
const inline_comments = computed(() => {
  return submission.value?.inline_comments ?? []
})
watch(activeComment, (newValue) => {
  if (!newValue) return
  if (newValue.__typename !== "InlineCommentReply") return
  nextTick(() => {
    let scrollTarget = null
    for (const commentRef of commentRefs.value) {
      if (commentRef.comment.id === newValue.id) {
        scrollTarget = commentRef.scrollTarget
        break
      }
      if (commentRef.replyIds.includes(newValue.id)) {
        const reply = commentRef.replyRefs.find(
          (r) => r.comment.id === newValue.id
        )
        scrollTarget = reply.scrollTarget
        break
      }
    }
    if (!scrollTarget) return
    const target = getScrollTarget(scrollTarget)
    const offset = scrollTarget.offsetTop
    setVerticalScrollPosition(target, offset, 250)
  })
})
</script>
