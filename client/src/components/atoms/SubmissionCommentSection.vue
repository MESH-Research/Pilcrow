<template>
  <section class="comments">
    <div class="comments-wrapper">
      <h3 class="text-h1">Overall Comments</h3>
      <overall-comment
        v-for="comment in overall_comments"
        :key="comment.id"
        :comment="comment"
      />
    </div>
  </section>
</template>

<script setup>
import OverallComment from "src/components/atoms/OverallComment.vue"
import { computed, inject } from "vue"

const submission = inject("submission")

const overall_comments = computed(() => {
  return submission.value?.overall_comments ?? []
})
</script>

<style lang="sass" scoped>
.comments
  background-color: #efefef

.comments-wrapper
  max-width: 700px
  margin: 0 auto
  padding: 10px 60px 60px
</style>
