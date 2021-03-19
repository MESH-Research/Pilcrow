<template>
  <div>
    <q-banner
      v-if="currentUser"
      inline-actions
      class="text-white bg-positive"
    >
      Welcome<span v-if="currentUser.name">, {{ currentUser.name }}</span>. You are logged in.
      <template #avatar>
        <q-icon
          name="dashboard"
          color="white"
        />
      </template>
      <template #action>
        <q-btn @click="logout">
          Logout
        </q-btn>
      </template>
    </q-banner>
    <h2 class="q-pl-lg">
      My Dashboard
    </h2>
  </div>
</template>

<script>
import gql from "graphql-tag";
import appAuth from "src/components/mixins/appAuth";
import { CURRENT_USER } from "src/graphql/queries";
export default {
  name: "DashboardPage",
  mixins: [appAuth],
  data() {
    return {
      currentUser: null
    };
  },
  apollo: {
    currentUser: {
      query: CURRENT_USER
    }
  },
  methods: {
    async logout() {
      const { success } = await this.$logout();
      if (success) {
        this.$router.push("/");
      }
    }
  }
};
</script>
