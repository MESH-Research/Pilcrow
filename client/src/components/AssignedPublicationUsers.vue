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
                  cyAttr: 'button_unassign'
                }
              ]
            : []
        "
        @action-click="handleUserListClick"
      />
    </div>
    <div v-else class="col">
      <q-card ref="card_no_users" class="text--grey" bordered flat>
        <q-card-section horizontal>
          <q-card-section>
            <q-icon color="accent" name="o_do_disturb_on" size="sm" />
          </q-card-section>
          <q-card-section>
            {{ tp$("none") }}
          </q-card-section>
        </q-card-section>
      </q-card>
    </div>

    <q-form v-if="acceptMore" class="col" @submit="handleSubmit">
      <find-user-select v-model="user" data-cy="input_user">
        <template #after>
          <q-btn
            :ripple="{ center: true }"
            color="accent"
            data-cy="button-assign"
            :label="$t(`publication.setup_pages.assign`)"
            type="submit"
            stretch
            @click="handleSubmit"
          />
        </template>
      </find-user-select>
    </q-form>
  </section>
</template>

<script setup lang="ts">
import FindUserSelect from "./forms/FindUserSelect.vue"
import UserList from "./molecules/UserList.vue"
import { useFeedbackMessages } from "src/use/guiElements"
import { useMutation } from "@vue/apollo-composable"
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"

import {
  type GetPublicationQuery,
  UpdatePublicationAdminsDocument,
  UpdatePublicationEditorsDocument
} from "src/gql/graphql"

interface Props {
  container: GetPublicationQuery["publication"]
  roleGroup: "publication_admins" | "editors"
  mutable?: boolean
  maxUsers?: number | false
}

const props = withDefaults(defineProps<Props>(), {
  mutable: false,
  maxUsers: false
})

const user = ref(null)

const { t, te } = useI18n()

const tPrefix = (key) => `publication.${props.roleGroup}.${key}`
const tp$ = (key, ...args) => t(tPrefix(key), args)

const { newStatusMessage } = useFeedbackMessages()

const opts = { variables: { id: props.container.id } }

const users = computed(() => {
  return props.container[props.roleGroup]
})

const acceptMore = computed(() => {
  const maxUsers = props.maxUsers === false ? Infinity : props.maxUsers
  return props.mutable && users.value.length < maxUsers
})

const { mutate: editorsMutate } = useMutation(
  UpdatePublicationEditorsDocument,
  opts
)
const { mutate: adminsMutate } = useMutation(
  UpdatePublicationAdminsDocument,
  opts
)

async function handleSubmit() {
  if (!acceptMore.value) {
    return
  }

  try {
    let mutate
    if (props.roleGroup === "editors") {
      mutate = editorsMutate
    } else if (props.roleGroup === "publication_admins") {
      mutate = adminsMutate
    }
    await mutate({
      connect: [user.value.id]
    })
      .then(() => {
        newStatusMessage(
          "success",
          tp$("assign.success", {
            display_name: user.value.name ?? user.value.username
          })
        )
      })
      .then(() => {
        user.value = null
      })
  } catch {
    newStatusMessage("failure", tp$("assign.error"))
  }
}

async function handleUserListClick({ user }) {
  if (!props.mutable) return
  let mutate
  if (props.roleGroup === "editors") {
    mutate = editorsMutate
  } else if (props.roleGroup === "publication_admins") {
    mutate = adminsMutate
  }
  try {
    await mutate({ disconnect: [user.id] })
    newStatusMessage(
      "success",
      tp$("unassign.success", {
        display_name: user.name ? user.name : user.username
      })
    )
  } catch {
    newStatusMessage("failure", tp$("unassign.error"))
  }
}
</script>

<script lang="ts">
import { graphql } from "src/gql"
graphql(`
  mutation UpdatePublicationEditors(
    $id: ID!
    $connect: [ID!]
    $disconnect: [ID!]
  ) {
    updatePublication(
      publication: {
        id: $id
        editors: { connect: $connect, disconnect: $disconnect }
      }
    ) {
      id
      editors {
        ...RelatedUserFields
      }
    }
  }
`)

graphql(`
  mutation UpdatePublicationAdmins(
    $id: ID!
    $connect: [ID!]
    $disconnect: [ID!]
  ) {
    updatePublication(
      publication: {
        id: $id
        publication_admins: { connect: $connect, disconnect: $disconnect }
      }
    ) {
      id
      publication_admins {
        ...RelatedUserFields
      }
    }
  }
`)
</script>

<style></style>
