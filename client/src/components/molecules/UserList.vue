<template>
  <q-list :data-cy="dataCy" role="list">
    <user-list-item
      v-for="user in users"
      :key="user.id"
      :user="user"
      :actions="actions"
      @action-click="bubble"
      @reinvite="reinviteUser"
    />
  </q-list>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment userList on User {
    id
    ...userListItem
  }
`)
</script>

<script setup lang="ts">
import UserListItem from "../atoms/UserListItem.vue"
import type { userListFragment, userListItemFragment } from "src/graphql/generated/graphql"
import type { UserAction } from "../atoms/UserListItem.vue"

interface Props {
  users: userListFragment[]
  actions?: UserAction[]
  dataCy?: string
}

withDefaults(defineProps<Props>(), {
  actions: () => [],
  dataCy: "user_list"
})
interface Emits {
  actionClick: [payload: { user: userListItemFragment; action: string }]
  reinvite: [payload: { user: userListItemFragment }]
}
const emit = defineEmits<Emits>()

function bubble(eventData: { user: userListItemFragment; action: string }) {
  emit("actionClick", eventData)
}
function reinviteUser(eventData: { user: userListItemFragment }) {
  emit("reinvite", eventData)
}
</script>
