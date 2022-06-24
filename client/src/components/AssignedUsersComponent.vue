<template>
  <section class="column q-gutter-y-sm">
    <h3 class="q-my-none">{{ tp$("heading") }}</h3>
    <p v-if="te(tPrefix('description'))" class="q-mb-none q-mx-none">
      {{ tp$("description") }}
    </p>
    <div v-if="users.length">
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

    <q-form v-if="acceptMore" class="col" @submit="handleSubmit">
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
</template>

<script setup>
import FindUserSelect from "./forms/FindUserSelect.vue"
import UserList from "./molecules/UserList.vue"
import { useFeedbackMessages } from "src/use/guiElements"
import { useMutation } from "@vue/apollo-composable"
import {
  UPDATE_PUBLICATION_ADMINS,
  UPDATE_PUBLICATION_EDITORS,
  UPDATE_SUBMISSION_REVIEWERS,
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  UPDATE_SUBMISSION_SUBMITERS,
} from "src/graphql/mutations"
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"
const props = defineProps({
  container: {
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
  maxUsers: {
    type: [Boolean, Number],
    required: false,
    default: false,
  },
  containerType: {
    type: String,
    requred: false,
    default: null,
  },
})

const user = ref(null)
const containerType = computed(() => props.container.__typename.toLowerCase())
const { t, te } = useI18n()
const tPrefix = (key) => `${containerType.value}.${props.relationship}.${key}`
const tp$ = (key, ...args) => t(tPrefix(key), ...args)

const { newStatusMessage } = useFeedbackMessages()

const opts = { variables: { id: props.container.id } }
const documents = {
  submission: {
    reviewers: UPDATE_SUBMISSION_REVIEWERS,
    review_coordinators: UPDATE_SUBMISSION_REVIEW_COORDINATORS,
    submitters: UPDATE_SUBMISSION_SUBMITERS,
  },
  publication: {
    editors: UPDATE_PUBLICATION_EDITORS,
    publication_admins: UPDATE_PUBLICATION_ADMINS,
  },
}
const users = computed(() => {
  return props.container[props.relationship]
})

const acceptMore = computed(() => {
  return (
    props.mutable &&
    (props.maxUsers === false) | (users.value.length < props.maxUsers)
  )
})

const { mutate } = useMutation(
  documents[containerType.value][props.relationship],
  opts
)

async function handleSubmit() {
  if (!acceptMore.value) {
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
