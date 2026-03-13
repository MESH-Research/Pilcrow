<template>
  <section>
    <h3 class="text-h3">{{ $t("submissions.overall_comments.heading") }}</h3>
    <p v-if="!comments.length" class="text-caption">
      {{ $t("export.comments.none") }}
    </p>
    <div
      v-for="(comment, index) in comments"
      :id="`overall-comment-${comment.id}`"
      :key="comment.id"
      class="overall-comment"
    >
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
            <span class="comment-number">#{{ Number(index) + 1 }}</span>
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
        <!-- eslint-disable vue/no-v-html -->
        <div
          v-if="!comment.deleted_at"
          class="comment-content"
          v-html="comment.content"
        />
        <!-- eslint-enable vue/no-v-html -->
      </div>
      <div v-if="comment.replies?.length" class="overall-comment-replies">
        <div
          v-for="reply in comment.replies"
          :id="`overall-comment-${reply.id}`"
          :key="reply.id"
          class="comment-reply"
        >
          <div class="comment">
            <div v-if="reply.deleted_at" class="comment-header">
              <div>
                <span class="text-caption">
                  {{
                    $t("submissions.comment.dateLabelDeleted", {
                      date: formatDate(reply.deleted_at)
                    })
                  }}
                </span>
              </div>
            </div>
            <div v-else class="comment-header">
              <div>
                <span class="comment-author">{{
                  reply.created_by.display_label
                }}</span>
                <span class="text-caption">
                  {{
                    formatDate(
                      reply.updated_at !== reply.created_at
                        ? reply.updated_at
                        : reply.created_at
                    )
                  }}
                </span>
              </div>
            </div>
            <div
              v-if="replyTarget(reply, comment.replies) && !reply.deleted_at"
              class="reply-reference"
            >
              <a
                :href="`#overall-comment-${replyTarget(reply, comment.replies)?.id}`"
                class="reply-link"
              >
                {{
                  $t("submissions.comment.reference.in_reply_to", {
                    username: replyTarget(reply, comment.replies)?.created_by
                      .display_label
                  })
                }}
              </a>
            </div>
            <!-- eslint-disable vue/no-v-html -->
            <div
              v-if="!reply.deleted_at"
              class="comment-content"
              v-html="reply.content"
            />
            <!-- eslint-enable vue/no-v-html -->
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment exportOverallComments on Submission {
    overall_comments(createdBy: $createdBy) @skip(if: $skip_overall) {
      ...commentFields
      replies {
        ...commentFields
        parent_id
        reply_to_id
        read_at
      }
      read_at
    }
  }
`)
</script>

<script setup lang="ts">
import { DateTime } from "luxon"
import { useTimeAgo } from "src/use/timeAgo"
import { computed } from "vue"
import type { exportOverallCommentsFragment } from "src/graphql/generated/graphql"

type Comment = NonNullable<
  exportOverallCommentsFragment["overall_comments"]
>[number]
type Reply = NonNullable<Comment["replies"]>[number]

interface Props {
  submission: exportOverallCommentsFragment
}

const props = defineProps<Props>()

const comments = computed(() => props.submission.overall_comments ?? [])

const timeAgo = useTimeAgo()

function replyTarget(reply: Reply, siblings: Reply[]) {
  return siblings.find((r) => r.id === reply.reply_to_id)
}

function formatDate(isoDate: string | null | undefined) {
  if (!isoDate) return ""
  const dt = DateTime.fromISO(isoDate)
  return timeAgo.format(dt.toJSDate(), "long")
}
</script>
