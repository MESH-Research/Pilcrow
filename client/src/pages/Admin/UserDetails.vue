<template>
  <div>
    <h2 class="q-pl-lg">
      User Details {{ this.$route.params.id }} {{ this.user.name }}
    </h2>
    <div class="row q-pa-lg q-col-gutter-lg">
      <div class="col-2">
        <q-item-section
          top
          avatar
        >
          <avatar-image
            :user="user"
            rounded
            class="fit"
          />
        </q-item-section>
      </div>
      <div class="col-10">
        .col-11
      </div>
    </div>
  </div>
</template>

<script>
import { GET_USER } from "src/graphql/queries";
import AvatarImage from "src/components/atoms/AvatarImage.vue";

export default {
  components: {
    AvatarImage,
  },
  data() {
    return {
      user: {
        name: null,
        email: null,
        username: null,
        roles: []
      },
      user_id: this.$route.params.id
    }
  },
  apollo: {
    user: {
      query: GET_USER,
      variables () {
        return {
         id:this.user_id
        }
      }
    }
  },
}
</script>

