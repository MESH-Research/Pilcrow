<template>
  <q-list :data-cy="dataCy" role="list">
    <user-list-basic-item
      v-for="(user, index) in users"
      :key="user.id"
      :user="user"
      :index="index"
      :action="action"
      data-cy="userListBasicItem"
      @action-click="bubble"
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
  emits: ["actionClick"],
  methods: {
    bubble(eventData) {
      this.$emit("actionClick", eventData)
    },
  },
}
</script>
