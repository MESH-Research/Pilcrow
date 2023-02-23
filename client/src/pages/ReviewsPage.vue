<template>
  <article>
    <h2 class="q-pl-lg">{{ $t(`reviews.page_title`) }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-10 col-sm-11 col-xs-12">
        <submission-table :table-data="reviewer_reviews" table-type="reviews" />
      </section>
      <section class="col-md-10 col-sm-11 col-xs-12 q-mt-md">
        <submission-table
          v-if="coordinator_reviews.length > 0"
          :table-data="coordinator_reviews"
          table-type="reviews"
          role="coordinator"
        />
      </section>
    </div>
  </article>
</template>

<script setup>
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import SubmissionTable from "src/components/SubmissionTable.vue"

const { result } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  return result.value?.currentUser?.submissions ?? []
})
const reviewer_reviews = computed(() =>
  submissions.value.filter(function (submission) {
    return (
      ["DRAFT", "INITIALLY_SUBMITTED", "AWAITING_REVIEW"].includes(
        submission.status
      ) === false && submission.my_role == "reviewer"
    )
  })
)
const coordinator_reviews = computed(() =>
  submissions.value.filter(function (submission) {
    return (
      submission.status != "DRAFT" && submission.my_role == "review_coordinator"
    )
  })
)
</script>
