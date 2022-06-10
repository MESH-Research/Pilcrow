<template>
  <section class="comments">
    <div class="comments-wrapper">
      <h3 class="text-h1">Overall Comments</h3>
      <overall-comment
        v-for="comment in overall_comments"
        :key="comment.id"
        ref="commentRefs"
        :comment="comment"
      />
      <q-card class="q-mb-md q-pa-md bg-grey-1" data-cy="overallCommentForm">
        <h4 class="q-mt-none">Add a New Overall Comment</h4>
        <comment-editor comment-type="overall" data-cy="overallCommentEditor" />
      </q-card>
    </div>
  </section>
</template>

<script setup>
import CommentEditor from "src/components/forms/CommentEditor.vue"
import OverallComment from "src/components/atoms/OverallComment.vue"
import { computed, inject, nextTick, ref, watch } from "vue"
import { scroll } from "quasar"
const { getScrollTarget, setVerticalScrollPosition } = scroll

const submission = inject("submission")
const activeComment = inject("activeComment")

const overall_comments = computed(() => {
  return submission.value?.overall_comments ?? []
})
const commentRefs = ref([])
watch(
  activeComment,
  (newValue) => {
    if (!newValue) return
    if (newValue.__typename !== "OverallCommentReply") return
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
      setVerticalScrollPosition(target, offset - 64, 250)
    })
  },
  { deep: false }
)
</script>

<style lang="sass" scoped>
.comments
  background-color: #efefef

.comments-wrapper
  max-width: 700px
  margin: 0 auto
  padding: 10px 60px 60px
</style>
