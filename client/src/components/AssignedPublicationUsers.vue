<template>
  <section class="column q-gutter-y-sm">
    <h3 class="q-my-none">{{ pt("heading") }}</h3>
    <p v-if="pte('description')" class="q-mb-none q-mx-none">
      {{ pt("description") }}
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
                  ariaLabel: pt('unassign_button.ariaLabel'),
                  icon: 'person_remove',
                  action: 'unassign',
                  help: pt('unassign_button.help'),
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
            {{ pt("none") }}
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
import type { FoundUser } from "./forms/FindUserSelect.vue"
import UserList from "./molecules/UserList.vue"
import { useFeedbackMessages } from "src/use/guiElements"
import { useMutation } from "@vue/apollo-composable"
import {
  UPDATE_PUBLICATION_ADMINS,
  UPDATE_PUBLICATION_EDITORS
} from "src/graphql/mutations"
import { computed, ref } from "vue"
import type { DocumentNode } from "graphql"
import { useI18nPrefix } from "src/use/i18nPrefix"
const props = withDefaults(
  defineProps<{
    container: Record<string, any>
    roleGroup: string
    mutable?: boolean
    maxUsers?: boolean | number
    containerType?: string | null
  }>(),
  {
    mutable: false,
    maxUsers: false,
    containerType: null
  }
)

const user = ref<FoundUser | null>(null)
const containerType = computed(() => props.container.__typename.toLowerCase())
const { pt, pte } = useI18nPrefix(
  () => `${containerType.value}.${props.roleGroup}`
)

const { newStatusMessage } = useFeedbackMessages()

interface PublicationMutationVars {
  id?: string
  connect?: string[]
  disconnect?: string[]
}

const mutations: Record<string, DocumentNode> = {
  editors: UPDATE_PUBLICATION_EDITORS,
  publication_admins: UPDATE_PUBLICATION_ADMINS
}
const users = computed(() => {
  return props.container[props.roleGroup]
})

const acceptMore = computed(() => {
  return (
    props.mutable &&
    (props.maxUsers === false || users.value.length < props.maxUsers)
  )
})

const { mutate } = useMutation<unknown, PublicationMutationVars>(
  mutations[props.roleGroup],
  { variables: { id: props.container.id } }
)

async function handleSubmit() {
  if (!acceptMore.value || !user.value) {
    return
  }

  const selectedUser = user.value
  try {
    await mutate({
      connect: [selectedUser.id]
    })
      .then(() => {
        newStatusMessage(
          "success",
          pt("assign.success", {
            display_name: selectedUser.name ?? selectedUser.username
          })
        )
      })
      .then(() => {
        user.value = null
      })
  } catch (error) {
    newStatusMessage("failure", pt("assign.error"))
  }
}

async function handleUserListClick({ user }: { user: FoundUser }) {
  if (!props.mutable) return
  try {
    await mutate({ disconnect: [user.id] })
    newStatusMessage(
      "success",
      pt("unassign.success", {
        display_name: user.name ? user.name : user.username
      })
    )
  } catch (error) {
    newStatusMessage("failure", pt("unassign.error"))
  }
}
</script>

<style></style>
