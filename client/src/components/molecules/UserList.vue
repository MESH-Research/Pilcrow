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
import UserListItem from "../atoms/UserListItem.vue"
import type { User } from "src/graphql/generated/graphql"
import type { UserAction } from "../atoms/UserListItem.vue"

interface Props {
  users: User[]
  actions?: UserAction[]
  dataCy?: string
}

withDefaults(defineProps<Props>(), {
  actions: () => [],
  dataCy: "user_list"
})
interface Emits {
  actionClick: [payload: { user: User; action: string }]
  reinvite: [payload: { user: User }]
}
const emit = defineEmits<Emits>()

function bubble(eventData: { user: User; action: string }) {
  emit("actionClick", eventData)
}
function reinviteUser(eventData: { user: User }) {
  emit("reinvite", eventData)
}
</script>
