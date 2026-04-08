<template>
  <section data-cy="record_of_review" class="q-pa-lg">
    <section>
      <div v-if="subsLoading" class="q-pa-lg">
        {{ $t("loading") }}
      </div>
      <div v-else-if="all_submissions" class="col-12">
        <record-of-review-table
          v-model:selected="selected_reviews"
          :table-data="reviews"
          data-cy="record-of-review_table"
        />
      </div>
    </section>
  </section>
  <section id="report" class="q-pa-lg">
    <record-of-review
      v-for="review in selected_reviews"
      :key="review['id']"
      :review="review as any"
    />
  </section>
</template>

<script setup lang="ts">
import { useQuery } from "@vue/apollo-composable"
import { computed, ref } from "vue"
import { compareDatesDesc } from "src/utils/dateSort"
import { GET_RECORDS_OF_REVIEW } from "src/graphql/queries"
import RecordOfReviewTable from "src/components/atoms/RecordOfReviewTable.vue"
import RecordOfReview from "./RecordOfReview.vue"

const selected_reviews = ref([])

const { result: all_submissions_result, loading: subsLoading } = useQuery(
  GET_RECORDS_OF_REVIEW,
  {
    page: 1
  }
)
const all_submissions = computed(() => {
  let s = []
  s = all_submissions_result.value?.currentUser?.submissions ?? []
  return s
})
const reviews = computed(() => {
  const s = all_submissions.value
  return [...s].sort((a, b) => compareDatesDesc(a.created_at, b.created_at))
})
</script>
