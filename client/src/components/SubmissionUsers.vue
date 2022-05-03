<template>
  <div class="q-pa-lg">
    <section class="column q-gutter-sm">
      <h3>{{ tp$("heading") }}</h3>
      <div v-if="users.length" class="col">
        <user-list
          ref="userList"
          data-cy="user-list"
          :users="users"
          :actions="
            mutable
              ? [
                  {
                    ariaLabel: tp$('unassign_button.ariaLabel'),
                    icon: 'person_remove',
                    action: 'unassign',
                    help: tp$('unassign_button.help'),
                    cyAttr: 'button_unassign',
                  },
                ]
              : []
          "
          @action-click="handleUserListClick"
        />
      </div>
      <div v-else class="col">
        <q-card ref="card_no_users" bordered flat>
          <q-item class="text--grey">
            <q-item-section avatar>
              <q-icon name="o_do_disturb_on" />
            </q-item-section>
            <q-item-section>
              {{ tp$("none") }}
            </q-item-section>
          </q-item>
        </q-card>
      </div>

      <q-form v-if="mutable" class="col" @submit="handleSubmit">
        <find-user-select v-model="user" data-cy="input_user">
          <template #after>
            <q-btn
              :ripple="{ center: true }"
              color="primary"
              data-cy="button-assign"
              label="Assign"
              type="submit"
              stretch
              @click="handleSubmit"
            />
          </template>
        </find-user-select>
      </q-form>
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
  UPDATE_SUBMISSION_SUBMITERS,
} from "src/graphql/mutations"
import { computed, ref } from "vue"
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
  mutable: {
    type: Boolean,
    default: false,
  },
})

const user = ref(null)

const { t } = useI18n()
const tPrefix = (key) => `submissions.${props.relationship}.${key}`
const tp$ = (key, ...args) => t(tPrefix(key), ...args)

const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "submission_details_notify",
  },
})

const opts = { variables: { submission_id: props.submission.id } }
const documents = {
  reviewers: UPDATE_SUBMISSION_REVIEWERS,
  review_coordinators: UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  submitters: UPDATE_SUBMISSION_SUBMITERS,
}
const users = computed(() => {
  return props.submission[props.relationship]
})

const { mutate } = useMutation(documents[props.relationship], opts)

async function handleSubmit() {
  if (!props.mutable) {
    console.log("not muatble")
    return
  }

  try {
    await mutate({
      connect: [user.value.id],
    })
      .then(() => {
        newStatusMessage(
          "success",
          tp$("assign.success", {
            display_name: user.value.name ?? user.value.username,
          })
        )
      })
      .then(() => {
        user.value = null
      })
  } catch (error) {
    newStatusMessage("failure", tp$("assign.error"))
  }
}

async function handleUserListClick({ user }) {
  if (!props.mutable) return
  try {
    await mutate({ disconnect: [user.id] })
    newStatusMessage(
      "success",
      tp$("unassign.success", {
        display_name: user.name ? user.name : user.username,
      })
    )
  } catch (error) {
    newStatusMessage("failure", tp$("unassign.error"))
  }
}
</script>

<style></style>
