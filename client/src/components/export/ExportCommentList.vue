<template>
  <section v-if="comments.length">
    <h3 class="text-h3">{{ heading }}</h3>
    <export-comment
      v-for="comment in sortedComments"
      :key="comment.id"
      :comment="comment"
    />
  </section>
</template>

<script setup>
import { computed } from "vue"
import ExportComment from "./ExportComment.vue"

const props = defineProps({
  heading: {
    type: String,
    required: true
  },
  comments: {
    type: Array,
    default: () => []
  },
  sortBy: {
    type: String,
    default: null
  }
})

const sortedComments = computed(() => {
  const filtered = props.comments.filter(
    (c) => c.deleted_at === null || c.replies?.length > 0
  )
  if (props.sortBy) {
    return [...filtered].sort((a, b) => a[props.sortBy] - b[props.sortBy])
  }
  return filtered
})
</script>
