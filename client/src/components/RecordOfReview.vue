<template>
  <article data-cy="record_of_review" class="q-mb-lg">
    <q-card bordered>
      <div class="flex justify-end q-mt-md q-mr-md">
        <q-btn
          :label="$t('record_of_review.download_one')"
          icon="download"
          color="accent"
          :href="blobUrl"
          class="record-download-button"
          :download="`record_of_review_${review.id}.html`"
        />
      </div>
      <div ref="recordContainer">
        <q-card-section>
          <h1 class="text-h2 q-mt-none" data-cy="page_heading">
            {{
              $t("record_of_review.title_record", {
                title: review.title
              })
            }}
          </h1>
          <h2 class="text-h3">{{ $t("record_of_review.title_reviewers") }}</h2>
          <div
            v-if="
              review.review_coordinators.length === 0 &&
              review.reviewers.length === 0
            "
          >
            <p>{{ $t("record_of_review.no_users") }}</p>
          </div>
          <div v-else class="row items-start q-gutter-md items-stretch">
            <record-of-review-user
              v-for="coordinator in review.review_coordinators"
              :key="coordinator.id"
              :user="coordinator"
              role="Review Coordinator"
            />
            <record-of-review-user
              v-for="reviewer in review.reviewers"
              :key="reviewer.id"
              :user="reviewer"
              role="Reviewer"
            />
          </div>
          <h2 class="text-h3">{{ $t("record_of_review.title_review") }}</h2>
          <dl>
            <dt>{{ $t("publication.entity", 1) }}</dt>
            <dd>{{ review.publication.name }}</dd>
            <template
              v-for="editor in review.publication.editors"
              :key="editor.id"
            >
              <dt>{{ $t("publication.editor", 1) }}</dt>
              <dd>{{ editor.display_label }}</dd>
            </template>
            <dt>{{ $t("record_of_review.document_type.heading") }}</dt>
            <dd>{{ $t("record_of_review.document_type.journal_article") }}</dd>
            <dt>{{ $t("record_of_review.completed.heading") }}</dt>
            <dd>{{ getCompletionDate(review) }}</dd>
            <dt>{{ $t("record_of_review.identifier") }}</dt>
            <dd>{{ review.id }}</dd>
          </dl>
        </q-card-section>
      </div>
    </q-card>
  </article>
</template>

<script setup lang="ts">
import RecordOfReviewUser from "src/components/atoms/RecordOfReviewUser.vue"
import { post_review_states } from "src/utils/postReviewStates"
import type {
  Submission,
  SubmissionAudit
} from "src/graphql/generated/graphql.ts"
import { DateTime } from "luxon"
import { ref, watch } from "vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const blobUrl = ref("")
const recordContainer = ref<HTMLElement | null>(null)

function getCompletionDate(review: Submission) {
  const audits = [...(review.audits as SubmissionAudit[])]
  const filtered = audits.filter(function (audit: SubmissionAudit) {
    const status = audit.new_values?.status
    return typeof status === "string" && post_review_states.includes(status)
  })
  if (filtered.length > 0) {
    filtered.sort((a, b) => {
      return a.created_at - b.created_at
    })
    const last_audit = filtered.pop()
    if (last_audit) {
      return DateTime.fromISO(last_audit.created_at).toFormat("yyyy-MM-dd")
    }
    return t("record_of_review.completed.unknown")
  }
  return t("record_of_review.completed.incomplete")
}

interface Props {
  review: Submission
}

const props = defineProps<Props>()

function updateBlob() {
  const el = recordContainer.value
  if (!el) return
  const doc = new DOMParser().parseFromString(el.innerHTML, "text/html")
  doc.title = t("record_of_review.title_record", {
    title: props.review.title
  })
  const html = doc.documentElement.outerHTML
  blobUrl.value = URL.createObjectURL(new Blob([html], { type: "text/html" }))
}

watch(recordContainer, () => updateBlob())
</script>
