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
          :download="`record_of_review_${submission.id}.html`"
        />
      </div>
      <div ref="recordContainer">
        <q-card-section>
          <h1 class="text-h2 q-mt-none" data-cy="page_heading">
            {{
              $t("record_of_review.title_record", {
                title: submission.title
              })
            }}
          </h1>

          <h2 class="text-h3">
            {{ $t("record_of_review.title_participation") }}
          </h2>
          <dl>
            <dt>{{ $t("user.name") }}</dt>
            <dd>{{ assignment.user.name || assignment.user.display_label }}</dd>
            <dt>{{ $t("user.email") }}</dt>
            <dd>{{ assignment.user.email }}</dd>
            <template v-if="orcidId">
              <dt>
                {{
                  $t(
                    "account.profile.fields.profile_metadata.academic_profiles.orcid_id.label"
                  )
                }}
              </dt>
              <dd>{{ orcidId }}</dd>
            </template>
            <dt>{{ $t("submission_tables.columns.role") }}</dt>
            <dd>{{ $t(`admin.users.details.roles.${assignment.role}`) }}</dd>
          </dl>

          <h2 class="text-h3">{{ $t("record_of_review.title_team") }}</h2>
          <div
            v-if="
              submission.review_coordinators.length === 0 &&
              submission.reviewers.length === 0
            "
          >
            <p>{{ $t("record_of_review.no_users") }}</p>
          </div>
          <div v-else class="row items-start q-gutter-md items-stretch">
            <record-of-review-user
              v-for="coordinator in submission.review_coordinators"
              :key="coordinator.id"
              :user="coordinator"
              role="Review Coordinator"
            />
            <record-of-review-user
              v-for="reviewer in submission.reviewers"
              :key="reviewer.id"
              :user="reviewer"
              role="Reviewer"
            />
          </div>

          <h2 class="text-h3">
            {{ $t("record_of_review.title_submission") }}
          </h2>
          <dl>
            <dt>{{ $t("record_of_review.document_type.heading") }}</dt>
            <dd>{{ $t("record_of_review.document_type.journal_article") }}</dd>
            <dt>{{ $t("record_of_review.completed.heading") }}</dt>
            <dd>{{ completionDate }}</dd>
            <dt>{{ $t("record_of_review.identifier") }}</dt>
            <dd>{{ submission.id }}</dd>
          </dl>

          <h2 class="text-h3">
            {{ $t("record_of_review.title_publication") }}
          </h2>
          <dl>
            <dt>{{ $t("publication.entity", 1) }}</dt>
            <dd>{{ submission.publication.name }}</dd>
            <template
              v-for="editor in submission.publication.editors"
              :key="`editor-${editor.id}`"
            >
              <dt>{{ $t("publication.editor", 1) }}</dt>
              <dd>{{ editor.display_label }}</dd>
            </template>
            <template
              v-for="admin in submission.publication.publication_admins"
              :key="`admin-${admin.id}`"
            >
              <dt>{{ $t("publication.publication_admin", 1) }}</dt>
              <dd>{{ admin.display_label }}</dd>
            </template>
          </dl>
        </q-card-section>
      </div>
    </q-card>
  </article>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment recordOfReview on SubmissionAssignment {
    id
    role
    user {
      id
      display_label
      name
      email
      profile_metadata {
        academic_profiles {
          orcid_id
        }
      }
    }
    submission {
      id
      title
      audits {
        id
        created_at
        event
        old_values {
          content_id
          status
          status_change_comment
          title
        }
        new_values {
          content_id
          status
          status_change_comment
          title
        }
      }
      reviewers {
        ...recordOfReviewUser
      }
      review_coordinators {
        ...recordOfReviewUser
      }
      publication {
        id
        name
        editors {
          ...relatedUserFields
        }
        publication_admins {
          ...relatedUserFields
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import RecordOfReviewUser from "src/components/atoms/RecordOfReviewUser.vue"
import { post_review_states } from "src/utils/postReviewStates"
import type { recordOfReviewFragment } from "src/graphql/generated/graphql"
import { DateTime } from "luxon"
import { computed, ref, watch } from "vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const blobUrl = ref("")
const recordContainer = ref<HTMLElement | null>(null)

interface Props {
  assignment: recordOfReviewFragment
}

const props = defineProps<Props>()

const submission = computed(() => props.assignment.submission)

const orcidId = computed(
  () =>
    props.assignment.user.profile_metadata?.academic_profiles?.orcid_id ?? null
)

const completionDate = computed(() => {
  const audits = (submission.value.audits ?? []).filter(
    (audit): audit is NonNullable<typeof audit> => {
      const status = audit?.new_values?.status
      return typeof status === "string" && post_review_states.includes(status)
    }
  )
  if (audits.length === 0) {
    return t("record_of_review.completed.incomplete")
  }
  const last_audit = audits.reduce((a, b) =>
    a.created_at > b.created_at ? a : b
  )
  return DateTime.fromISO(last_audit.created_at).toFormat("yyyy-MM-dd")
})

function updateBlob() {
  const el = recordContainer.value
  if (!el) return
  const doc = new DOMParser().parseFromString(el.innerHTML, "text/html")
  doc.title = t("record_of_review.title_record", {
    title: submission.value.title
  })
  const html = doc.documentElement.outerHTML
  blobUrl.value = URL.createObjectURL(new Blob([html], { type: "text/html" }))
}

watch(recordContainer, () => updateBlob())
</script>
