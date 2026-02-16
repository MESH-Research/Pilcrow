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

<script setup lang="ts">
import UserListBasicItem from "../atoms/UserListBasicItem.vue"

interface Props {
  users: Record<string, unknown>[]
  action?: string
  dataCy?: string
}
withDefaults(defineProps<Props>(), {
  action: "",
  dataCy: "user_list"
})
const emit = defineEmits<{
  actionClick: [eventData: unknown]
}>()

function bubble(eventData: unknown) {
  emit("actionClick", eventData)
}
</script>
