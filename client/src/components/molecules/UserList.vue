<template>
  <q-list :data-cy="dataCy" role="list">
    <user-list-item
      v-for="user in users"
      :key="user.id"
      :user="user"
      :actions="actions"
      @action-click="bubble"
    />
  </q-list>
</template>

<script setup>
import UserListItem from "../atoms/UserListItem.vue"
defineProps({
  users: {
    type: Array,
    required: true,
  },
  actions: {
    type: Array,
    required: false,
    default: () => [],
  },
  dataCy: {
    type: String,
    default: "user_list",
  },
})
const emit = defineEmits(["actionClick"])

function bubble(eventData) {
  emit("actionClick", eventData)
}
</script>
