<template>
  <q-list :data-cy="dataCy" role="list">
    <user-list-basic-item
      v-for="(user, index) in users"
      :key="(user.id as string) ?? index"
      :user="user"
      :index="index"
      :action="action"
      data-cy="userListBasicItem"
      @action-click="bubble"
    />
  </q-list>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment userListBasic on User {
    id
    ...userListBasicItem
  }
`)
</script>

<script setup lang="ts">
import UserListBasicItem from "../atoms/UserListBasicItem.vue"
import type { userListBasicFragment } from "src/graphql/generated/graphql"

interface Props {
  users: userListBasicFragment[]
  action?: string
  dataCy?: string
}
withDefaults(defineProps<Props>(), {
  action: "",
  dataCy: "user_list"
})
interface Emits {
  actionClick: [eventData: unknown]
}

const emit = defineEmits<Emits>()

function bubble(eventData: unknown) {
  emit("actionClick", eventData)
}
</script>
