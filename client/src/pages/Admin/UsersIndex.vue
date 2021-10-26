<template>
  <div>
    <h2 class="q-pl-lg">All Users</h2>
    <div v-if="users.length">
      <q-list>
        <q-item
          v-for="user in users"
          :key="user.id"
          clickable
          data-cy="userListItem"
          class="q-px-lg"
          @click="goToUserDetail(user.id)"
        >
          <q-item-section top avatar>
            <avatar-image :user="user" rounded />
          </q-item-section>

          <q-item-section>
            <q-item-label v-if="user.name">
              {{ user.name }}
            </q-item-label>
            <q-item-label v-else>
              {{ user.username }}
            </q-item-label>
            <q-item-label caption>
              {{ user.email }}
            </q-item-label>
          </q-item-section>

          <q-item-section side top>
            <q-item-label caption> meta </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
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
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useQuery, useResult } from "@vue/apollo-composable"
import { ref } from "@vue/composition-api"

export default {
  components: {
    AvatarImage,
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

    function goToUserDetail(userId) {
      root.$router.push({
        name: "user_details",
        params: { id: userId },
      })
    }

    return { currentPage, users, lastPage, goToUserDetail }
  },
}
</script>
