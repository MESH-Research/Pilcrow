<template>
  <article data-cy="vueDashboard" class="q-pa-lg">
    <q-banner
      v-if="showManageBanner"
      inline-actions
      dense
      class="q-mb-md bg-primary text-white"
      data-cy="manage_banner"
    >
      <template #avatar>
        <q-icon name="dashboard" size="md" />
      </template>
      {{ $t("dashboard.manage_banner.message") }}
      <template #action>
        <q-btn
          flat
          icon="arrow_forward"
          to="/manage"
          :label="$t('dashboard.manage_banner.cta')"
          data-cy="manage_banner_cta"
        />
        <q-btn
          flat
          dense
          icon="close"
          :aria-label="$t('dashboard.manage_banner.dismiss')"
          data-cy="manage_banner_dismiss"
          @click="dismissManageBanner"
        />
        <q-tooltip>{{ $t("dashboard.manage_banner.dismiss") }}</q-tooltip>
      </template>
    </q-banner>
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
                  label: currentUser.display_label
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
                  href="https://latest.docs.pilcrow.dev/guide/"
                  class="text-primary"
                  >{{ $t(`dashboard.guide`) }}</a
                ></template
              >
            </i18n-t>
          </q-card-section>
        </q-card>
      </div>
    </section>
    <NeedsActionPublicationsTable class="q-mb-md" :limit="3" />
    <section class="row wrap q-gutter-y-md">
      <div v-if="all_submissions.length > 0" class="col-12">
        <submission-table
          :table-data="all_submissions"
          table-type="submissions"
          variation="dashboard"
          :role="currentUser.highest_privileged_role"
          :data-cy="`${currentUser.highest_privileged_role}_table`"
        />
      </div>
      <div v-if="reviewer_submissions.length > 0" class="col-12">
        <submission-table
          :table-data="reviewer_submissions"
          table-type="submissions"
          variation="dashboard"
          role="reviewer"
          data-cy="reviews_table"
        />
      </div>
      <div v-if="coordinator_reviews.length > 0" class="col-12">
        <submission-table
          :table-data="coordinator_reviews"
          variation="dashboard"
          table-type="reviews"
          role="review_coordinator"
          data-cy="coordinator_table"
        />
      </div>
      <div v-if="submitter_submissions.length > 0" class="col-12">
        <submission-table
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

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Probe query: are there any publications where the current user is
// publication_admin or editor? We only need the total count; `first: 1`
// keeps the payload small.
graphql(`
  query DashboardManagedPublicationsProbe {
    currentUser {
      id
      publications(roles: [publication_admin, editor], first: 1, page: 1) {
        paginatorInfo {
          total
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import NeedsActionPublicationsTable from "src/components/molecules/NeedsActionPublicationsTable.vue"
import { useCurrentUser } from "src/use/user"
import { useQuery } from "@vue/apollo-composable"
import { CURRENT_USER_SUBMISSIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { DashboardManagedPublicationsProbeDocument } from "src/graphql/generated/graphql"
import SubmissionTable from "src/components/SubmissionTable.vue"
import { computed, ref } from "vue"
import { compareDatesDesc } from "src/utils/dateSort"
import { useQuasar } from "quasar"

const $q = useQuasar()

const { currentUser, isAppAdmin } = useCurrentUser()

// Skip the probe for app admins — they always see the banner.
const { result: managedProbe } = useQuery(
  DashboardManagedPublicationsProbeDocument,
  {},
  () => ({ enabled: !isAppAdmin.value })
)
const hasManagedPublication = computed(
  () =>
    (managedProbe.value?.currentUser?.publications?.paginatorInfo?.total ?? 0) >
    0
)

const MANAGE_BANNER_KEY = "hideManageBannerUntil"
const manageBannerDismissed = ref(false)
if ($q.localStorage.has(MANAGE_BANNER_KEY)) {
  const until = $q.localStorage.getItem(MANAGE_BANNER_KEY) as number
  if (until < Date.now()) {
    $q.localStorage.remove(MANAGE_BANNER_KEY)
  } else {
    manageBannerDismissed.value = true
  }
}
function dismissManageBanner() {
  manageBannerDismissed.value = true
  const oneWeekMs = 1000 * 60 * 60 * 24 * 7
  $q.localStorage.set(MANAGE_BANNER_KEY, Date.now() + oneWeekMs)
}
const showManageBanner = computed(
  () =>
    !manageBannerDismissed.value &&
    (isAppAdmin.value || hasManagedPublication.value)
)
const { result: all_submissions_result } = useQuery(GET_SUBMISSIONS, {
  page: 1
})
const all_submissions = computed(() => {
  return all_submissions_result.value?.submissions.data ?? []
})
const { result } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  const s = result.value?.currentUser?.submissions ?? []
  return [...s].sort((a, b) => compareDatesDesc(a.created_at, b.created_at))
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
