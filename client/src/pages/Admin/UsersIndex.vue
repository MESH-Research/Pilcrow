<template>
  <div>
    <h2 class="q-pl-lg">
      All Users
    </h2>
    <q-banner v-if="userSearch.data">
      <q-item
        v-for="user in userSearch.data"
        :key="user.id"
        data-cy="userListItem"
      >
        <q-item-section
          top
          avatar
        >
          <avatar-image
            :user="user"
            rounded
          />
        </q-item-section>

        <q-item-section>
          <q-item-label>{{ user.name }}</q-item-label>
          <q-item-label caption>
            Secondary line text. Lorem ipsum dolor sit amet, consectetur adipiscit elit.
          </q-item-label>
        </q-item-section>

        <q-item-section
          side
          top
        >
          <q-item-label caption>
            meta
          </q-item-label>
        </q-item-section>
      </q-item>
      <q-pagination
        v-model="current_page"
        class="q-pa-lg flex flex-center"
        :max="userSearch.paginatorInfo.lastPage"
      />
    </q-banner>
  </div>
</template>

<script>
import { GET_USERS } from "src/graphql/queries";
import AvatarImage from "src/components/atoms/AvatarImage.vue";

export default {
  components: {
    AvatarImage,
  },
  data() {
    return {
      userSearch: {
        data: null
      },
      current_page: 1
    }
  },
  apollo: {
    userSearch: {
      query: GET_USERS,
      variables () {
        return {
          page:this.current_page
        }
      }
    }
  },
}
</script>
