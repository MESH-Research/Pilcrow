<template>
  <q-scroll-area class="fit col xlight-grey">
    <section ref="inline_comments_section">
      <div class="q-pa-md">
        <span class="text-h3">{{
          $t("submissions.inline_comments.heading")
        }}</span>
      </div>

      <component
        :is="isNewInlineComment(comment) ? NewInlineComment : InlineComment"
        v-for="comment in inline_comments"
        :key="comment.id"
        ref="commentRefs"
        :comment="comment"
        @submit="closeEditor"
        @cancel="closeEditor"
      />
      <div class="row justify-center q-pa-md q-pb-xl">
        <q-btn
          ref="scroll_to_top_button"
          color="dark"
          class="accent-dark"
          icon="arrow_upward"
          @click="scrollToTop"
        >
          {{ $t("submissions.comment.scroll_to_top") }}
        </q-btn>
      </div>
    </section>
  </q-scroll-area>
</template>

<script setup lang="ts">
import { ref, watch, computed, nextTick } from "vue"
import NewInlineComment from "../NewInlineCommentComponent.vue"
import InlineComment from "src/components/atoms/InlineComment.vue"
import { scroll } from "quasar"
import {
  useSubmission,
  useActiveComment,
  type NewInlineComment as NewInlineCommentType,
  type ActiveComment
} from "src/use/submissionContext"
import type { InlineComment as InlineCommentType } from "src/graphql/generated/graphql"
const { getScrollTarget, setVerticalScrollPosition } = scroll

const submission = useSubmission()
const activeComment = useActiveComment()
function isNewInlineComment(
  comment: InlineCommentType | NewInlineCommentType | ActiveComment
): comment is NewInlineCommentType {
  return "new" in comment
}

const isActiveCommentNew = computed(() => {
  return activeComment.value != null && isNewInlineComment(activeComment.value)
})

const commentRefs = ref<any[]>([])
const inline_comments_section = ref<HTMLElement | null>(null)

const inline_comments = computed(() => {
  const comments: (InlineCommentType | NewInlineCommentType)[] = Array.isArray(
    submission.value?.inline_comments
  )
    ? [...submission.value.inline_comments]
    : []

  if (isActiveCommentNew.value && activeComment.value) {
    comments.push(activeComment.value as NewInlineCommentType)
  }
  return comments
    .filter((c) => {
      return c.deleted_at === null || ("replies" in c && c.replies?.length > 0)
    })
    .sort((a, b) => {
      return a.from - b.from
    })
})

function scrollToTop() {
  const target = getScrollTarget(inline_comments_section.value)
  setVerticalScrollPosition(target, 0, 250)
}

function closeEditor() {
  activeComment.value = null
}
watch(
  activeComment,
  (newValue) => {
    if (!newValue) return
    if (!newValue.__typename.startsWith("InlineComment")) return
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
      const getOffsetTop = function (element) {
        if (!element) return 0
        return getOffsetTop(element.offsetParent) + element.offsetTop
      }
      const primaryNavHeight = 70
      const secondaryNavHeight = 48
      const tertiaryNavHeight = 75
      const negativeSpaceAdjustment = 14
      const offset =
        getOffsetTop(scrollTarget) -
        primaryNavHeight -
        secondaryNavHeight -
        tertiaryNavHeight -
        negativeSpaceAdjustment
      const target = getScrollTarget(scrollTarget)
      setVerticalScrollPosition(target, offset, 250)
    })
  },
  { deep: false }
)
</script>
