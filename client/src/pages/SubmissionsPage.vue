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
        <h3>Newest Comments</h3>
      </div>
      <div class="row q-col-gutter-lg">
        <div v-for="comment in inline_comments" :key="comment.id" class="col-3">
          <comment-preview :comment="comment" />
        </div>
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
const inline_comments = computed(() => {
  const comments = submitter_submissions.value.map((submission) => {
    return submission.inline_comments.map(
      (comment) => ({
        ...comment, submission_title: submission.title
      })
    )
  })
  return comments.flat().sort((a, b) => {
    return a.updated_at - b.updated_at
  }).reverse()
})
</script>
