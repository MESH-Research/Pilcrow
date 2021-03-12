<template>
  <q-banner v-if="users.data">
    <q-item 
      v-for="user in users.data" 
      :key="user.id" 
    >
      <q-item-section
        top
        avatar
      >
        <q-avatar rounded>
          <img src="avatar.png">
        </q-avatar>
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
      :max="users.paginatorInfo.lastPage"
    />
  </q-banner>
</template>

<script>
import { GET_USERS } from "src/graphql/queries";

export default {
  data() {
    return {
      users: {
        data: null
      },
      current_page: 1
    }
  },
  apollo: {
    users: {
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
