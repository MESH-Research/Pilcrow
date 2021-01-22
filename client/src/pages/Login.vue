<template>
  <q-page class="flex-center flex">
    <q-card style="width: 400px" square>
      <q-card-section class="bg-deep-purple-7 q-pa-sm">
        <div class="text-h5 text-white">Login</div>
      </q-card-section>
      <q-card-section class="q-pa-lg">
        <q-form
          v-on:submit.prevent="login()"
          class="q-px-sm q-pt-md  q-gutter-y-lg q-pb-lg"
        >
          <q-input
            outlined
            ref="username"
            v-model="$v.form.email.$model"
            :error="$v.form.email.$error"
            :label="$t('auth.fields.email')"
            @keypress.enter="$refs.password.focus()"
            autofocus
            autocomplete="username"
          >
            <template #error>
              <div
                v-if="!$v.form.email.required"
                v-text="$t('helpers.REQUIRED_FIELD', [$t('auth.fields.email')])"
              />
              <div
                v-if="!$v.form.email.email"
                v-text="$t('auth.validation.EMAIL_INVALID')"
              />
            </template>
          </q-input>

          <password-input
            outlined
            ref="password"
            v-model="$v.form.password.$model"
            :error="$v.form.password.$error"
            :label="$t('auth.fields.password')"
            @keypress.enter="login"
            autocomplete="current-password"
          >
            <template #error>
              <div
                v-if="!$v.form.password.required"
                v-text="
                  $t('helpers.REQUIRED_FIELD', [$t('auth.fields.password')])
                "
              />
            </template>
          </password-input>
        </q-form>

        <q-banner
          class="text-white bg-red text-center"
          dense
          rounded
          v-if="error"
          v-text="$t(`auth.failures.${error}`)"
        />
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
import PasswordInput from "src/components/forms/PasswordInput.vue";
import { validationMixin } from "vuelidate";
import { required, email } from "vuelidate/lib/validators";

export default {
  components: { PasswordInput },
  mixins: [validationMixin],
  name: "PageLogin",
  data() {
    return {
      form: {
        email: "",
        password: ""
      },
      error: "",
      loading: false
    };
  },
  validations: {
    form: {
      email: {
        email,
        required
      },
      password: {
        required
      }
    }
  },
  methods: {
    async login() {
      this.error = "";
      this.$v.$touch();
      if (this.$v.$invalid) {
        this.error = "LOGIN_FORM_VALIDATION";
        return false;
      }
      try {
        const loginResult = await this.$apollo.mutate({
          mutation: gql`
            mutation($email: String!, $password: String!) {
              login(email: $email, password: $password) {
                id
                name
                username
              }
            }
          `,
          variables: { ...this.form }
        });
      } catch (error) {
        if (error.graphQLErrors) {
          error.graphQLErrors.forEach(error => {
            if (error.extensions?.code) {
              this.error = error.extensions.code;
            }
          });
        }
        if (!this.error) {
          this.error = "FAILURE_OTHER";
        }
        return;
      }
    }
  }
};
</script>
