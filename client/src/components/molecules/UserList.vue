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

<script setup lang="ts">
import type { UserListFragment } from "src/gql/graphql"
import type { Action } from "../atoms/UserListItem.vue"
import UserListItem from "../atoms/UserListItem.vue"
interface Props {
  users: UserListFragment[]
  actions?: Action[]
  dataCy?: string
}

const { actions = [], dataCy = "user_list", users } = defineProps<Props>()
const emit = defineEmits(["actionClick", "reinvite"])

function bubble(eventData) {
  emit("actionClick", eventData)
}
function reinviteUser(eventData) {
  emit("reinvite", eventData)
}
</script>

<script lang="ts">
graphql(`
  fragment UserList on User {
    ...UserListItem
  }
`)
</script>
