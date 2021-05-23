<template>
  <div>
    <div class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          label="All Users"
          to="/admin/users"
        />
        <q-breadcrumbs-el label="User Details" />
      </q-breadcrumbs>
    </div>
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
        <div class="row q-mb-sm">
          <div class="col-2 text-right text--grey">
            Username
          </div>
          <div class="q-pl-lg col-10">
            <q-icon
              name="person_outline"
              class="text--grey"
            />
            {{ user.username }}
          </div>
        </div>
        <div class="row q-mb-sm">
          <div class="col-2 text-right text--grey">
            Email
          </div>
          <div class="q-pl-lg col-10">
            <q-icon
              name="mail_outline"
              class="text--grey"
            />
            {{ user.email }}
          </div>
        </div>
        <div class="row q-mb-sm">
          <div class="col-2 text-right text--grey">
            Display Name
          </div>
          <div class="q-pl-lg col-10">
            <div v-if="user.name">
              <q-icon
                name="label_outline"
                class="text--grey"
              />
              {{ user.name }}
            </div>
            <div v-else>
              <q-icon
                name="o_do_disturb_on"
                class="text--grey"
              />
              <span
                class="text--grey text-weight-light"
              >
                No Display Name
              </span>
            </div>
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

