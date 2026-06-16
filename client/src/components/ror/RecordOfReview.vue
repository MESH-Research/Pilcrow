<template>
  <article data-cy="record_of_review" class="q-mb-lg ror">
    <q-btn
      :label="$t('record_of_review.download_one')"
      icon="download"
      color="accent"
      :href="blobUrl"
      class="ror__download_btn"
      :download="`record_of_review_${submission.id}.html`"
    />
    <div ref="recordContainer" class="ror__document">
      <header class="ror__header">
        <p class="ror__eyebrow text-primary">
          {{ $t("record_of_review.eyebrow") }}
        </p>
        <h1 class="text-h2 q-mt-none ror__title" data-cy="page_heading">
          {{ submission.title }}
        </h1>
        <p class="ror__subtitle">
          {{ $t("record_of_review.subtitle") }}
        </p>
        <div class="ror__rule" aria-hidden="true" />
      </header>

      <section class="ror__section">
        <h2 class="text-h3 ror__section-title">
          {{ $t("record_of_review.title_participation") }}
        </h2>
        <dl class="ror__dl">
          <dt>{{ $t("user.name") }}</dt>
          <dd>
            {{ assignment.user.name || assignment.user.display_label }}
          </dd>
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
      </section>

      <section class="ror__section">
        <h2 class="text-h3 ror__section-title">
          {{ $t("record_of_review.title_team") }}
        </h2>
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
            :role="$t('admin.users.details.roles.review_coordinator')"
          />
          <record-of-review-user
            v-for="reviewer in submission.reviewers"
            :key="reviewer.id"
            :user="reviewer"
            :role="$t('admin.users.details.roles.reviewer')"
          />
        </div>
      </section>

      <section class="ror__section">
        <h2 class="text-h3 ror__section-title">
          {{ $t("record_of_review.title_submission") }}
        </h2>
        <dl class="ror__dl">
          <dt>{{ $t("record_of_review.document_type.heading") }}</dt>
          <dd>
            {{ $t("record_of_review.document_type.journal_article") }}
          </dd>
          <dt>{{ $t("record_of_review.completed.heading") }}</dt>
          <dd>{{ completionDate }}</dd>
          <dt>{{ $t("record_of_review.identifier") }}</dt>
          <dd>{{ submission.id }}</dd>
        </dl>
      </section>

      <section class="ror__section">
        <h2 class="text-h3 ror__section-title">
          {{ $t("record_of_review.title_publication") }}
        </h2>
        <dl class="ror__dl">
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
      </section>

      <footer class="ror__footer">
        <div class="ror__seal">
          <svg
            class="ror__seal-icon"
            viewBox="0 0 24 24"
            aria-hidden="true"
            focusable="false"
          >
            <path
              fill="currentColor"
              d="M23 12l-2.44-2.79.34-3.69-3.61-.82-1.89-3.2L12 2.96 8.6 1.5 6.71 4.69 3.1 5.5l.34 3.7L1 12l2.44 2.79-.34 3.7 3.61.82L8.6 22.5l3.4-1.47 3.4 1.46 1.89-3.19 3.61-.82-.34-3.69L23 12zm-12.91 4.72l-3.8-3.81 1.48-1.48 2.32 2.33 5.85-5.87 1.48 1.48-7.33 7.35z"
            />
          </svg>
          <img src="/logo/logo.svg" alt="Pilcrow" class="ror__seal-logo" />
        </div>
        <div class="ror__footer-meta">
          <i18n-t
            keypath="record_of_review.footer_certified"
            tag="p"
            class="ror__footer-line"
            scope="global"
          >
            <template #host>
              <a :href="siteUrl" class="ror__footer-link text-primary">{{ issuingHost }}</a>
            </template>
            <template #publication>
              <router-link
                :to="{
                  name: 'publication:home',
                  params: { id: submission.publication.id }
                }"
                class="ror__footer-link text-primary"
              >
                {{ submission.publication.name }}
              </router-link>
            </template>
          </i18n-t>
          <p class="ror__footer-detail">
            {{ $t("record_of_review.identifier") }}: {{ submission.id }}
            {{ separator }}
            {{ $t("record_of_review.completed.heading") }}:
            {{ completionDate }}
          </p>
        </div>
      </footer>
    </div>
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
        new_values {
          status
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
import RecordOfReviewUser from "src/components/ror/RecordOfReviewUser.vue"
import { post_review_states } from "src/utils/postReviewStates"
import {
  buildRorExportBlob,
  buildRorExportHtml
} from "src/utils/recordOfReviewExport"
import type { recordOfReviewFragment } from "src/graphql/generated/graphql"
import { DateTime } from "luxon"
import { computed, onBeforeUnmount, ref, watch } from "vue"
import { useI18n } from "vue-i18n"

const { t } = useI18n()

// Decorative middot separator. Kept as a constant so the raw glyph lives
// in script and is not flagged by @intlify/vue-i18n/no-raw-text.
const separator = "·"

const blobUrl = ref("")
const recordContainer = ref<HTMLElement | null>(null)

interface Props {
  assignment: recordOfReviewFragment
}

const props = defineProps<Props>()

const submission = computed(() => props.assignment.submission)

const issuingHost = window.location.host
const siteUrl = window.location.origin

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
  // Most recent transition into a post-review state. Compare parsed
  // timestamps rather than raw ISO strings so ordering is robust to any
  // timezone-offset or precision variation in `created_at`.
  const latest = audits.reduce((a, b) =>
    DateTime.fromISO(b.created_at) > DateTime.fromISO(a.created_at) ? b : a
  )
  const completed = DateTime.fromISO(latest.created_at)
  return completed.isValid
    ? completed.toFormat("yyyy-MM-dd")
    : t("record_of_review.completed.incomplete")
})

let pendingBlobUrl = ""
function setBlobUrl(next: string) {
  if (pendingBlobUrl) URL.revokeObjectURL(pendingBlobUrl)
  pendingBlobUrl = next
  blobUrl.value = next
}

async function updateBlob() {
  const el = recordContainer.value
  if (!el) return
  const html = await buildRorExportHtml(
    [el],
    t("record_of_review.title_record", { title: submission.value.title })
  )
  setBlobUrl(URL.createObjectURL(buildRorExportBlob(html)))
}

watch(recordContainer, () => {
  void updateBlob()
})

onBeforeUnmount(() => {
  if (pendingBlobUrl) URL.revokeObjectURL(pendingBlobUrl)
})

defineExpose({
  getRecordElement: () => recordContainer.value,
  submissionId: computed(() => submission.value.id)
})
</script>

<style lang="sass" scoped>
@import 'src/css/quasar.variables.sass'

.ror
  position: relative

.ror__document
  border: 1px solid $light-grey
  border-radius: 8px
  position: relative
  padding: 2.5rem 2rem 0 2rem

.ror__download_btn
  position: absolute
  left: 1rem
  top: 1rem
  z-index: 10

.ror__header
  text-align: center
  margin-bottom: 2rem

.ror__eyebrow
  font-size: 0.75rem
  font-weight: 600
  letter-spacing: 0.25em
  margin: 0 0 0.5rem
  text-transform: uppercase

.ror__title
  font-family: 'Georgia', 'Times New Roman', serif
  font-weight: 600
  margin-bottom: 0.5rem

.ror__subtitle
  font-style: italic
  margin: 0

.ror__rule
  margin: 1.25rem auto 0
  width: 60%
  height: 0
  border-top: 1px solid $primary
  border-bottom: 1px solid $primary
  padding-top: 4px
  position: relative

.ror__section
  margin-bottom: 1.75rem

.ror__section-title
  font-family: 'Georgia', 'Times New Roman', serif
  font-size: 1.25rem
  font-weight: 600
  letter-spacing: 0.05em
  text-transform: uppercase
  border-bottom: 1px solid $light-grey
  padding-bottom: 0.25rem
  margin-bottom: 0.75rem

.ror__dl
  display: grid
  grid-template-columns: max-content 1fr
  column-gap: 1.5rem
  row-gap: 0.4rem
  margin: 0

  dt
    font-weight: 600
    text-transform: uppercase
    font-size: 0.75rem
    letter-spacing: 0.08em
    align-self: center

  dd
    margin: 0

.ror__footer
  padding: 1.5rem 2rem
  display: flex
  align-items: center
  gap: 1rem
  margin-top: 2.5rem
  border-top: 2px double $light-grey

.ror__seal
  flex: 0 0 auto
  position: relative
  width: 112px
  height: 112px
  display: flex
  align-items: center
  justify-content: center

.ror__seal-icon
  position: absolute
  inset: 0
  width: 100%
  height: 100%
  color: $primary
  opacity: 0.85

.ror__seal-logo
  position: relative
  width: 78px
  height: 78px

.ror__footer-meta
  flex: 1 1 auto

.ror__footer-line
  font-style: italic
  margin: 0 0 0.25rem

.ror__footer-link
  text-decoration: underline
  font-style: normal

.ror__footer-detail
  font-size: 0.8rem
  margin: 0
</style>
