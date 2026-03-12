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

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetUsers($page: Int) {
    userSearch(page: $page) {
      paginatorInfo {
        ...paginationFields
      }
      data {
        ...userListBasic
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { useQuery } from "@vue/apollo-composable"
import UserListBasic from "src/components/molecules/UserListBasic.vue"
import { GetUsersDocument } from "src/graphql/generated/graphql"
import { computed, ref } from "vue"
import { useRouter } from "vue-router"
const currentPage = ref(1)

const { result } = useQuery(GetUsersDocument, () => ({
  page: currentPage.value
}))

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
    params: { id: userId }
  })
}
</script>
