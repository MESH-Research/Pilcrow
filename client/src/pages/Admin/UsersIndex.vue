<template>
  <q-banner v-if="users.data">
    <div
      v-for="user in users.data"
      :key="user.id"
    >
      {{ user.name }}
    </div>
  </q-banner>
</template>

<script>
import { GET_USERS } from "src/graphql/queries";

export default {
  data() {
    return {
      users: {
        data: null
      }
    }
  },
  apollo: {
    users: {
      query: GET_USERS,
      variables: {
        page:1
      }
    }
  },
}
</script>
