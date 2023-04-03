<template>
  <article>
    <h2 class="q-pl-lg" data-cy="submissions_title">
      {{ $t(`submissions.heading`) }}
    </h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-12">
        <div v-if="subsLoading" class="q-pa-lg">
          {{ $t("loading") }}
        </div>

        <div v-else-if="currentUser" class="col-12">
          <submission-table
            :table-data="submitter_submissions"
            variation="submissions"
            table-type="submissions"
            role="submitter"
            data-cy="submissions_table"
          />
        </div>
      </section>
    </div>
  </article>
</template>

<script setup>
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import { useCurrentUser } from "src/use/user"
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import SubmissionTable from "src/components/SubmissionTable.vue"

const { currentUser } = useCurrentUser()
const { result, loading: subsLoading } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  return result.value?.currentUser?.submissions ?? []
})
const submitter_submissions = computed(() =>
  submissions.value.filter(function (submission) {
    return submission.my_role == "submitter"
  })
)

</script>
