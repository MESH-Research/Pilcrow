<template>
  <article>
    <h2 class="q-pl-lg">{{ $t(`reviews.page_title`) }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-10 col-sm-11 col-xs-12">
        <submission-table
          :table-data="reviewer_reviews"
          table-title="To Review"
          table-byline="Reviews in which you're assigned as a <strong>reviewer</strong>"
          type="reviews"
        />
      </section>
      <section class="col-md-10 col-sm-11 col-xs-12 q-mt-lg">
        <submission-table
          v-if="coordinator_reviews.length > 0"
          :table-data="coordinator_reviews"
          table-title="To Coordinate"
          table-byline="Reviews in which you're assigned as a
        <strong>review coordinator</strong>"
          type="reviews"
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
      submission.status != "DRAFT" &&
      submission.status != "INITIALLY_SUBMITTED" &&
      submission.my_role == "reviewer"
    )
  })
)
const coordinator_reviews = computed(() =>
  submissions.value.filter(function (submission) {
    return (
      submission.status != "DRAFT" &&
      submission.status != "INITIALLY_SUBMITTED" &&
      submission.my_role == "review_coordinator"
    )
  })
)
</script>
