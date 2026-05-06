<template>
  <q-timeline-entry
    :icon="entryIcon"
    :color="entryColor"
    :subtitle="`${absoluteDate} · ${relativeTime}`"
  >
    <template #title>
      <i18n-t
        :keypath="
          audit.user
            ? `submission.activity_record.description_no_time`
            : `submission.activity_record.description_no_user_no_time`
        "
        tag="span"
        scope="global"
      >
        <template #object>
          <template v-if="audit.old_values?.title != null">
            {{ $t(`submission.activity_record.object.title`) }}
          </template>
          <template v-if="audit.old_values?.status != null">
            {{ $t(`submission.activity_record.object.status`) }}
          </template>
          <template v-if="audit.new_values?.content_id != null">
            {{ $t(`submission.activity_record.object.content`) }}
          </template>
          <template v-if="audit.event == 'created'">
            {{ $t(`submission.activity_record.object.submission`) }}
          </template>
        </template>

        <template #event>
          {{ $t(`submission.activity_record.events.${audit.event}`) }}
        </template>

        <template #user>
          <router-link
            v-if="audit.user"
            :to="{
              name: 'user_details',
              params: { id: audit.user.id }
            }"
          >
            {{ audit.user.name || audit.user.username }}
          </router-link>
        </template>
      </i18n-t>
    </template>

    <i18n-t
      v-if="audit.event == 'updated' && audit.old_values?.title != null"
      keypath="submission.activity_record.title_change"
      tag="div"
      scope="global"
    >
      <template #previous_title>{{ audit.old_values.title }}</template>
      <template #current_title>
        <b>{{ audit.new_values.title }}</b>
      </template>
    </i18n-t>

    <i18n-t
      v-if="audit.event == 'updated' && audit.old_values?.status != null"
      keypath="submission.activity_record.status_change"
      tag="div"
      scope="global"
    >
      <template #previous_status>
        {{ $t(`submission.status.${audit.old_values.status}`) }}
      </template>
      <template #current_status>
        <b>{{ $t(`submission.status.${audit.new_values.status}`) }}</b>
      </template>
    </i18n-t>

    <p
      v-if="audit.new_values.status_change_comment != null"
      class="q-mt-xs q-mb-none"
    >
      {{ $t("submission.activity_record.comment_title") }}:
      {{ audit.new_values.status_change_comment }}
    </p>
  </q-timeline-entry>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Co-located fragment: every field this component reads off an audit
// entry. Pages that render <manage-submission-audit> spread this
// fragment into their query so the field set stays next to the code
// that consumes it (and codegen tells us when something drifts).
graphql(`
  fragment ManageSubmissionAuditFields on SubmissionAudit {
    id
    event
    created_at
    user {
      id
      name
      username
      email
    }
    old_values {
      title
      status
      status_change_comment
      content_id
    }
    new_values {
      title
      status
      status_change_comment
      content_id
    }
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import { DateTime } from "luxon"
import type { ManageSubmissionAuditFieldsFragment } from "src/graphql/generated/graphql"
import { useTimeAgo } from "src/use/timeAgo"

interface Props {
  audit: ManageSubmissionAuditFieldsFragment
}

const props = defineProps<Props>()
const timeAgo = useTimeAgo()

const createdDate = computed(() => DateTime.fromISO(props.audit.created_at))

// Long-form absolute timestamp: e.g. "Apr 27, 2026 · 3:42 PM".
const absoluteDate = computed(() =>
  createdDate.value.isValid
    ? createdDate.value.toFormat("LLL d, yyyy · h:mm a")
    : ""
)

const relativeTime = computed(() =>
  createdDate.value.isValid
    ? timeAgo.format(createdDate.value.toJSDate(), "long")
    : ""
)

// Per-event dot icon + color so the timeline reads at a glance:
// status changes are the eye-catching ones (the workflow signal),
// title/content edits are quieter, "created" anchors the bottom
// of the trail.
const entryIcon = computed(() => {
  if (props.audit.event === "created") return "playlist_add"
  if (props.audit.event === "contentUpload") return "upload_file"
  if (props.audit.old_values?.status != null) return "swap_horiz"
  if (props.audit.old_values?.title != null) return "edit"
  return "edit"
})

const entryColor = computed(() => {
  if (props.audit.event === "created") return "positive"
  if (props.audit.old_values?.status != null) return "accent"
  return "primary"
})
</script>

<style scoped>
/* Quasar's q-timeline ships defaults aimed at marketing-style
   timelines: a tiny uppercase subtitle and a relatively small
   title. For an activity feed we want the description sentence to
   read like body copy and the timestamp to read like a caption,
   so the three slots get explicit sizing. `:deep` is needed
   because q-timeline-entry renders Quasar's own classes inside
   this component's slot tree. */

/* Description sentence: the primary thing to read. Match the
   body-copy size so it sits comfortably in the rail. */
:deep(.q-timeline__title) {
  font-size: 1rem;
  font-weight: 500;
  line-height: 1.4;
  margin-bottom: 4px;
}

/* Subtitle = the timestamp readout. Smaller, no uppercase, mono-
   width digits so dates don't shimmy as the relative time updates. */
:deep(.q-timeline__subtitle) {
  font-size: 0.8125rem;
  text-transform: none;
  letter-spacing: 0;
  font-variant-numeric: tabular-nums;
  opacity: 0.75;
  margin-bottom: 2px;
}

/* Body content (title diff, status diff, comment). Slightly smaller
   than the title so the change details read as a follow-on aside. */
:deep(.q-timeline__content) {
  font-size: 0.9375rem;
  line-height: 1.45;
}
:deep(.q-timeline__content) p {
  margin: 0;
}
</style>
