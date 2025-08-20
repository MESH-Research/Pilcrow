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
    <section class="row no-wrap items-center q-px-lg q-pt-md">
      <submission-title />
    </section>
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
            color="secondary"
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
            :label="$t(`submission.action.${viewType}`)"
            :to="{
              name: `submission:${viewType}`,
              params: { id: props.id }
            }"
          />
        </div>
      </q-banner>
    </section>
    <section class="q-pa-lg">
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-xs-8 col-sm-5 col-md-3">
          <assigned-submission-users
            data-cy="submitters_list"
            role-group="submitters"
            :container="submission"
          />
        </div>

        <div
          :class="{
            'q-mt-lg': $q.screen.width < 600
          }"
          class="col-12 col-xs-8 col-sm-5 col-md-3"
        >
          <assigned-submission-users
            data-cy="coordinators_list"
            role-group="review_coordinators"
            :container="submission"
            mutable
            :max-users="1"
          />
        </div>
      </div>
      <div class="row q-col-gutter-lg q-mt-lg">
        <div class="col-12 col-xs-8 col-sm-10 col-md-6">
          <assigned-submission-users
            data-cy="reviewers_list"
            role-group="reviewers"
            :container="submission"
            mutable
          />
        </div>
      </div>
      <submission-export-button class="q-mt-xl" :submission="submission" />
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

<script setup lang="ts">
import AssignedSubmissionUsers from "src/components/AssignedSubmissionUsers.vue"
import SubmissionAudit from "../components/SubmissionAudit.vue"
import SubmissionTitle from "src/components/SubmissionTitle.vue"
import SubmissionExportButton from "src/components/atoms/SubmissionExportButton.vue"
import { GET_SUBMISSION } from "src/graphql/queries"
import { computed, provide, watchEffect, ref } from "vue"
import { useQuery } from "@vue/apollo-composable"

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})

const { result } = useQuery(GET_SUBMISSION, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})

const viewType = ref("review")

watchEffect(() => {
  const status = submission.value?.status
  if (status === "DRAFT") {
    viewType.value = "preview"
  } else if (status === "INITIALLY_SUBMITTED") {
    viewType.value = "view"
  }
})

provide("submission", submission)
</script>
