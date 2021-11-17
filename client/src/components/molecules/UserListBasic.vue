<template>
  <q-list bordered separator :data-cy="dataCy">
    <user-list-basic-item
      v-for="(user, index) in users"
      :key="user.id"
      :user="user"
      :index="index"
      :action="action"
      data-cy="userListBasicItem"
      @actionClick="bubble"
    />
  </q-list>
</template>

<script>
import UserListBasicItem from "../atoms/UserListBasicItem.vue"
export default {
  name: "UserListBasic",
  components: { UserListBasicItem },
  props: {
    users: {
      type: Array,
      required: true,
    },
    action: {
      type: String,
      required: false,
      default: "",
    },
    dataCy: {
      type: String,
      default: "user_list",
    },
  },
  methods: {
    bubble(eventData) {
      this.$emit("actionClick", eventData)
    },
  },
}
</script>
