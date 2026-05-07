<template>
  <section data-cy="record_of_review" class="q-pa-lg">
    <div class="q-pb-md">
      <h1 class="q-my-none text-h2">
        {{ $t(`record_of_review.title`) }}
        <q-icon name="info">
          <q-tooltip>{{ $t(`record_of_review.tooltip`) }}</q-tooltip>
        </q-icon>
      </h1>
      <i18n-t
        keypath="record_of_review.byline"
        class="q-mb-none"
        tag="p"
        scope="global"
      ></i18n-t>
    </div>
    <record-of-review-table
      ref="tableRef"
      v-model:selected="selected_assignments"
      :query="GetRecordsOfReviewDocument"
      data-cy="record-of-review_table"
    />
  </section>
  <section id="report" class="q-pa-lg">
    <div v-if="selected_assignments.length > 1" class="q-pa-lg">
      <q-btn
        :label="$t('record_of_review.download_all.label')"
        icon="download"
        color="accent"
        class="q-mb-sm"
        @click="handleDownloadAll"
      />
      <p>
        <q-icon name="info" />
        {{ $t("record_of_review.download_all.byline") }}
      </p>
    </div>
    <record-of-review
      v-for="assignment in selected_assignments"
      :key="assignment.id"
      :assignment="assignment"
    />
  </section>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetRecordsOfReview(
    $page: Int
    $first: Int
    $search: String
    $status: [SubmissionStatus!]
    $roles: [SubmissionUserRoles!]
    $publication: [ID!]
    $orderBy: [SubmissionAssignmentOrderBy!]
  ) {
    currentUser {
      id
      submissions(
        page: $page
        first: $first
        search: $search
        status: $status
        roles: $roles
        publication: $publication
        orderBy: $orderBy
      ) {
        ...QueryTable
        data {
          ...recordOfReviewRow
          ...recordOfReview
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { ref } from "vue"
import RecordOfReviewTable from "src/components/RecordOfReviewTable.vue"
import RecordOfReview from "src/components/RecordOfReview.vue"
import {
  GetRecordsOfReviewDocument,
  type GetRecordsOfReviewQuery
} from "src/graphql/generated/graphql"

type AssignmentRow = NonNullable<
  NonNullable<GetRecordsOfReviewQuery["currentUser"]>["submissions"]
>["data"][number]

const selected_assignments = ref<AssignmentRow[]>([])

function handleDownloadAll() {
  const downloadButtons = document.querySelectorAll(".record-download-button")
  downloadButtons.forEach((button) => {
    const clickEvent = new MouseEvent("click", {
      view: window,
      bubbles: true,
      cancelable: false
    })
    button.dispatchEvent(clickEvent)
  })
}
</script>
