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
import type { ExportCommentBase } from "./ExportComment.vue"

interface Props {
  heading: string
  comments?: ExportCommentBase[]
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
    const key = props.sortBy as keyof ExportCommentBase
    return [...filtered].sort((a, b) => Number(a[key]) - Number(b[key]))
  }
  return filtered
})
</script>
