<template>
    <h2 class="q-pl-lg">All Users</h2>
    <div v-if="users.length">
      <user-list-basic
        ref="user_list_basic"
        :users="users"
        action="goToUserDetail"
        @action-click="handleUserListBasicClick"
      />

      <q-pagination
        v-model="currentPage"
        data-cy="user_list_pagination"
        class="q-pa-lg flex flex-center"
        :max="lastPage"
      />
    </div>
</template>

<script setup>
import { useQuery } from "@vue/apollo-composable"
import UserListBasic from "src/components/molecules/UserListBasic.vue"
import { GET_USERS } from "src/graphql/queries"
import { computed, ref } from "vue"
import { useRouter } from "vue-router"
const currentPage = ref(1)

const { result } = useQuery(GET_USERS, { page: currentPage })

const users = computed(() => {
  return result.value?.userSearch.data ?? []
})

const lastPage = computed(() => {
  return result.value?.userSearch.paginatorInfo.lastPage ?? 1
})

async function handleUserListBasicClick({ user, action }) {
  switch (action) {
    case "goToUserDetail":
      goToUserDetail(user)
      break
  }
}

const { push } = useRouter()
async function goToUserDetail(user) {
  const userId = user.id
  push({
    name: "user_details",
    params: { id: userId },
  })
}
</script>
