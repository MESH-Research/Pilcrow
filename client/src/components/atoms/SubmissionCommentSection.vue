<template>
  <section class="comments">
    <div class="comments-wrapper">
      <h2 class="text-h1">{{ $t("submissions.overall_comments.heading") }}</h2>
      <overall-comment
        v-for="comment in overall_comments"
        :key="comment.id"
        ref="commentRefs"
        :comment="comment"
      />
      <q-card class="q-my-md q-pa-md bg-grey-1 comment-editor-card">
        <h3 class="text-h3 q-mt-none">
          {{ $t("submissions.overall_comments.editor_heading") }}
        </h3>
        <comment-editor
          comment-type="OverallComment"
          data-cy="overallCommentEditor"
        />
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
            (r) => r.comment.id === newValue.id,
          )
          scrollTarget = reply.scrollTarget
          break
        }
      }
      if (!scrollTarget) return
      const getOffsetTop = function (element) {
        if (!element) return 0
        return getOffsetTop(element.offsetParent) + element.offsetTop
      }
      const secondaryNavHeight = 75
      const toolbarHeight = 50
      const negativeSpaceAdjustment = 14
      const offset =
        getOffsetTop(scrollTarget) -
        toolbarHeight -
        secondaryNavHeight -
        negativeSpaceAdjustment
      const target = getScrollTarget(scrollTarget)
      setVerticalScrollPosition(target, offset - 64, 250)
    })
  },
  { deep: false },
)
</script>

<style lang="sass" scoped>
.comments
  background-color: #F3F4F7

.comments-wrapper
  max-width: 700px
  margin: 0 auto
  padding: 10px 60px 60px
</style>
