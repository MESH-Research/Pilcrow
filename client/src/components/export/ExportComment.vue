<template>
  <div :id="anchorId" :class="commentClass">
    <div class="comment">
      <div v-if="comment.deleted_at" class="comment-header">
        <div>
          <span class="text-caption">
            {{
              $t("submissions.comment.dateLabelDeleted", {
                date: formatDate(comment.deleted_at)
              })
            }}
          </span>
        </div>
      </div>
      <div v-else class="comment-header">
        <div>
          <a
            v-if="isTopLevelInline"
            :href="`#comment-highlight-${comment.id}`"
            class="highlight-link"
            :aria-label="$t('submissions.comment.reference.go_to_highlight')"
            >&#8679;</a
          >
          <span v-if="commentNumber" class="comment-number"
            >#{{ commentNumber }}</span
          >
          <span class="comment-author">{{
            comment.created_by.display_label
          }}</span>
          <span class="text-caption">
            {{
              formatDate(
                comment.updated_at !== comment.created_at
                  ? comment.updated_at
                  : comment.created_at
              )
            }}
          </span>
        </div>
      </div>
      <div v-if="replyTo && !comment.deleted_at" class="reply-reference">
        <a :href="`#${replyToAnchorId}`" class="reply-link">
          {{
            $t("submissions.comment.reference.in_reply_to", {
              username: replyTo.created_by.display_label
            })
          }}
        </a>
      </div>
      <!-- eslint-disable vue/no-v-html -->
      <div
        v-if="!comment.deleted_at"
        class="comment-content"
        v-html="comment.content"
      />
      <!-- eslint-enable vue/no-v-html -->
      <div v-if="comment.style_criteria?.length" class="style-criteria-section">
        <span
          v-for="criteria in comment.style_criteria"
          :key="criteria.icon"
          class="style-criteria-chip"
          >{{ criteria.name }}</span
        >
      </div>
    </div>
    <div v-if="comment.replies?.length" :class="repliesClass">
      <export-comment
        v-for="reply in comment.replies"
        :key="reply.id"
        :comment="reply"
        :parent-id="comment.id"
        :siblings="comment.replies"
        is-reply
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { DateTime } from "luxon"
import { useTimeAgo } from "src/use/timeAgo"
export interface ExportCommentBase {
  __typename?: string
  id: string
  content: string
  created_at: string
  updated_at: string
  deleted_at?: string | null
  created_by: { display_label?: string | null }
  from?: number | null
  to?: number | null
  reply_to_id?: string
  style_criteria?: Array<{ icon?: string | null; name?: string | null }> | null
  replies?: ExportCommentBase[]
}

const timeAgo = useTimeAgo()

interface Props {
  comment: ExportCommentBase
  commentNumber?: number | null
  parentId?: string | null
  siblings?: ExportCommentBase[]
  isReply?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  commentNumber: null,
  parentId: null,
  siblings: () => [],
  isReply: false
})

const commentPrefix = computed(() => {
  const type = props.comment.__typename
  if (type === "InlineComment") return "inline-comment"
  return "overall-comment"
})

const anchorId = computed(() => `${commentPrefix.value}-${props.comment.id}`)

const isTopLevelInline = computed(() => {
  return props.comment.__typename === "InlineComment"
})

const commentClass = computed(() => {
  if (props.isReply) return "comment-reply"
  return isTopLevelInline.value ? "inline-comment" : "overall-comment"
})

const repliesClass = computed(() => {
  return isTopLevelInline.value
    ? "inline-comment-replies"
    : "overall-comment-replies"
})

const replyTo = computed(() => {
  if (!props.comment.reply_to_id) return null
  return props.siblings.find((r) => r.id === props.comment.reply_to_id)
})

const replyToAnchorId = computed(() => {
  if (!replyTo.value) return null
  return `${commentPrefix.value}-${replyTo.value.id}`
})

function formatDate(isoDate: string | null | undefined) {
  if (!isoDate) return ""
  const dt = DateTime.fromISO(isoDate)
  return timeAgo.format(dt.toJSDate(), "long")
}
</script>
