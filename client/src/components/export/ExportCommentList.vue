<template>
  <section v-if="comments.length">
    <h3 class="text-h3">{{ heading }}</h3>
    <export-comment
      v-for="comment in sortedComments"
      :key="comment.id"
      :comment="comment"
      :comment-number="numberMap[comment.id]"
    />
  </section>
</template>

<script setup lang="ts">
import { computed } from "vue"
import ExportComment from "./ExportComment.vue"
import type { Comment } from "src/graphql/generated/graphql"

interface ExportComment extends Comment {
  __typename?: string
  replies?: ExportComment[]
  [key: string]: unknown
}

interface Props {
  heading: string
  comments?: ExportComment[]
  numberMap?: Record<string, number>
  sortBy?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  comments: () => [],
  numberMap: () => ({}),
  sortBy: null
})

const sortedComments = computed(() => {
  const filtered = props.comments.filter(
    (c) => c.deleted_at === null || c.replies?.length > 0
  )
  if (props.sortBy) {
    const key = props.sortBy
    return [...filtered].sort((a, b) => Number(a[key]) - Number(b[key]))
  }
  return filtered
})
</script>
