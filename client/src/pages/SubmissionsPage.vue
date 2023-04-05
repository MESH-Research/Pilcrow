<template>
  <article>
    <h2 class="q-pl-lg" data-cy="submissions_title">
      {{ $t(`submissions.heading`) }}
    </h2>
    <section class="row q-col-gutter-lg q-pa-lg">
      <div class="col-12">
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
      </div>
    </section>
    <section class="q-pa-lg">
      <div class="row">
        <h3>New Comments</h3>
      </div>
      <div class="row q-col-gutter-lg">
        <div class="col-3"><comment-preview :comment="sampleComment" /></div>
        <div class="col-3"><comment-preview :comment="sampleComment" /></div>
        <div class="col-3"><comment-preview :comment="sampleComment" /></div>
        <div class="col-3"><comment-preview :comment="sampleComment" /></div>
      </div>
    </section>
  </article>
</template>

<script setup>
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import { useCurrentUser } from "src/use/user"
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import SubmissionTable from "src/components/SubmissionTable.vue"
import CommentPreview from "src/components/atoms/CommentPreview.vue"

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
const sampleComment = {
  created_at: "2022-06-05T01:57:20Z",
  created_by: {
    username: "Hello",
  },
  replies: [],
  content: "Lorem Ipsum",
}
</script>
