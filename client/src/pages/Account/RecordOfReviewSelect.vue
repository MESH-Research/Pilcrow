<template>
  <article data-cy="record_of_review" class="q-pa-lg">
    <h1 class="text-h2 q-pl-md" data-cy="page_heading">Record of Review</h1>
    <h2 class="text-h3 q-pl-md">Select a submission</h2>
    <section>
      <div v-if="subsLoading" class="q-pa-lg">
        {{ $t("loading") }}
      </div>
      <div v-else-if="currentUser" class="col-12">
        <submission-table
          :table-data="submitter_submissions"
          variation="submissions_page"
          table-type="submissions"
          role="submitter"
          data-cy="submissions_table"
        />
      </div>
    </section>
  </article>
</template>

<script setup lang="ts">
import { useCurrentUser } from "src/use/user"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import { compareDatesDesc } from "src/utils/dateSort"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"

import SubmissionTable from "src/components/SubmissionTable.vue"

const { currentUser } = useCurrentUser()
const { result, loading: subsLoading } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  const s = result.value?.currentUser?.submissions ?? []
  return [...s].sort((a, b) => compareDatesDesc(a.created_at, b.created_at))
})
const submitter_submissions = computed(() =>
  submissions.value.filter(function (submission) {
    return submission.my_role == "submitter"
  })
)
</script>
