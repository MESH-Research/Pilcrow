<template>
  <div class="row q-col-gutter-lg q-pa-lg">
    <section class="col-md-5 col-sm-6 col-xs-12">
      <h3>REPLACEME</h3>
      <q-form @submit="assignUser(reviewer_candidate)">
        <div class="q-gutter-md column q-pl-none">
          <find-user-select v-model="user" data-cy="input_review_assignee" />
        </div>
        <q-btn
          :ripple="{ center: true }"
          class="q-mt-lg"
          color="primary"
          data-cy="button-assign"
          label="Assign"
          type="submit"
        />
      </q-form>
    </section>
    <section class="col-md-5 col-sm-6 col-xs-12">
      <h3>Users</h3>
      <div v-if="users.length">
        <user-list
          ref="userList"
          data-cy="user-list"
          :users="users"
          :actions="[
            {
              ariaLabel: 'Unassign',
              icon: 'person_remove',
              action: 'unassign',
              help: 'Remove Reviewer',
              cyAttr: 'button_unassign',
            },
          ]"
          @action-click="handleUserListClick"
        />
      </div>
      <div v-else>
        <q-card ref="card_no_users" bordered flat>
          <q-item class="text--grey">
            <q-item-section avatar>
              <q-icon name="o_do_disturb_on" />
            </q-item-section>
            <q-item-section>
              {{ $t("submissions.users.none") }}
            </q-item-section>
          </q-item>
        </q-card>
      </div>
    </section>
  </div>
</template>

<script setup>
import FindUserSelect from "./forms/FindUserSelect.vue"
import UserList from "./molecules/UserList.vue"
import { useFeedbackMessages } from "src/use/guiElements"
import { useMutation } from "@vue/apollo-composable"
import {
  UPDATE_SUBMISSION_REVIEWERS,
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
} from "src/graphql/mutations"
import { computed } from "vue"
import { useI18n } from "vue-i18n"
const props = defineProps({
  submission: {
    type: Object,
    required: true,
  },
  relationship: {
    type: String,
    required: true,
  },
})

const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "submission_details_notify",
  },
})

const opts = { variables: { submission_id: props.submission.id } }
const documents = {
  reviewers: UPDATE_SUBMISSION_REVIEWERS,
  review_coordinators: UPDATE_SUBMISSION_REVIEW_COORDINATORS,
}
const users = computed(() => {
  return props.submission[props.relationship]
})

const { mutate } = useMutation(documents[props.relationship], opts)
const { t } = useI18n()

async function handleUserListClick({ user }) {
  try {
    await mutate({ disconnect: [user.id] })
    newStatusMessage(
      "success",
      t(`submissions.${props.relationship}.unassign.success`, {
        display_name: user.name ? user.name : user.username,
      })
    )
  } catch (error) {
    newStatusMessage(
      "failure",
      t(`submissions.${props.relationship}.unassign.error`)
    )
  }
}
</script>

<style></style>
