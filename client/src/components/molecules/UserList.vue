<template>
  <q-list bordered separator :data-cy="dataCy">
    <user-list-item
      v-for="user in users"
      :key="user.id"
      :user="user"
      :actions="actions"
      @action-click="bubble"
    />
  </q-list>
</template>

<script>
import UserListItem from "../atoms/UserListItem.vue"
export default {
  name: "UserList",
  components: { UserListItem },
  props: {
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
  },
  emits: ["actionClick"],
  methods: {
    bubble(eventData) {
      this.$emit("actionClick", eventData)
    },
  },
}
</script>
