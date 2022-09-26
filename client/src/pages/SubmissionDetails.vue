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
    <section>
      <q-banner class="light-grey">
        <div class="flex row items-center">
          <h3 class="q-ml-sm q-mr-md text-h4">
            {{ $t("submission.status.title") }}
          </h3>
          <q-separator vertical />
          <q-chip
            class="q-ml-md"
            icon="radio_button_checked"
            color="primary"
            text-color="white"
          >
            {{ $t(`submission.status.${submission.status}`) }}
          </q-chip>
          <q-space />
          <q-btn
            data-cy="submission_review_btn"
            class="q-mr-sm"
            color="accent"
            size="lg"
            :label="$t('submissions.action.review.name')"
            :to="{
              name: 'submission_review',
              params: { id: props.id },
            }"
          />
        </div>
      </q-banner>
    </section>
    <section class="q-pa-lg">
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-xs-8 col-sm-5 col-md-3">
          <assigned-users
            data-cy="submitters_list"
            relationship="submitters"
            :container="submission"
          />
        </div>

        <div
          :class="{
            'q-mt-lg': $q.screen.width < 600,
          }"
          class="col-12 col-xs-8 col-sm-5 col-md-3"
        >
          <assigned-users
            relationship="review_coordinators"
            data-cy="coordinators_list"
            :container="submission"
            mutable
            :max-users="1"
          />
        </div>
      </div>
      <div class="row q-col-gutter-lg q-mt-lg">
        <div class="col-12 col-xs-8 col-sm-10 col-md-6">
          <assigned-users
            data-cy="reviewers_list"
            relationship="reviewers"
            :container="submission"
            mutable
          />
        </div>
      </div>
    </section>
    <section class="q-pa-lg" data-cy="activity_section">
      <h3>{{ $t("submission.activity_section.title") }}</h3>
      <p v-if="submission.audits.length == 0">
        {{ $t("submission.activity_section.no_activity") }}
      </p>
      <submission-audit
        v-for="audit in submission.audits.slice().reverse()"
        :key="audit.id"
        :audit="audit"
      />
    </section>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import AssignedUsers from "src/components/AssignedUsersComponent.vue"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import SubmissionAudit from "../components/SubmissionAudit.vue"
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
