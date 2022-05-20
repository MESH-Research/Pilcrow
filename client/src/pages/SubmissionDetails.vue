<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.submissions', 2)"
          to="/submissions"
        />
        <q-breadcrumbs-el :label="$t('submissions.details_heading')" />
      </q-breadcrumbs>
    </nav>
    <h2 class="q-pl-lg">{{ submission.title }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-12">
        <q-btn
          data-cy="submission_review_btn"
          color="primary"
          size="lg"
          :label="$t('submissions.view_heading')"
          :to="{
            name: 'submission_review',
            params: { id: props.id },
          }"
        />
      </section>
    </div>
    <submission-users
      data-cy="submitters_list"
      relationship="submitters"
      :submission="submission"
    />
    <submission-users
      relationship="review_coordinators"
      data-cy="coordinators_list"
      :submission="submission"
      mutable
      :max-users="1"
    />
    <submission-users
      data-cy="reviewers_list"
      relationship="reviewers"
      :submission="submission"
      mutable
    />
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import SubmissionUsers from "src/components/SubmissionUsers.vue"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_SUBMISSION, { id: props.id })
const submission = computed(() => {
  return result.value?.submission ?? null
})
</script>
