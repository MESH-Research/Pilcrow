import { ref, computed } from "vue"
import { useActiveComment } from "src/use/submissionContext"
import type { Comment } from "src/graphql/generated/graphql"

/**
 * Reply/modify editor state machine shared by comment thread components
 * (InlineComment, OverallComment, CommentReply).
 */
export function useCommentReplyState() {
  const isReplying = ref(false)
  const isQuoteReplying = ref(false)
  const commentReply = ref(null)
  const isModifying = ref(null)
  const commentModify = ref(null)

  function resetReplyState() {
    isReplying.value = false
    isModifying.value = false
    isQuoteReplying.value = false
    commentReply.value = null
  }

  const submitReply = resetReplyState
  const cancelReply = resetReplyState

  function initiateReply() {
    isReplying.value = true
    isModifying.value = false
    isQuoteReplying.value = false
  }

  function initiateQuoteReply(comment?: Comment) {
    isReplying.value = true
    isModifying.value = false
    isQuoteReplying.value = true
    commentReply.value = comment ?? null
  }

  function modifyComment(comment) {
    isReplying.value = false
    isQuoteReplying.value = false
    isModifying.value = true
    commentModify.value = comment
  }

  return {
    isReplying,
    isQuoteReplying,
    commentReply,
    isModifying,
    commentModify,
    resetReplyState,
    submitReply,
    cancelReply,
    initiateReply,
    initiateQuoteReply,
    modifyComment
  }
}

/**
 * Whether the given comment is the currently active one in the
 * submission review context.
 */
export function useIsActiveComment(comment: {
  __typename?: string
  id: string
}) {
  const activeComment = useActiveComment()
  const isActive = computed(() => {
    return (
      activeComment.value?.__typename === comment.__typename &&
      activeComment.value?.id === comment.id
    )
  })
  return { activeComment, isActive }
}
