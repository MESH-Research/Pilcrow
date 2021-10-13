<template>
  <q-list bordered separator :data-cy="dataCy">
    <user-list-item
      v-for="(user, index) in users"
      :key="user.pivot.id"
      :user="user"
      :index="index"
      :actions="actions"
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
}
</script>
