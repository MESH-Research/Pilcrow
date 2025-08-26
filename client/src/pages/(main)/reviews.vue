<template>
  <article>
    <h2 class="q-pl-lg">{{ $t(`reviews.page_title`) }}</h2>
    <section v-if="all_reviews.length > 0" class="row q-col-gutter-lg q-pa-lg">
      <div class="col-12">
        <submission-table
          :data-cy="`${currentUser.highest_privileged_role}_table`"
          :table-data="all_reviews"
          variation="reviews_page"
          table-type="reviews"
          :role="currentUser.highest_privileged_role"
        />
      </div>
    </section>
    <section
      v-if="coordinator_reviews.length > 0"
      class="row q-col-gutter-lg q-pa-lg"
    >
      <div class="col-12">
        <submission-table
          data-cy="coordinator_table"
          :table-data="coordinator_reviews"
          table-type="reviews"
          role="review_coordinator"
        />
      </div>
    </section>
    <section class="row q-col-gutter-lg q-pa-lg">
      <div class="col-12">
        <submission-table
          data-cy="reviewer_table"
          :table-data="reviewer_reviews"
          table-type="reviews"
        />
      </div>
    </section>
  </article>
</template>

<script setup lang="ts">
import { useCurrentUser } from "src/use/user"
import SubmissionTable from "src/components/SubmissionTable.vue"
import { CURRENT_USER_SUBMISSIONS, GET_SUBMISSIONS } from "src/graphql/queries"

definePage({
  name: "reviews"
})

const { currentUser } = useCurrentUser()
const { result: all_submissions_result } = useQuery(GET_SUBMISSIONS, {
  page: 1
})
const all_submissions = computed(() => {
  return all_submissions_result.value?.submissions.data ?? []
})
const all_reviews = computed(() =>
  all_submissions.value.filter(function (submission) {
    return ["DRAFT"].includes(submission.status) === false
  })
)
const { result } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  const r = result.value?.currentUser?.submissions ?? []
  return [...r].sort((a, b) => {
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  })
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
