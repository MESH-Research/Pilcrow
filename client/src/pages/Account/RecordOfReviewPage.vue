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
      ref="recordRefs"
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
import { useI18n } from "vue-i18n"
import RecordOfReviewTable from "src/components/RecordOfReviewTable.vue"
import RecordOfReview from "src/components/RecordOfReview.vue"
import {
  buildRorExportBlob,
  buildRorExportHtml
} from "src/utils/recordOfReviewExport"
import {
  GetRecordsOfReviewDocument,
  type GetRecordsOfReviewQuery
} from "src/graphql/generated/graphql"

type AssignmentRow = NonNullable<
  NonNullable<GetRecordsOfReviewQuery["currentUser"]>["submissions"]
>["data"][number]

type RecordOfReviewExposed = {
  getRecordElement: () => HTMLElement | null
}

const { t } = useI18n()
const selected_assignments = ref<AssignmentRow[]>([])
const recordRefs = ref<RecordOfReviewExposed[]>([])

async function handleDownloadAll() {
  const elements = recordRefs.value
    .map((c) => c?.getRecordElement?.() ?? null)
    .filter((el): el is HTMLElement => el !== null)
  if (elements.length === 0) return

  const html = await buildRorExportHtml(
    elements,
    t("record_of_review.title_combined")
  )
  const url = URL.createObjectURL(buildRorExportBlob(html))
  const anchor = document.createElement("a")
  anchor.href = url
  anchor.download = "records_of_review.html"
  document.body.appendChild(anchor)
  anchor.click()
  anchor.remove()
  // Defer revoke so the browser has time to start the download — revoking
  // immediately cancels the download in Safari and some Chromium versions.
  setTimeout(() => URL.revokeObjectURL(url), 0)
}
</script>
