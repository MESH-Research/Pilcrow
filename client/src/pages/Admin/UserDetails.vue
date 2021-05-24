<template>
  <article>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          label="All Users"
          to="/admin/users"
        />
        <q-breadcrumbs-el label="User Details" />
      </q-breadcrumbs>
    </nav>
    <div class="row justify-center q-px-lg">
      <h2 class="col-sm-12">
        {{ user.username }}
      </h2>
    </div>
    <div class="row q-pa-lg q-col-gutter-lg">
      <section class="col-sm-2 col-xs-12">
        <div class="row justify-center">
          <q-item-section
            top
            avatar
            class="col-sm-12 col-xs-2 q-mb-lg"
          >
            <avatar-image
              :user="user"
              rounded
              class="fit"
            />
          </q-item-section>
        </div>
      </section>
      <section class="col-sm-10 col-xs-12">
        <div class="row q-mb-sm">
          <div class="col-3 q-pr-lg text-right text--grey hide--xxs">
            Username
          </div>
          <div class="col">
            <q-icon
              name="person_outline"
              class="text--grey"
            />
            {{ user.username }}
          </div>
        </div>
        <div class="row q-mb-sm">
          <div class="col-3 q-pr-lg text-right text--grey hide--xxs">
            Email
          </div>
          <div class="col">
            <q-icon
              name="mail_outline"
              class="text--grey"
            />
            {{ user.email }}
          </div>
        </div>
        <div class="row q-mb-sm">
          <div class="col-3 q-pr-lg text-right text--grey hide--xxs">
            Display Name
          </div>
          <div class="col">
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
        <div class="row q-mt-lg">
          <div class="col-3 q-pr-lg text-right text--grey">
            Roles
          </div>
          <div class="col">
            <div
              v-for="role in user.roles"
              :key="role.id"
              class="text-weight-medium"
            >
              <q-icon
                v-if="role.name === 'Application Administrator' || role.name === 'Publication Administrator'"
                name="manage_accounts"
              />
              <q-icon
                v-if="role.name === 'Editor'"
                name="o_book"
              />
              {{ role.name }}
            </div>
            <div v-if="user.roles.length <= 0">
              <q-icon
                name="o_do_disturb_on"
                class="text--grey"
              />
              <span
                class="text--grey text-weight-light"
              >
                No Roles Assigned
              </span>
            </div>
          </div>
        </div>
      </section>
    </div>
  </article>
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
