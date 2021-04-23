<template>
  <div>
    <h2 class="q-pl-lg">
      {{ user.username }}
    </h2>
    <div class="row q-pa-lg q-col-gutter-lg">
      <div class="col-2">
        <q-item-section
          top
          avatar
        >
          <avatar-image
            :user="user"
            square
            class="fit"
          />
        </q-item-section>
      </div>
      <div class="col-10">
        <div class="row">
          <div class="col-2 text-right">
            Username
          </div>
          <div class="q-pl-lg col-10">
            <q-icon name="person_outline"/>
            {{ user.username }}
          </div>
          <div class="col-2 text-right">
            Email
          </div>
          <div class="q-pl-lg col-10">
            <q-icon name="mail_outline"/>
            {{ user.email }}
          </div>
          <div class="col-2 text-right">
            Display Name
          </div>
          <div class="q-pl-lg col-10">
            <q-icon name="label_outline"/>
            {{ user.name }}
          </div>
        </div>
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

