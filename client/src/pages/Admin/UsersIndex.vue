<template>
  <div>
    <h2 class="q-pl-lg">All Users</h2>
    <div v-if="users.length">
      <user-list-basic
        :users="users"
        action="goToUserDetail"
        @actionClick="handleUserListBasicClick"
      />

      <q-pagination
        v-model="currentPage"
        class="q-pa-lg flex flex-center"
        :max="lastPage"
      />
    </div>
  </div>
</template>

<script>
import { GET_USERS } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
import { ref } from "@vue/composition-api"
import UserListBasic from "src/components/molecules/UserListBasic"

export default {
  components: {
    UserListBasic,
  },
  setup(_, { root }) {
    const currentPage = ref(1)

    const { result } = useQuery(GET_USERS, { page: currentPage })
    const users = useResult(result, [], (data) => data.userSearch.data)
    const lastPage = useResult(
      result,
      1,
      (data) => data.userSearch.paginatorInfo.lastPage
    )
    async function handleUserListBasicClick({ user, action }) {
      switch (action) {
        case "goToUserDetail":
          this.goToUserDetail(user)
          break
      }
    }
    async function goToUserDetail(user) {
      const userId = user.id
      root.$router.push({
        name: "user_details",
        params: { id: userId },
      })
    }

    return {
      currentPage,
      users,
      lastPage,
      goToUserDetail,
      handleUserListBasicClick,
    }
  },
}
</script>
