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

<script setup lang="ts">
import UserListBasic from "src/components/molecules/UserListBasic.vue"
import { AdminGetUsersDocument } from "src/gql/graphql"

definePage({
  name: "admin:users"
})

const currentPage = ref(1)

const { result } = useQuery(AdminGetUsersDocument, () => ({
  page: currentPage.value
}))

const users = computed(() => {
  return result.value?.userSearch.data ?? []
})

const lastPage = computed(() => {
  return result.value?.userSearch.paginatorInfo.lastPage ?? 1
})

function handleUserListBasicClick({ user, action }) {
  switch (action) {
    case "goToUserDetail":
      void goToUserDetail(user)
      break
  }
}

const { push } = useRouter()

function goToUserDetail(user) {
  const userId = user.id
  void push({
    name: "admin:users:details",
    params: { id: userId }
  })
}
</script>

<script lang="ts">
graphql(`
  query AdminGetUsers($page: Int) {
    userSearch(page: $page) {
      paginatorInfo {
        ...PaginationFields
      }
      data {
        id
        name
        username
        email
      }
    }
  }
`)
</script>
