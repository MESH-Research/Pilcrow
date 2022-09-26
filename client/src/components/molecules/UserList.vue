<template>
  <q-list separator :data-cy="dataCy">
    <user-list-item
      v-for="user in users"
      :key="user.id"
      :user="user"
      :actions="actions"
      @action-click="bubble"
    />
    <q-separator v-if="persistentSeparator" />
  </q-list>
</template>

<script setup>
import UserListItem from "../atoms/UserListItem.vue"
defineProps({
  users: {
    type: Array,
    required: true,
  },
  persistentSeparator: {
    type: Boolean,
    default: false,
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
