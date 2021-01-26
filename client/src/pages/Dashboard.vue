<template>
  <q-banner v-if="currentUser" inline-actions class="text-white bg-green">
    Welcome {{ currentUser.name }}, this will be the future location of your
    dashboard. Please wait....
    <template #avatar>
      <q-icon name="dashboard" color="white" />
    </template>
    <template #action>
      <q-btn @click="logout">Logout</q-btn>
    </template>
  </q-banner>
</template>

<script>
import gql from "graphql-tag";
import appAuth from "src/components/mixins/appAuth";

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
      query: gql`
        query currentUser {
          me {
            username
            id
            name
          }
        }
      `,
      update: data => data.me
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
