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
        <h3>{{ $t(`submissions.latest_comments_heading`) }}</h3>
      </div>
      <div class="row q-col-gutter-lg">
        <div
          v-for="comment in latest_comments"
          :key="comment.id"
          class="col-lg-3 col-md-4 col-sm-6 col-xs-12"
        >
          <comment-preview class="flex fit" :comment="comment" />
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
const latest_comments = computed(() => {
  let comments = submitter_submissions.value.map((submission) => {
    const inline = submission.inline_comments
      .map((comment) => ({
        ...comment,
        submission_title: submission.title,
      }))
      .flat()
    const overall = submission.overall_comments
      .map((comment) => ({
        ...comment,
        submission_title: submission.title,
      }))
      .flat()
    return inline.concat(overall)
  })
  return comments
    .flat()
    .sort((a, b) => {
      return new Date(b.updated_at) - new Date(a.updated_at)
    })
    .slice(0, 4)
})
</script>
