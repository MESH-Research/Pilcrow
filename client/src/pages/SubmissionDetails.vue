<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.submissions', 2)"
          to="/submissions"
        />
        <q-breadcrumbs-el :label="$t('submissions.details_heading')" />
      </q-breadcrumbs>
    </nav>
    <h2 class="q-pl-lg">{{ submission.title }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-12">
        <q-btn
          data-cy="submission_review_btn"
          color="primary"
          size="lg"
          :label="$t('submissions.view_heading')"
          :to="{
            name: 'submission_review',
            params: { id: props.id },
          }"
        />
      </section>
      <section class="col-md-5 col-sm-12 col-xs-12">
        <h3>
          {{
            submitters.length > 1
              ? $t("submissions.submitter.title.plural")
              : $t("submissions.submitter.title.singular")
          }}
        </h3>
        <div v-if="submitters.length" class="q-gutter-md column q-pl-none">
          <user-list
            ref="list_assigned_submitters"
            data-cy="list_assigned_submitters"
            :users="submitters"
          />
        </div>
        <div v-else>
          <q-card ref="card_no_submitters" bordered flat>
            <q-item>
              <q-card-section avatar>
                <q-icon
                  color="negative"
                  text-color="white"
                  name="report_problem"
                  size="lg"
                />
              </q-card-section>
              <q-card-section>
                <p>
                  {{ $t("submissions.submitter.none") }}
                </p>
              </q-card-section>
            </q-item>
          </q-card>
        </div>
      </section>
    </div>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assign a Reviewer</h3>
        <q-form @submit="assignUser(`reviewer`, reviewer_candidate)">
          <div class="q-gutter-md column q-pl-none">
            <find-user-select
              id="input_review_assignee"
              v-model="reviewer_candidate"
              cy-selected-item="review_assignee_selected"
              cy-options-item="result_review_assignee"
            />
          </div>
          <q-btn
            :ripple="{ center: true }"
            class="q-mt-lg"
            color="primary"
            data-cy="button_assign_reviewer"
            label="Assign"
            type="submit"
          />
        </q-form>
      </section>
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assigned Reviewers</h3>
        <div v-if="reviewers.length">
          <user-list
            ref="list_assigned_reviewers"
            data-cy="list_assigned_reviewers"
            :users="reviewers"
            :actions="[
              {
                ariaLabel: 'Unassign',
                icon: 'person_remove',
                action: 'unassignReviewer',
                help: 'Remove Reviewer',
                cyAttr: 'button_unassign_reviewer',
              },
            ]"
            @action-click="handleUserListClick"
          />
        </div>
        <div v-else>
          <q-card ref="card_no_reviewers" bordered flat>
            <q-item class="text--grey">
              <q-item-section avatar>
                <q-icon name="o_do_disturb_on" />
              </q-item-section>
              <q-item-section>
                {{ $t("submissions.reviewer.none") }}
              </q-item-section>
            </q-item>
          </q-card>
        </div>
      </section>
    </div>

    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assign a Review Coordinator</h3>
        <q-form
          @submit="
            assignUser(`review_coordinator`, review_coordinator_candidate)
          "
        >
          <div class="q-gutter-md column q-pl-none">
            <find-user-select
              id="input_review_coordinator_assignee"
              v-model="review_coordinator_candidate"
              cy-selected-item="review_coordinator_assignee_selected"
              cy-options-item="result_review_coordinator_assignee"
            />
          </div>
          <q-btn
            :ripple="{ center: true }"
            class="q-mt-lg"
            color="primary"
            data-cy="button_assign_review_coordinator"
            label="Assign"
            type="submit"
          />
        </q-form>
      </section>
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Review Coordinator</h3>
        <div v-if="review_coordinators.length">
          <user-list
            ref="list_assigned_review_coordinators"
            data-cy="list_assigned_review_coordinators"
            :users="review_coordinators"
            :actions="[
              {
                ariaLabel: 'Unassign',
                icon: 'person_remove',
                action: 'unassignReviewCoordinator',
                help: 'Remove Review Coordinator',
                cyAttr: 'button_unassign_review_coordinator',
              },
            ]"
            @action-click="handleUserListClick"
          />
        </div>
        <div v-else>
          <q-card ref="card_no_review_coordinators" bordered flat>
            <q-item class="text--grey">
              <q-item-section avatar>
                <q-icon name="o_do_disturb_on" />
              </q-item-section>
              <q-item-section>
                {{ $t("submissions.review_coordinator.none") }}
              </q-item-section>
            </q-item>
          </q-card>
        </div>
      </section>
    </div>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import {
  CREATE_SUBMISSION_USER,
  DELETE_SUBMISSION_USER,
} from "src/graphql/mutations"
import UserList from "src/components/molecules/UserList.vue"
import RoleMapper from "src/mappers/roles"
import { useFeedbackMessages } from "src/use/guiElements"
import { useMutation, useQuery, useResult } from "@vue/apollo-composable"
import { ref, computed } from "vue"
import { useI18n } from "vue-i18n"
import FindUserSelect from "src/components/forms/FindUserSelect.vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const submission = useResult(useQuery(GET_SUBMISSION, { id: props.id }).result)

const reviewer_candidate = ref(null)
const review_coordinator_candidate = ref(null)

const review_coordinators = computed(() => {
  return filterUsersByRoleId(
    submission.value.users,
    RoleMapper[`review_coordinators`]
  )
})
const reviewers = computed(() => {
  return filterUsersByRoleId(submission.value.users, RoleMapper[`reviewers`])
})
const submitters = computed(() => {
  return filterUsersByRoleId(submission.value.users, RoleMapper[`submitters`])
})

function filterUsersByRoleId(users, id) {
  return users.filter((user) => {
    return parseInt(user.pivot.role_id) === id
  })
}

const { t } = useI18n()

const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "submission_details_notify",
  },
})

const { mutate: assignUserMutate } = useMutation(CREATE_SUBMISSION_USER, {
  refetchQueries: ["GetSubmission"],
})

async function assignUser(role_name, candidate_model) {
  try {
    await assignUserMutate({
      user_id: candidate_model.id,
      role_id: RoleMapper[role_name],
      submission_id: props.id,
    })
      .then(() => {
        newStatusMessage(
          "success",
          t(`submissions.${role_name}.assign.success`, {
            display_name: candidate_model.name
              ? candidate_model.name
              : candidate_model.username,
          })
        )
      })
      .then(() => {
        resetForm()
        candidate_model = null
      })
  } catch (error) {
    newStatusMessage("failure", t(`submissions.${role_name}.assign.error`))
  }
}
function resetForm() {
  review_coordinator_candidate.value = null
  reviewer_candidate.value = null
}

async function handleUserListClick({ user, action }) {
  switch (action) {
    case "unassignReviewer":
      await unassignUser("reviewer", user)
      break
    case "unassignReviewCoordinator":
      await unassignUser("review_coordinator", user)
      break
  }
}

const { mutate: unassignUserMutate } = useMutation(DELETE_SUBMISSION_USER, {
  refetchQueries: ["GetSubmission"],
})

async function unassignUser(role_name, user) {
  try {
    await unassignUserMutate({
      user_id: user.pivot.user_id,
      role_id: RoleMapper[role_name],
      submission_id: props.id,
    })
    newStatusMessage(
      "success",
      t(`submissions.${role_name}.unassign.success`, {
        display_name: user.name ? user.name : user.username,
      })
    )
  } catch (error) {
    newStatusMessage("failure", t(`submissions.${role_name}.unassign.error`))
  }
}
</script>
