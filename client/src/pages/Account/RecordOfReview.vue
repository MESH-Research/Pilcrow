<template>
  <article data-cy="record_of_review" class="q-mb-lg">
    <q-card bordered class="q-pa-lg">
      <h1 class="text-h2" data-cy="page_heading">
        Record of Review for {{ review.title }}
      </h1>
      <h2 class="text-h3">Reviewer Information</h2>
      <div class="row items-start q-gutter-md items-stretch">
        <q-card
          v-for="coordinator in review.review_coordinators"
          :key="coordinator.id"
          bordered
          style="width: 250px"
        >
          <q-card-section>
            <dl>
              <dt><span>Name</span></dt>
              <dd>
                <span>{{ coordinator.display_label }}</span>
              </dd>
              <template v-if="coordinator.profile_metadata?.academic_profiles">
                <dt
                  v-if="
                    coordinator.profile_metadata?.academic_profiles?.orcid_id
                  "
                >
                  <span>ORCID iD</span>
                </dt>
                <dd
                  v-if="
                    coordinator.profile_metadata?.academic_profiles?.orcid_id
                  "
                >
                  <span>
                    {{
                      coordinator.profile_metadata?.academic_profiles.orcid_id
                    }}
                  </span>
                </dd>
              </template>
              <dt>
                <span>Role</span>
              </dt>
              <dd><span>Review Coordinator</span></dd>
            </dl>
          </q-card-section>
        </q-card>
        <q-card
          v-for="reviewer in review.reviewers"
          :key="reviewer.id"
          bordered
          style="width: 250px"
        >
          <q-card-section>
            <dl>
              <dt>Name</dt>
              <dd>{{ reviewer.display_label }}</dd>
              <dt v-if="reviewer.profile_metadata?.academic_profiles">
                <span>ORCID iD</span>
              </dt>
              <dd v-if="reviewer.profile_metadata?.academic_profiles">
                <span>
                  {{ reviewer.profile_metadata?.academic_profiles.orcid_id }}
                </span>
              </dd>
              <dt>
                <span>Role</span>
              </dt>
              <dd><span>Reviewer</span></dd>
            </dl>
          </q-card-section>
        </q-card>
      </div>
      <h3>Review Information</h3>
      <dl>
        <dt>Publication</dt>
        <dd>{{ review.publication.name }}</dd>
        <template v-for="editor in review.publication.editors" :key="editor.id">
          <dt>Editor</dt>
          <dd>{{ editor.display_label }}</dd>
        </template>
        <dt>Type</dt>
        <dd>Journal Article (Make configurable?)</dd>
        <dt>Date Completed</dt>
        <dd>{{ getCompletionDate(review) }}</dd>
        <dt>Review Identifier</dt>
        <dd>{{ review.id }}</dd>
      </dl>
    </q-card>
  </article>
</template>

<script setup lang="ts">
import type { Submission } from "src/graphql/generated/graphql"

function getCompletionDate(review: Submission) {
  const audits = [...review.audits]
  audits.filter(function (audit) {
    return ["ACCEPTED_AS_FINAL", "RESUBMISSION_REQUESTED", "REJECTED"].includes(
      audit.new_values.status
    )
  })
  if (audits && audits.length > 0) {
    audits.sort((a, b) => {
      return a.created_at - b.created_at
    })
  }
  const last_audit = audits.pop()
  return last_audit.created_at
}

interface Props {
  review: Submission
}

defineProps<Props>()
</script>
