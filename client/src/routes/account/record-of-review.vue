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
    <q-banner
      v-if="selected_assignments.length > 1"
      rounded
      inline-actions
      class="ror-download-callout q-mb-lg"
    >
      <template #avatar>
        <q-icon name="folder_zip" color="accent" />
      </template>
      {{ $t("record_of_review.download_all.byline") }}
      <template #action>
        <q-btn
          :label="$t('record_of_review.download_all.label')"
          icon="folder_zip"
          color="accent"
          @click="downloadDialog = true"
        />
      </template>
      <q-dialog v-model="downloadDialog" data-cy="ror_download_dialog">
        <q-card style="min-width: 320px">
          <dialog-title icon="download">
            {{ $t("record_of_review.download_all.dialog.title") }}
          </dialog-title>
          <q-card-section>
            <q-option-group
              v-model="downloadFormat"
              type="radio"
              :label="$t('record_of_review.download_all.dialog.format_label')"
              :options="[
                {
                  label: $t(
                    'record_of_review.download_all.dialog.format_combined'
                  ),
                  value: 'combined'
                },
                {
                  label: $t('record_of_review.download_all.dialog.format_zip'),
                  value: 'zip'
                }
              ]"
            />
          </q-card-section>
          <q-card-actions align="right">
            <q-btn
              v-close-popup
              flat
              :label="$t('record_of_review.download_all.dialog.cancel')"
            />
            <q-btn
              v-close-popup
              color="accent"
              :loading="downloading"
              :label="$t('record_of_review.download_all.dialog.confirm')"
              data-cy="ror_download_confirm"
              @click="confirmDownload"
            />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-banner>
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
import RecordOfReviewTable from "src/components/ror/RecordOfReviewTable.vue"
import RecordOfReview from "src/components/ror/RecordOfReview.vue"
import DialogTitle from "src/components/atoms/DialogTitle.vue"
import {
  buildRorExportBlob,
  buildRorExportHtml,
  buildRorZipBlob,
  type RorZipEntry
} from "src/utils/recordOfReviewExport"
import {
  GetRecordsOfReviewDocument,
  type GetRecordsOfReviewQuery
} from "src/graphql/generated/graphql"

definePage({
  name: "account:record_of_review",
  meta: {
    navigation: {
      icon: "history_edu",
      label: "record_of_review.title",
      order: 30
    },
    // Beta-gated: the account menu and header only surface this page while
    // the user has opted into the `record_of_review` feature. `private`
    // keeps the opt-in itself hidden from non-beta users in Labs.
    feature: { key: "record_of_review", private: true }
  }
})

type AssignmentRow = NonNullable<
  NonNullable<GetRecordsOfReviewQuery["currentUser"]>["submissions"]
>["data"][number]

type RecordOfReviewExposed = {
  getRecordElement: () => HTMLElement | null
}

const { t } = useI18n()
const selected_assignments = ref<AssignmentRow[]>([])
const recordRefs = ref<RecordOfReviewExposed[]>([])
const downloadDialog = ref(false)
const downloadFormat = ref<"combined" | "zip">("combined")
const downloading = ref(false)

function triggerDownload(blob: Blob, filename: string) {
  const url = URL.createObjectURL(blob)
  const anchor = document.createElement("a")
  anchor.href = url
  anchor.download = filename
  document.body.appendChild(anchor)
  anchor.click()
  anchor.remove()
  // Defer revoke so the browser has time to start the download — revoking
  // immediately cancels the download in Safari and some Chromium versions.
  setTimeout(() => URL.revokeObjectURL(url), 0)
}

async function confirmDownload() {
  const pairs = recordRefs.value
    .map((c, i) => ({
      element: c?.getRecordElement?.() ?? null,
      assignment: selected_assignments.value[i]
    }))
    .filter(
      (p): p is { element: HTMLElement; assignment: AssignmentRow } =>
        p.element !== null && p.assignment !== undefined
    )
  if (pairs.length === 0) return

  downloading.value = true
  try {
    if (downloadFormat.value === "zip") {
      const entries: RorZipEntry[] = pairs.map(({ element, assignment }) => ({
        element,
        filename: `record_of_review_${assignment.id}.html`,
        title: t("record_of_review.title_record", {
          title: assignment.submission.title
        })
      }))
      const blob = await buildRorZipBlob(entries)
      triggerDownload(blob, "records_of_review.zip")
    } else {
      const html = await buildRorExportHtml(
        pairs.map((p) => p.element),
        t("record_of_review.title_combined")
      )
      triggerDownload(buildRorExportBlob(html), "records_of_review.html")
    }
  } finally {
    downloading.value = false
  }
}
</script>

<style lang="sass" scoped>
@import 'src/css/quasar.variables.sass'

.ror-download-callout
  border: 1px solid $accent
  background: rgba($accent, 0.06)
</style>
