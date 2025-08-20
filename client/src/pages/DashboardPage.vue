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
                  label: currentUser.display_labels
                })
              }}
            </h2>
          </q-card-section>
          <q-separator />
          <q-card-actions
            :align="`${screen.width < 600 ? 'left' : 'evenly'}`"
            :class="`${screen.width < 600 ? 'q-pl-lg' : ''}`"
            :vertical="screen.width < 600 ? true : false"
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
            <span :class="`${screen.width < 1024 ? 'block' : ''}`"
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

<script setup lang="ts">
import { useQuasar } from "quasar"
import { useCurrentUser } from "src/use/user"
import { useQuery } from "@vue/apollo-composable"

import AvatarImage from "src/components/atoms/AvatarImage.vue"
import SubmissionTable from "src/components/SubmissionTable.vue"

import { computed } from "vue"

import {
  GetSubmissionsDocument,
  CurrentUserSubmissionsDocument
} from "src/gql/graphql"

const { screen } = useQuasar()

const { currentUser } = useCurrentUser()
const { result: all_submissions_result } = useQuery(GetSubmissionsDocument, {
  page: 1
})
const all_submissions = computed(() => {
  return all_submissions_result.value?.submissions.data ?? []
})
const { result } = useQuery(CurrentUserSubmissionsDocument)
const submissions = computed(() => {
  const s = result.value?.currentUser?.submissions ?? []
  return [...s].sort(
    (a, b) =>
      new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  )
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

<script lang="ts">
import { graphql } from "src/gql"

graphql(`
  query CurrentUserSubmissions {
    currentUser {
      id
      email2
      roles {
        name
      }
      submissions {
        id
        title
        status
        created_at
        submitted_at
        my_role
        effective_role
        review_coordinators {
          ...RelatedUserFields
        }
        reviewers {
          ...RelatedUserFields
        }
        submitters {
          ...RelatedUserFields
        }
        inline_comments(trashed: WITH) {
          id
          content
          created_by {
            id
            display_label
            email
          }
          updated_by {
            id
            display_label
            email
          }
          created_at
          updated_at
          style_criteria {
            id
            name
            icon
          }
          replies {
            id
            content
            created_by {
              id
              display_label
              email
            }
            updated_by {
              id
              display_label
              email
            }
            created_at
            updated_at
            read_at
          }
          read_at
        }
        overall_comments(trashed: WITH) {
          id
          content
          created_by {
            id
            display_label
            email
          }
          updated_by {
            id
            display_label
            email
          }
          created_at
          updated_at
          replies {
            id
            content
            created_by {
              id
              display_label
              email
            }
            updated_by {
              id
              display_label
              email
            }
            created_at
            updated_at
            read_at
          }
          read_at
        }
        publication {
          id
          name
          my_role
          editors {
            ...RelatedUserFields
          }
          publication_admins {
            ...RelatedUserFields
          }
        }
      }
    }
  }
`)

graphql(`
  fragment RelatedUserFields on User {
    id
    display_label
    username
    name
    email
    staged
  }
`)

graphql(`
  query GetSubmissions($page: Int) {
    submissions(page: $page) {
      paginatorInfo {
        ...PaginationFields
      }
      data {
        id
        title
        status
        my_role
        created_at
        submitted_at
        effective_role
        submitters {
          ...RelatedUserFields
        }
        reviewers {
          ...RelatedUserFields
        }
        review_coordinators {
          ...RelatedUserFields
        }
        publication {
          id
          name
          my_role
          editors {
            ...RelatedUserFields
          }
          publication_admins {
            ...RelatedUserFields
          }
        }
      }
    }
  }
`)
</script>

<style lang="scss" scoped>
.q-btn {
  &:deep(.q-icon) {
    margin-right: 0.5rem;
  }
}
</style>
