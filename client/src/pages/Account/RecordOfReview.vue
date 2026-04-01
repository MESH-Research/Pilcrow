<template>
  <article data-cy="record_of_review" class="q-pa-lg">
    <h1 class="text-h2" data-cy="page_heading">Report</h1>
    <h2 class="text-h3">{{ review.title }}</h2>
    <h3>Reviewer Information</h3>
    <dl>
      <dt>Reviewer Name</dt>
      <dd>Value</dd>
      <dt>Reviewer ORCID</dt>
      <dd>Value</dd>
      <dt>Role</dt>
      <dd>Value</dd>
    </dl>
    <h3>Review Information</h3>
    <dl>
      <dt>Publication</dt>
      <dd>Value</dd>
      <dt>Editor</dt>
      <dd>Value</dd>
      <dt>Type</dt>
      <dd>Journal Article</dd>
      <dt>Date Completed</dt>
      <dd>Value</dd>
      <dt>Review Identifier</dt>
      <dd>Value</dd>
    </dl>
  </article>
</template>

<script setup lang="ts">
import type { Submission } from "src/graphql/generated/graphql"

interface Props {
  review: Submission
}

defineProps<Props>()
</script>
