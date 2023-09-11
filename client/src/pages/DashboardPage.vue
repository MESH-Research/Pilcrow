<template>
  <article data-cy="vueDashboard" class="q-pa-lg">
    <section
      class="row wrap justify-start q-col-gutter-md items-stretch content-start q-mb-md"
    >
      <div class="col-md-6 col-xs-12">
        <q-card v-if="currentUser" flat bordered square class="full-height">
          <q-card-section class="text-h4">
            <avatar-image
              :user="currentUser"
              rounded
              size="md"
              class="q-mr-md"
            />
            <h2 class="text-h4" style="display: inline">
              {{
                $t(`dashboard.welcome_message`, {
                  label: currentUser.display_label,
                })
              }}
            </h2>
          </q-card-section>
          <q-separator />
          <q-card-actions
            :align="`${$q.screen.width < 600 ? 'left' : 'evenly'}`"
            :class="`${$q.screen.width < 600 ? 'q-pl-lg' : ''}`"
            :vertical="$q.screen.width < 600 ? true : false"
          >
            <q-btn flat icon="account_circle" to="/account/profile">{{
              $t(`profile.page_title`)
            }}</q-btn>
            <q-btn flat icon="o_settings" to="/account/settings">{{
              $t(`settings.page_title`)
            }}</q-btn>
            <q-btn flat icon="mdi-logout" to="/logout">{{
              $t(`auth.logout`)
            }}</q-btn>
          </q-card-actions>
        </q-card>
      </div>
      <div class="col-md-6 col-xs-12">
        <q-card
          flat
          bordered
          square
          class="flex justify-center items-center full-height q-pa-md text-center"
        >
          <q-card-section class="text-h3">
            <span :class="`${$q.screen.width < 1024 ? 'block' : ''}`"
              >{{ $t(`dashboard.guide_question`) }} </span
            >&nbsp;<i18n-t
              keypath="dashboard.guide_call_to_action"
              tag="span"
              scope="global"
            >
              {{ $t(`dashboard.guide_call_to_action`) }}
              <template #link>
                <a
                  href="https://pilcrow.meshresearch.dev/guide/"
                  class="text-primary"
                  >{{ $t(`dashboard.guide`) }}</a
                ></template
              >
            </i18n-t>
          </q-card-section>
        </q-card>
      </div>
    </section>
    <section class="row wrap q-gutter-y-md">
      <div class="col-12">
        <submission-table
          v-if="reviewer_submissions.length > 0"
          :table-data="reviewer_submissions"
          table-type="submissions"
          variation="dashboard"
          role="reviewer"
          data-cy="reviews_table"
        />
      </div>
      <div class="col-12">
        <submission-table
          v-if="coordinator_reviews.length > 0"
          :table-data="coordinator_reviews"
          variation="dashboard"
          table-type="reviews"
          role="coordinator"
          data-cy="coordinator_table"
        />
      </div>
      <div class="col-12">
        <submission-table
          v-if="submitter_submissions.length > 0"
          :table-data="submitter_submissions"
          variation="dashboard"
          table-type="submissions"
          role="submitter"
          data-cy="submissions_table"
        />
      </div>
    </section>
  </article>
</template>

<script setup>
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useCurrentUser } from "src/use/user"
import { useQuery } from "@vue/apollo-composable"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import SubmissionTable from "src/components/SubmissionTable.vue"
import { computed } from "vue"

const { currentUser } = useCurrentUser()
const { result } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  let s = result.value?.currentUser?.submissions ?? []
  return [...s].sort((a, b) => {
    return new Date(b.created_at) - new Date(a.created_at)
  })
})
const reviewer_submissions = computed(() =>
  submissions.value.filter(function (submission) {
    return (
      ["DRAFT", "INITIALLY_SUBMITTED", "AWAITING_REVIEW"].includes(
        submission.status
      ) === false && submission.my_role == "reviewer"
    )
  })
)
const coordinator_reviews = computed(() =>
  submissions.value.filter(function (submission) {
    return (
      submission.status != "DRAFT" && submission.my_role == "review_coordinator"
    )
  })
)
const submitter_submissions = computed(() =>
  submissions.value.filter(function (submission) {
    return submission.my_role == "submitter"
  })
)
</script>

<style lang="scss" scoped>
.q-btn {
  &::v-deep {
    .q-icon {
      margin-right: 0.5rem;
    }
  }
}
</style>
