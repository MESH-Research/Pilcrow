<template>
  <q-page class="flex-center flex">
    <q-card square>
      <q-card-section class="bg-deep-purple-7 q-pa-sm">
        <div class="text-h5 text-white">Login</div>
      </q-card-section>
      <q-card-section class="q-pa-lg">
        <q-form v-on:submit.prevent="login()" class="q-px-sm q-pt-md q-pb-lg">
          <q-input
            square
            ref="username"
            v-model="form.username"
            :label="$t('auth.fields.username')"
            @keypress.enter="$refs.password.focus()"
            autofocus
            autocomplete="username"
          >
            <template #prepend>
              <q-icon name="person" />
            </template>
          </q-input>

          <q-input
            square
            ref="password"
            v-model="form.password"
            :type="isPwd ? 'password' : 'text'"
            :label="$t('auth.fields.password')"
            @keypress.enter="login"
            autocomplete="current-password"
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="isPwd = !isPwd"
              />
            </template>
          </q-input>
        </q-form>

        <transition
          appear
          enter-active-class="animated bounceIn"
          leave-active-class="animated fadeOut"
        >
          <q-banner
            class="text-white bg-red text-center"
            dense
            rounded
            v-if="error"
          >
            {{ error }}
          </q-banner>
        </transition>
      </q-card-section>
      <q-card-actions class="q-px-lg">
        <q-btn
          @click.prevent="login()"
          unelevated
          size="lg"
          color="purple-4"
          class="full-width text-white"
          label="Login"
          :loading="loading"
        />
      </q-card-actions>
      <q-card-section class="text-center q-pa-sm">
        <p class="text-grey-6">
          Don't have an account?
          <router-link to="/register">Register.</router-link>
        </p>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import gql from "graphql-tag";

export default {
  name: "PageLogin",
  data() {
    return {
      isPwd: true,
      form: {
        email: "",
        password: ""
      },
      error: "",
      loading: false
    };
  },
  methods: {
    async login() {
      const loginResult = await this.$apollo
        .mutate({
          mutation: gql`
            mutation($email: String!, $password: String!) {
              login(email: $email, password: $password) {
                id
                name
                username
              }
            }
          `,
          variables: {
            email: this.form.email,
            password: this.form.password
          }
        })
        .then(data => {
          console.log(data);
        })
        .catch(error => {
          console.error(error);
        });
    },
  },
};
</script>
