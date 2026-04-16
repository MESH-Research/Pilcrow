<template>
  <article data-cy="record_of_review" class="q-mb-lg">
    <q-card bordered class="q-pa-lg">
      <div class="flex row justify-between q-gutter-md">
        <h1 class="text-h2" data-cy="page_heading">
          {{
            $t("record_of_review_table.title_record", {
              title: review.title
            })
          }}
        </h1>
        <div>
          <q-btn
            :label="$t('record_of_review_table.download_one')"
            icon="download"
            color="accent"
            @click="console.log('Download')"
          ></q-btn>
        </div>
      </div>
      <h2 class="text-h3">Reviewer Information</h2>
      <div
        v-if="
          review.review_coordinators.length === 0 &&
          review.reviewers.length === 0
        "
      >
        <p>No users assigned.</p>
      </div>
      <div v-else class="row items-start q-gutter-md items-stretch">
        <record-of-review-user
          v-for="coordinator in review.review_coordinators"
          :key="coordinator.id"
          :user="coordinator"
          role="Review Coordinator"
        />
        <record-of-review-user
          v-for="reviewer in review.reviewers"
          :key="reviewer.id"
          :user="reviewer"
          role="Reviewer"
        />
      </div>
      <h2 class="text-h3">Review Information</h2>
      <dl>
        <dt>Publication</dt>
        <dd>{{ review.publication.name }}</dd>
        <template v-for="editor in review.publication.editors" :key="editor.id">
          <dt>Editor</dt>
          <dd>{{ editor.display_label }}</dd>
        </template>
        <dt>Document Type</dt>
        <dd>Journal Article</dd>
        <dt>Review Completed</dt>
        <dd>{{ getCompletionDate(review) }}</dd>
        <dt>Review Identifier</dt>
        <dd>{{ review.id }}</dd>
      </dl>
    </q-card>
  </article>
</template>

<script setup lang="ts">
import RecordOfReviewUser from "src/components/atoms/RecordOfReviewUser.vue"
import { post_review_states } from "src/utils/postReviewStates"
import type {
  Submission,
  SubmissionAudit
} from "src/graphql/generated/graphql.ts"
import { DateTime } from "luxon"

function getCompletionDate(review: Submission) {
  const audits = [...(review.audits as SubmissionAudit[])]
  const filtered = audits.filter(function (audit: SubmissionAudit) {
    return post_review_states.includes(audit.new_values?.status)
  })
  if (filtered.length > 0) {
    filtered.sort((a, b) => {
      return a.created_at - b.created_at
    })
    const last_audit = filtered.pop()
    return DateTime.fromISO(last_audit.created_at).toFormat("yyyy-MM-dd")
  }
  return "Incomplete"
}

interface Props {
  review: Submission
}

defineProps<Props>()
</script>
