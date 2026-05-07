<template>
  <section data-cy="record_of_review" class="q-pa-lg">
    <div v-if="subsLoading" class="q-pa-lg">
      {{ $t("loading") }}
    </div>
    <div v-else-if="all_reviews" class="col-12">
      <record-of-review-table
        v-model:selected="selected_reviews"
        :table-data="reviews"
        data-cy="record-of-review_table"
      />
    </div>
  </section>
  <section id="report" class="q-pa-lg">
    <div v-if="selected_reviews.length > 1" class="q-pa-lg">
      <q-btn
        :label="$t('record_of_review.download_all.label')"
        icon="download"
        color="accent"
        class="q-mb-sm"
        @click="handleDownloadAll"
      />
      <p>
        <q-icon name="info" />
        {{ $t("record_of_review.download_all.byline") }}
      </p>
    </div>
    <record-of-review
      v-for="review in selected_reviews"
      :key="review['id']"
      :review="review"
    />
  </section>
</template>

<script setup lang="ts">
import { post_review_states } from "src/utils/postReviewStates"
import { useQuery } from "@vue/apollo-composable"
import { computed, ref } from "vue"
import { compareDatesDesc } from "src/utils/dateSort"
import { GET_RECORDS_OF_REVIEW } from "src/graphql/queries"
import RecordOfReviewTable from "src/components/RecordOfReviewTable.vue"
import RecordOfReview from "src/components/RecordOfReview.vue"

import type { Submission } from "src/graphql/generated/graphql.ts"

const selected_reviews = ref<Submission[]>([])

const { result: all_submissions_result, loading: subsLoading } = useQuery(
  GET_RECORDS_OF_REVIEW,
  {
    page: 1
  }
)
const all_reviews = computed(() => {
  const cs = all_submissions_result.value?.currentUser?.submissions ?? []
  const as = all_submissions_result.value?.submissions?.data ?? []
  const s = cs.concat(as)
  const f = s.filter((submission: Submission) =>
    post_review_states.includes(submission.status)
  )
  return f
})
const reviews = computed(() => {
  const s = all_reviews.value
  return [...s].sort((a, b) => compareDatesDesc(a.created_at, b.created_at))
})

function handleDownloadAll() {
  const downloadButtons = document.querySelectorAll(".record-download-button")
  downloadButtons.forEach((button) => {
    const clickEvent = new MouseEvent("click", {
      view: window,
      bubbles: true,
      cancelable: false
    })
    button.dispatchEvent(clickEvent)
  })
}
</script>
