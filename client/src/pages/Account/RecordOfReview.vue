<template>
  <article data-cy="record_of_review" class="q-pa-lg">
    <h1 class="text-h2" data-cy="page_heading">Report</h1>
    <h2 class="text-h3">{{ review.title }}</h2>
    <h3>Reviewer Information</h3>
    <q-card v-for="reviewer in review.reviewers" :key="reviewer.id" bordered>
      <q-card-section>
        <h4>{{ reviewer.display_label }}</h4>
        <dl>
          <dt v-if="reviewer.profile_metadata?.academic_profiles">
            Reviewer ORCID
          </dt>
          <dd v-if="reviewer.profile_metadata?.academic_profiles">
            {{ reviewer.profile_metadata?.academic_profiles.orcid_id }}
          </dd>
          <dt>Role</dt>
          <dd>Reviewer</dd>
        </dl>
      </q-card-section>
    </q-card>
    <h3>Review Information</h3>
    <dl>
      <dt>Publication</dt>
      <dd>{{ review.publication.name }}</dd>
      <template v-for="editor in review.publication.editors" :key="editor.id">
        <dt>Editor</dt>
        <dd>{{ editor.display_label }}</dd>
      </template>
      <dt>Type</dt>
      <dd>Journal Article (Make configurable?)</dd>
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
