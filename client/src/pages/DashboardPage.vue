<template>
  <q-page data-cy="vueDashboard" class="q-pa-lg">
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
              class="q-mr-sm"
            />
            Welcome<span v-if="currentUser.name">, {{ currentUser.name }}</span>
          </q-card-section>
          <q-separator />
          <q-card-actions
            :align="`${$q.screen.width < 600 ? 'left' : 'evenly'}`"
            :class="`${$q.screen.width < 600 ? 'q-pl-lg' : ''}`"
            :vertical="$q.screen.width < 600 ? true : false"
          >
            <q-btn flat icon="o_settings" to="/account/profile"
              >Account Information</q-btn
            >
            <q-btn flat icon="o_contact_page" to="/account/metadata"
              >Profile Details</q-btn
            >
            <q-btn flat icon="mdi-logout" @click="logout">Logout</q-btn>
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
              >New to Pilcrow?</span
            >
            <span>
              Learn more in our
              <a href="https://docs.pilcrow.lndo.site" class="text-primary"
                >guide</a
              >.
            </span>
          </q-card-section>
        </q-card>
      </div>
    </section>
    <section class="row wrap q-gutter-y-md">
      <div class="col-12">
        <submission-table
          :table-data="reviewer_reviews"
          table-type="reviews"
          variation="dashboard"
          role="reviewer"
        />
      </div>
      <div class="col-12">
        <submission-table
          :table-data="coordinator_reviews"
          variation="dashboard"
          table-type="reviews"
          role="coordinator"
          class="col-12"
        />
      </div>
      <div class="col-12">
        <submission-table
          :table-data="submitter_submissions"
          variation="dashboard"
          table-type="submissions"
          role="submitter"
          class="col-12"
        />
      </div>
    </section>
  </q-page>
</template>

<script setup>
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useCurrentUser, useLogout } from "src/use/user"
import { useQuery } from "@vue/apollo-composable"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import SubmissionTable from "src/components/SubmissionTable.vue"
import { computed } from "vue"

const { currentUser } = useCurrentUser()
const { logoutUser: logout } = useLogout()
const { result } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  return result.value?.currentUser?.submissions ?? []
})
const reviewer_reviews = computed(() =>
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
    return (
      ["DRAFT", "INITIALLY_SUBMITTED", "AWAITING_REVIEW"].includes(
        submission.status
      ) === false && submission.my_role == "reviewer"
    )
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
