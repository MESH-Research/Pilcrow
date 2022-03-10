<template>
  <section class="comments">
    <div class="comments-wrapper">
      <h3 class="text-h1">Overall Comments</h3>
      <submission-comment is-overall-comment />
      <submission-comment is-overall-comment />
    </div>
  </section>
</template>

<script setup>
import SubmissionComment from "src/components/atoms/SubmissionComment.vue"
</script>

<style lang="sass" scoped>
.comments
  background-color: #efefef

.comments-wrapper
  max-width: 700px
  margin: 0 auto
  padding: 10px 60px 60px
</style>
